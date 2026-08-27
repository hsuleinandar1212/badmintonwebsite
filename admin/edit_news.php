<?php

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * ADMIN - EDIT NEWS
 * =========================================================
 */

declare(strict_types=1);


/* =========================================================
   AUTHENTICATION
   ========================================================= */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';


/* =========================================================
   DATABASE CHECK
   ========================================================= */

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Database connection is unavailable.');
}


/* =========================================================
   HELPER
   ========================================================= */

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}


/* =========================================================
   NEWS ID
   ========================================================= */

$news_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$news_id || $news_id < 1) {
    http_response_code(400);
    die('Invalid news article ID.');
}


/* =========================================================
   FETCH ARTICLE
   ========================================================= */

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            content,
            image
        FROM news
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$news_id]);

    $article = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    http_response_code(500);

    die('Unable to load the news article.');
}


if (!$article) {

    http_response_code(404);

    die('News article not found.');
}


/* =========================================================
   MESSAGES
   ========================================================= */

$error = '';
$success = '';


/* =========================================================
   FORM SUBMISSION
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /* =====================================================
       CSRF
       ===================================================== */

    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!is_string($csrfToken) ||
        !verify_csrf_token($csrfToken)
    ) {

        http_response_code(403);

        die('CSRF validation failed.');
    }


    /* =====================================================
       FORM DATA
       ===================================================== */

    $title = trim(
        (string)($_POST['title'] ?? '')
    );

    $content = trim(
        (string)($_POST['content'] ?? '')
    );


    /* =====================================================
       VALIDATION
       ===================================================== */

    if ($title === '') {

        $error =
            'Article title is required.';

    } elseif (mb_strlen($title) > 255) {

        $error =
            'Article title must not exceed 255 characters.';

    } elseif ($content === '') {

        $error =
            'Article content is required.';

    } elseif (mb_strlen($content) > 50000) {

        $error =
            'Article content is too long.';
    }


    /* =====================================================
       KEEP CURRENT IMAGE
       ===================================================== */

    $image_path =
        $article['image'] ?? null;


    /* =====================================================
       NEW IMAGE UPLOAD
       ===================================================== */

    if (
        $error === '' &&
        isset($_FILES['image'])
    ) {

        $file = $_FILES['image'];


        /* =================================================
           NO FILE
           ================================================= */

        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {


            /* =============================================
               UPLOAD ERROR
               ============================================= */

            if ($file['error'] !== UPLOAD_ERR_OK) {

                switch ($file['error']) {

                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:

                        $error =
                            'The uploaded image is too large.';

                        break;

                    case UPLOAD_ERR_PARTIAL:

                        $error =
                            'The image upload was incomplete.';

                        break;

                    case UPLOAD_ERR_NO_TMP_DIR:

                        $error =
                            'Temporary upload directory is missing.';

                        break;

                    case UPLOAD_ERR_CANT_WRITE:

                        $error =
                            'The server could not save the uploaded image.';

                        break;

                    default:

                        $error =
                            'Image upload failed.';
                }


            } else {


                /* =============================================
                   FILE SIZE
                   ============================================= */

                $maxFileSize =
                    5 * 1024 * 1024;

                if ((int)$file['size'] > $maxFileSize) {

                    $error =
                        'Image size must not exceed 5 MB.';
                }


                /* =============================================
                   TEMP FILE
                   ============================================= */

                $tmpName =
                    $file['tmp_name'] ?? '';

                if (
                    $error === '' &&
                    !is_uploaded_file($tmpName)
                ) {

                    $error =
                        'Invalid uploaded file.';
                }


                /* =============================================
                   MIME TYPE
                   ============================================= */

                $mimeType = '';

                if ($error === '') {

                    $finfo =
                        finfo_open(FILEINFO_MIME_TYPE);

                    if ($finfo === false) {

                        $error =
                            'Unable to verify the uploaded image.';

                    } else {

                        $mimeType =
                            finfo_file(
                                $finfo,
                                $tmpName
                            );

                        finfo_close($finfo);

                    }
                }


                /* =============================================
                   ALLOWED MIME TYPES
                   ============================================= */

                $allowedMimeTypes = [

                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'

                ];


                if (
                    $error === '' &&
                    !array_key_exists(
                        $mimeType,
                        $allowedMimeTypes
                    )
                ) {

                    $error =
                        'Invalid image type. Only JPG, PNG and WEBP are allowed.';
                }


                /* =============================================
                   REAL IMAGE CHECK
                   ============================================= */

                if ($error === '') {

                    $imageInfo =
                        @getimagesize($tmpName);

                    if ($imageInfo === false) {

                        $error =
                            'The uploaded file is not a valid image.';
                    }
                }


                /* =============================================
                   IMAGE DIMENSIONS
                   ============================================= */

                if (
                    $error === '' &&
                    isset($imageInfo[0], $imageInfo[1])
                ) {

                    $width =
                        (int)$imageInfo[0];

                    $height =
                        (int)$imageInfo[1];


                    /*
                     * Prevent absurdly large dimensions.
                     */

                    if (
                        $width < 1 ||
                        $height < 1 ||
                        $width > 8000 ||
                        $height > 8000
                    ) {

                        $error =
                            'The image dimensions are not allowed.';
                    }
                }


                /* =============================================
                   CREATE UPLOAD DIRECTORY
                   ============================================= */

                $uploadDir =
                    __DIR__ . '/../uploads/news/';

                if ($error === '') {

                    if (!is_dir($uploadDir)) {

                        if (!mkdir(
                            $uploadDir,
                            0755,
                            true
                        )) {

                            $error =
                                'Unable to create the news upload directory.';
                        }
                    }
                }


                /* =============================================
                   DIRECTORY CHECK
                   ============================================= */

                if (
                    $error === '' &&
                    !is_dir($uploadDir)
                ) {

                    $error =
                        'The news upload directory does not exist.';
                }


                /* =============================================
                   DIRECTORY PERMISSION
                   ============================================= */

                if (
                    $error === '' &&
                    !is_writable($uploadDir)
                ) {

                    $error =
                        'The news upload directory is not writable.';
                }


                /* =============================================
                   SECURE RANDOM FILENAME
                   ============================================= */

                $newFileName = '';

                if ($error === '') {

                    try {

                        $newFileName =
                            bin2hex(
                                random_bytes(16)
                            )
                            . '.'
                            . $allowedMimeTypes[$mimeType];

                    } catch (Throwable $e) {

                        $error =
                            'Unable to generate a secure image filename.';
                    }
                }


                /* =============================================
                   MOVE FILE
                   ============================================= */

                if ($error === '') {

                    $destination =
                        $uploadDir . $newFileName;


                    if (!move_uploaded_file(
                        $tmpName,
                        $destination
                    )) {

                        $error =
                            'Unable to save the uploaded image.';

                    } else {

                        /*
                         * Store only the web path.
                         */

                       $image_path = $newFileName;
                    }
                }
            }
        }
    }


    /* =====================================================
       UPDATE DATABASE
       ===================================================== */

    if ($error === '') {

        try {

            $pdo->beginTransaction();


            $updateStmt = $pdo->prepare("
                UPDATE news
                SET
                    title = ?,
                    content = ?,
                    image = ?
                WHERE id = ?
                LIMIT 1
            ");


            $updateStmt->execute([
                $title,
                $content,
                $image_path,
                $news_id
            ]);


            $pdo->commit();


            /* =================================================
               UPDATE LOCAL ARTICLE
               ================================================= */

            $article['title'] =
                $title;

            $article['content'] =
                $content;

            $article['image'] =
                $image_path;


            $success =
                'News article updated successfully.';

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            /*
             * If a new image was uploaded but DB update
             * failed, remove the newly uploaded file.
             */

            if (
                isset($destination) &&
                is_file($destination)
            ) {

                @unlink($destination);
            }


            $error =
                'Unable to update the news article.';
        }
    }
}


/* =========================================================
   CSRF TOKEN
   ========================================================= */

$csrf_token = csrf_token();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex,nofollow">

    <title>
        Edit News | MTU Badminton Club
    </title>


    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">


    <style>
    /* =========================================================
   ROOT
   ========================================================= */

    :root {

        --primary: #8B5CF6;
        --primary-dark: #6D28D9;
        --primary-light: #F3E8FF;

        --indigo: #6366F1;

        --navy: #0F172A;

        --text: #1E293B;

        --muted: #64748B;

        --background: #F8FAFC;

        --white: #FFFFFF;

        --border: #E2E8F0;

        --success-bg: #ECFDF5;
        --success-border: #A7F3D0;
        --success-text: #047857;

        --error-bg: #FEF2F2;
        --error-border: #FECACA;
        --error-text: #B91C1C;
    }


    /* =========================================================
   RESET
   ========================================================= */

    * {

        box-sizing: border-box;

    }


    html {

        scroll-behavior: smooth;

    }


    body {

        margin: 0;

        min-height: 100vh;

        padding: 40px 20px;

        font-family:
            'Inter',
            Arial,
            sans-serif;

        color: var(--text);

        background:

            radial-gradient(circle at top right,
                rgba(139, 92, 246, .13),
                transparent 32%),

            radial-gradient(circle at bottom left,
                rgba(99, 102, 241, .08),
                transparent 32%),

            var(--background);
    }


    /* =========================================================
   CONTAINER
   ========================================================= */

    .container {

        width: min(900px,
                100%);

        margin: auto;
    }


    /* =========================================================
   TOP BAR
   ========================================================= */

    .top-bar {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        margin-bottom: 25px;
    }


    .brand {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .brand-icon {

        width: 45px;

        height: 45px;

        display: flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        border-radius: 13px;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));

        color: white;

        font-size: 21px;

        box-shadow:
            0 8px 20px rgba(109, 40, 217, .22);
    }


    .brand h2 {

        margin: 0;

        color: var(--navy);

        font-size: 15px;

        font-weight: 800;
    }


    .brand p {

        margin: 3px 0 0;

        color: var(--muted);

        font-size: 11px;
    }


    /* =========================================================
   BACK
   ========================================================= */

    .back {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 10px 15px;

        border:
            1px solid var(--border);

        border-radius: 10px;

        background: white;

        color: var(--text);

        text-decoration: none;

        font-size: 13px;

        font-weight: 600;

        transition: .25s ease;
    }


    .back:hover {

        background: var(--primary-light);

        border-color: #C4B5FD;

        color: var(--primary-dark);

        transform: translateY(-1px);
    }


    /* =========================================================
   ALERTS
   ========================================================= */

    .alert {

        display: flex;

        align-items: flex-start;

        gap: 10px;

        padding: 15px 18px;

        margin-bottom: 20px;

        border-radius: 12px;

        font-size: 13px;

        font-weight: 600;

        line-height: 1.5;
    }


    .success {

        background: var(--success-bg);

        border:
            1px solid var(--success-border);

        color: var(--success-text);
    }


    .error {

        background: var(--error-bg);

        border:
            1px solid var(--error-border);

        color: var(--error-text);
    }


    /* =========================================================
   CARD
   ========================================================= */

    .card {

        overflow: hidden;

        background: white;

        border:
            1px solid var(--border);

        border-radius: 20px;

        box-shadow:
            0 20px 60px rgba(15, 23, 42, .08);

        animation:
            enter .45s ease;
    }


    .gradient {

        height: 6px;

        background:
            linear-gradient(90deg,
                var(--primary-dark),
                var(--primary),
                var(--indigo));
    }


    /* =========================================================
   HEADER
   ========================================================= */

    .card-header {

        display: flex;

        align-items: center;

        gap: 15px;

        padding:
            30px 36px 25px;

        border-bottom:
            1px solid #F1F5F9;
    }


    .header-icon {

        width: 52px;

        height: 52px;

        display: flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        border-radius: 15px;

        background: var(--primary-light);

        color: var(--primary-dark);

        font-size: 22px;
    }


    .card-header h1 {

        margin: 0;

        color: var(--navy);

        font-size: 23px;

        font-weight: 800;
    }


    .card-header p {

        margin: 6px 0 0;

        color: var(--muted);

        font-size: 13px;

        line-height: 1.5;
    }


    /* =========================================================
   FORM
   ========================================================= */

    form {

        padding:
            30px 36px 36px;
    }


    .form-group {

        margin-bottom: 25px;
    }


    label {

        display: block;

        margin-bottom: 9px;

        color: var(--navy);

        font-size: 13px;

        font-weight: 700;
    }


    .required {

        color: #EF4444;
    }


    /* =========================================================
   INPUTS
   ========================================================= */

    input[type="text"],
    textarea {

        width: 100%;

        border:
            1.5px solid var(--border);

        border-radius: 12px;

        outline: none;

        background: white;

        color: var(--text);

        font-family: inherit;

        font-size: 14px;

        transition:
            border-color .25s ease,
            box-shadow .25s ease;
    }


    input[type="text"] {

        height: 50px;

        padding:
            0 15px;
    }


    textarea {

        min-height: 240px;

        padding: 14px 15px;

        resize: vertical;

        line-height: 1.7;
    }


    input[type="text"]:focus,
    textarea:focus {

        border-color: var(--primary);

        box-shadow:
            0 0 0 4px rgba(139, 92, 246, .11);
    }


    /* =========================================================
   CURRENT IMAGE
   ========================================================= */

    .current-image {

        padding: 16px;

        margin-bottom: 20px;

        border:
            1px solid var(--border);

        border-radius: 15px;

        background: #F8FAFC;
    }


    .current-image img {

        display: block;

        width: 220px;

        max-width: 100%;

        height: 145px;

        object-fit: cover;

        border:
            3px solid white;

        border-radius: 12px;

        box-shadow:
            0 8px 20px rgba(15, 23, 42, .12);
    }


    .current-image p {

        margin: 10px 0 0;

        color: var(--muted);

        font-size: 11px;
    }


    .no-image {

        padding: 30px 20px;

        margin-bottom: 20px;

        text-align: center;

        border:
            2px dashed var(--border);

        border-radius: 14px;

        background: #F8FAFC;

        color: var(--muted);

        font-size: 13px;
    }


    /* =========================================================
   UPLOAD
   ========================================================= */

    .upload-box {

        padding: 20px;

        border:
            2px dashed #C4B5FD;

        border-radius: 14px;

        background: var(--primary-light);

        transition: .25s ease;
    }


    .upload-box:hover {

        border-color: var(--primary);

        background: #EDE9FE;
    }


    input[type="file"] {

        width: 100%;

        color: var(--muted);

        font-family: inherit;

        font-size: 12px;
    }


    input[type="file"]::file-selector-button {

        padding: 10px 15px;

        margin-right: 10px;

        border: none;

        border-radius: 9px;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));

        color: white;

        font-family: inherit;

        font-size: 12px;

        font-weight: 700;

        cursor: pointer;
    }


    .help {

        margin: 10px 0 0;

        color: var(--muted);

        font-size: 11px;

        line-height: 1.5;
    }


    /* =========================================================
   DIVIDER
   ========================================================= */

    .divider {

        height: 1px;

        margin: 30px 0;

        background: var(--border);
    }


    /* =========================================================
   ACTIONS
   ========================================================= */

    .actions {

        display: flex;

        justify-content: flex-end;

        gap: 12px;
    }


    .cancel,
    .update {

        min-height: 48px;

        padding: 0 22px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border-radius: 11px;

        font-family: inherit;

        font-size: 13px;

        font-weight: 700;

        text-decoration: none;

        cursor: pointer;

        transition: .25s ease;
    }


    .cancel {

        border:
            1px solid var(--border);

        background: white;

        color: var(--text);
    }


    .cancel:hover {

        background: #F8FAFC;

        transform: translateY(-1px);
    }


    .update {

        border: none;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));

        color: white;

        box-shadow:
            0 9px 22px rgba(109, 40, 217, .25);
    }


    .update:hover {

        transform: translateY(-2px);

        box-shadow:
            0 14px 28px rgba(109, 40, 217, .32);
    }


    .update:active {

        transform: translateY(0);
    }


    /* =========================================================
   SECURITY
   ========================================================= */

    .security {

        margin-top: 18px;

        text-align: center;

        color: #94A3B8;

        font-size: 11px;
    }


    /* =========================================================
   ANIMATION
   ========================================================= */

    @keyframes enter {

        from {

            opacity: 0;

            transform:
                translateY(18px);
        }

        to {

            opacity: 1;

            transform:
                translateY(0);
        }
    }


    /* =========================================================
   RESPONSIVE
   ========================================================= */

    @media (max-width: 650px) {

        body {

            padding:
                20px 10px;
        }


        .top-bar {

            align-items: flex-start;
        }


        .card-header {

            padding:
                25px 20px;
        }


        form {

            padding:
                25px 20px;
        }


        .card-header h1 {

            font-size: 20px;
        }


        .current-image img {

            width: 100%;

            height: 180px;
        }


        .actions {

            flex-direction:
                column-reverse;
        }


        .cancel,
        .update {

            width: 100%;
        }

    }


    @media (max-width: 450px) {

        .brand p {

            display: none;
        }


        .header-icon {

            display: none;
        }


        .card-header h1 {

            font-size: 18px;
        }


        .back {

            padding:
                9px 11px;

            font-size: 12px;
        }

    }
    </style>

</head>


<body>

    <div class="container">


        <!-- =====================================================
         TOP BAR
         ===================================================== -->

        <div class="top-bar">

            <div class="brand">

                <div class="brand-icon">
                    🏸
                </div>

                <div>

                    <h2>
                        MTU Badminton Club
                    </h2>

                    <p>
                        Administration Panel
                    </p>

                </div>

            </div>


            <a href="manage_news.php" class="back">
                ← Back
            </a>

        </div>


        <!-- =====================================================
         SUCCESS
         ===================================================== -->

        <?php if ($success !== ''): ?>

        <div class="alert success" role="alert">

            <span>✓</span>

            <span>
                <?= e($success) ?>
            </span>

        </div>

        <?php endif; ?>


        <!-- =====================================================
         ERROR
         ===================================================== -->

        <?php if ($error !== ''): ?>

        <div class="alert error" role="alert">

            <span>⚠</span>

            <span>
                <?= e($error) ?>
            </span>

        </div>

        <?php endif; ?>


        <!-- =====================================================
         CARD
         ===================================================== -->

        <div class="card">

            <div class="gradient"></div>


            <!-- =================================================
             HEADER
             ================================================= -->

            <div class="card-header">

                <div class="header-icon">
                    ✎
                </div>

                <div>

                    <h1>
                        Edit News Article
                    </h1>

                    <p>
                        Update your badminton club news
                        and announcements.
                    </p>

                </div>

            </div>


            <!-- =================================================
             FORM
             ================================================= -->

            <form method="POST" enctype="multipart/form-data" autocomplete="off">


                <!-- =================================================
                 CSRF
                 ================================================= -->

                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">


                <!-- =================================================
                 TITLE
                 ================================================= -->

                <div class="form-group">

                    <label for="title">

                        Article Title

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input type="text" id="title" name="title" value="<?= e($article['title']) ?>"
                        placeholder="Enter article title..." maxlength="255" required>

                </div>


                <!-- =================================================
                 CONTENT
                 ================================================= -->

                <div class="form-group">

                    <label for="content">

                        Article Content

                        <span class="required">
                            *
                        </span>

                    </label>


                    <textarea id="content" name="content" placeholder="Write your news article..." maxlength="50000"
                        required><?= e($article['content']) ?></textarea>

                </div>


                <!-- =================================================
                 IMAGE
                 ================================================= -->

                <div class="form-group">

                    <label>
                        Current Image
                    </label>


                    <?php if (!empty($article['image'])): ?>

                    <div class="current-image">

                        <img src="../uploads/news/<?= e(basename($article['image'])) ?>" alt="Current news image">

                        <p>
                            Current image attached to this article.
                        </p>

                    </div>

                    <?php else: ?>

                    <div class="no-image">

                        📷

                        <br>
                        <br>

                        No image is currently attached.

                    </div>

                    <?php endif; ?>


                    <!-- =================================================
                     CHANGE IMAGE
                     ================================================= -->

                    <label for="image">
                        Change Image
                    </label>


                    <div class="upload-box">

                        <input type="file" id="image" name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">


                        <p class="help">

                            JPG, JPEG, PNG or WEBP
                            · Maximum 5 MB

                        </p>

                    </div>

                </div>


                <!-- =================================================
                 DIVIDER
                 ================================================= -->

                <div class="divider"></div>


                <!-- =================================================
                 ACTIONS
                 ================================================= -->

                <div class="actions">

                    <a href="manage_news.php" class="cancel">
                        Cancel
                    </a>


                    <button type="submit" class="update">
                        ✓ &nbsp; Update Article
                    </button>

                </div>


                <!-- =================================================
                 SECURITY
                 ================================================= -->

                <div class="security">

                    🔒 Protected by CSRF security

                </div>


            </form>

        </div>

    </div>


    <script>
    /*
     * Small client-side file size check.
     *
     * Server-side validation remains the real security control.
     */

    const imageInput =
        document.getElementById('image');

    if (imageInput) {

        imageInput.addEventListener(
            'change',
            function() {

                const file =
                    this.files[0];

                if (!file) {
                    return;
                }

                const maxSize =
                    5 * 1024 * 1024;

                if (file.size > maxSize) {

                    alert(
                        'Image size must not exceed 5 MB.'
                    );

                    this.value = '';

                }

            }
        );

    }
    </script>

</body>

</html>