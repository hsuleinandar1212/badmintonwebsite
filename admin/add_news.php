<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/csrf.php";

// Use the connection name provided by the database configuration.
if (!isset($pdo)) {
   $pdo = $mysqli ?? null;
}

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF
    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        $error = "Invalid security token.";
    } else {

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'Published';

        // Validate
        if ($title === '') {
            $error = "Please enter a news title.";
        } elseif ($content === '') {
            $error = "Please enter the news content.";
        } elseif (!in_array($status, ['Published', 'Draft'], true)) {
            $error = "Invalid news status.";
        }

        // Image upload
        $imageName = null;

        if (
            $error === "" &&
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $error = "There was a problem uploading the image.";

            } else {

                $allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                $fileType = mime_content_type(
                    $_FILES['image']['tmp_name']
                );

                if (!in_array($fileType, $allowedTypes, true)) {

                    $error = "Only JPG, PNG and WEBP images are allowed.";

                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {

                    $error = "Image size must be less than 5MB.";

                } else {

                    $uploadDir = __DIR__ . "/../uploads/news/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $extension = strtolower(
                        pathinfo(
                            $_FILES['image']['name'],
                            PATHINFO_EXTENSION
                        )
                    );

                    $imageName = uniqid('news_', true) . "." . $extension;

                    $destination = $uploadDir . $imageName;

                    if (!move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $destination
                    )) {
                        $error = "Failed to save the image.";
                    }
                }
            }
        }

        // Insert news
        if ($error === "" && !isset($pdo)) {
            $error = "Database connection is unavailable.";
        }

        if ($error === "") {

            $stmt = $pdo->prepare(
                "INSERT INTO news
                (title, content, image, status)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bindValue(1, $title);
            $stmt->bindValue(2, $content);
            $stmt->bindValue(3, $imageName);
            $stmt->bindValue(4, $status);

            if ($stmt->execute()) {

                $message = "News posted successfully!";

                // Clear form
                $title = "";
                $content = "";

            } else {

                // Remove uploaded image if database insert failed
                if ($imageName !== null) {

                    $file = __DIR__ . "/../uploads/news/" . $imageName;

                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                $error = "Failed to post news.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Post News - MTU Badminton Club</title>

    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* =========================================================
           ROOT
        ========================================================= */

    :root {

        --purple: #8B5CF6;
        --purple-dark: #6D28D9;
        --purple-deep: #5B21B6;

        --purple-soft: #F3E8FF;
        --purple-light: #FAF5FF;

        --black: #111111;
        --black-soft: #1F2937;

        --white: #FFFFFF;

        --background: #F7F7FB;

        --text: #1F2937;
        --muted: #6B7280;

        --border: #E5E7EB;

        --green: #16A34A;
        --green-soft: #DCFCE7;

        --red: #DC2626;
        --red-soft: #FEE2E2;

        --orange: #D97706;
        --orange-soft: #FEF3C7;

        --shadow:
            0 10px 30px rgba(17, 17, 17, 0.06);

        --shadow-hover:
            0 18px 40px rgba(109, 40, 217, 0.12);

        --radius: 18px;

        --transition: all 0.25s ease;
    }


    /* =========================================================
           GLOBAL
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

        background:
            radial-gradient(circle at top right,
                rgba(139, 92, 246, 0.08),
                transparent 32%),
            var(--background);

        color: var(--text);

        font-family:
            Inter,
            "Segoe UI",
            Arial,
            sans-serif;

        font-size: 14px;

        animation: pageFade 0.45s ease;
    }


    @keyframes pageFade {

        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    /* =========================================================
           MAIN CONTAINER
        ========================================================= */

    .page-wrapper {

        width: 100%;

        max-width: 1450px;

        margin: 0 auto;

        padding: 30px 32px 60px;
    }


    /* =========================================================
           HEADER
        ========================================================= */

    .page-header {

        position: relative;

        overflow: hidden;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 25px;

        padding: 27px 30px;

        margin-bottom: 25px;

        background: var(--white);

        border: 1px solid rgba(139, 92, 246, 0.09);

        border-radius: 22px;

        box-shadow: var(--shadow);
    }


    .page-header::before {

        content: "";

        position: absolute;

        left: 0;
        top: 0;

        width: 6px;
        height: 100%;

        background:
            linear-gradient(180deg,
                var(--purple),
                var(--purple-dark));
    }


    .page-header::after {

        content: "";

        position: absolute;

        width: 180px;
        height: 180px;

        right: -70px;
        top: -100px;

        border-radius: 50%;

        background: var(--purple-soft);

        opacity: 0.7;
    }


    .header-content {

        position: relative;

        z-index: 2;
    }


    .header-icon {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        width: 45px;
        height: 45px;

        margin-bottom: 12px;

        border-radius: 13px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 22px;
    }


    .page-header h1 {

        margin: 0;

        color: var(--black);

        font-size: clamp(25px, 3vw, 34px);

        font-weight: 800;

        letter-spacing: -1px;
    }


    .page-header p {

        margin: 7px 0 0;

        color: var(--muted);

        font-size: 14px;

        font-weight: 500;
    }


    /* =========================================================
           HEADER BUTTONS
        ========================================================= */

    .header-actions {

        position: relative;

        z-index: 5;

        display: flex;

        flex-wrap: wrap;

        justify-content: flex-end;

        gap: 9px;
    }


    .header-actions .btn {

        border: none;

        border-radius: 11px;

        padding: 10px 16px;

        font-size: 13px;

        font-weight: 700;

        transition: var(--transition);
    }


    .header-actions .btn:hover {

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(109, 40, 217, 0.14);
    }


    .btn-dashboard {

        background: #F3F4F6;

        color: var(--black-soft);
    }


    .btn-dashboard:hover {

        background: var(--purple-soft);

        color: var(--purple-dark);
    }


    .btn-manage {

        background: var(--purple-dark);

        color: white;
    }


    .btn-manage:hover {

        background: var(--purple-deep);

        color: white;
    }


    /* =========================================================
           ALERTS
        ========================================================= */

    .custom-alert {

        display: flex;

        align-items: flex-start;

        gap: 13px;

        padding: 16px 18px;

        margin-bottom: 22px;

        border-radius: 15px;

        border: 1px solid transparent;

        box-shadow: var(--shadow);

        animation: alertIn 0.35s ease;
    }


    @keyframes alertIn {

        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .alert-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        width: 34px;
        height: 34px;

        border-radius: 10px;

        font-size: 16px;

        font-weight: 800;
    }


    .alert-success-custom {

        background: var(--green-soft);

        color: #166534;

        border-color: #BBF7D0;
    }


    .alert-success-custom .alert-icon {

        background: white;

        color: var(--green);
    }


    .alert-error-custom {

        background: var(--red-soft);

        color: #991B1B;

        border-color: #FECACA;
    }


    .alert-error-custom .alert-icon {

        background: white;

        color: var(--red);
    }


    .alert-content strong {

        display: block;

        margin-bottom: 2px;

        font-size: 13px;

        font-weight: 800;
    }


    .alert-content span {

        font-size: 13px;
    }


    /* =========================================================
           EDITOR LAYOUT
        ========================================================= */

    .editor-layout {

        display: grid;

        grid-template-columns: minmax(0, 1fr) 340px;

        gap: 24px;

        align-items: start;
    }


    /* =========================================================
           MAIN CARD
        ========================================================= */

    .editor-card {

        background: white;

        border: 1px solid var(--border);

        border-radius: var(--radius);

        box-shadow: var(--shadow);

        overflow: hidden;

        transition: var(--transition);
    }


    .editor-card:hover {

        border-color:
            rgba(139, 92, 246, 0.18);

        box-shadow: var(--shadow-hover);
    }


    .editor-card-header {

        display: flex;

        align-items: center;

        gap: 13px;

        padding: 21px 24px;

        border-bottom: 1px solid #F1F1F4;
    }


    .editor-card-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 40px;
        height: 40px;

        border-radius: 11px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 19px;
    }


    .editor-card-header h2 {

        margin: 0;

        color: var(--black);

        font-size: 16px;

        font-weight: 800;
    }


    .editor-card-header p {

        margin: 3px 0 0;

        color: var(--muted);

        font-size: 12px;
    }


    .editor-body {

        padding: 26px;
    }


    /* =========================================================
           FORM
        ========================================================= */

    .form-section {

        margin-bottom: 25px;
    }


    .form-label {

        display: flex;

        align-items: center;

        gap: 5px;

        margin-bottom: 9px;

        color: var(--black-soft);

        font-size: 12px;

        font-weight: 800;

        letter-spacing: 0.2px;

        text-transform: uppercase;
    }


    .required {

        color: var(--red);

        font-size: 13px;
    }


    .form-control,
    .form-select {

        min-height: 48px;

        padding: 11px 14px;

        border: 1px solid var(--border);

        border-radius: 11px;

        background: #FCFCFD;

        color: var(--text);

        font-size: 13px;

        box-shadow: none !important;

        transition: var(--transition);
    }


    .form-control::placeholder {

        color: #A1A1AA;
    }


    .form-control:hover,
    .form-select:hover {

        border-color: #D8D0E8;
    }


    .form-control:focus,
    .form-select:focus {

        background: white;

        border-color: var(--purple);

        box-shadow:
            0 0 0 4px rgba(139, 92, 246, 0.11) !important;
    }


    textarea.form-control {

        min-height: 280px;

        resize: vertical;

        line-height: 1.7;
    }


    /* =========================================================
           TITLE INPUT
        ========================================================= */

    .title-input {

        font-size: 15px !important;

        font-weight: 600;
    }


    /* =========================================================
           SIDEBAR
        ========================================================= */

    .sidebar-card {

        background: white;

        border: 1px solid var(--border);

        border-radius: var(--radius);

        box-shadow: var(--shadow);

        overflow: hidden;

        transition: var(--transition);
    }


    .sidebar-card:hover {

        border-color:
            rgba(139, 92, 246, 0.18);

        box-shadow: var(--shadow-hover);
    }


    .sidebar-card+.sidebar-card {

        margin-top: 20px;
    }


    .sidebar-header {

        padding: 19px 20px;

        border-bottom: 1px solid #F1F1F4;
    }


    .sidebar-header h3 {

        margin: 0;

        color: var(--black);

        font-size: 14px;

        font-weight: 800;
    }


    .sidebar-header p {

        margin: 4px 0 0;

        color: var(--muted);

        font-size: 11px;
    }


    .sidebar-body {

        padding: 20px;
    }


    /* =========================================================
           IMAGE UPLOAD
        ========================================================= */

    .upload-area {

        position: relative;

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        min-height: 190px;

        padding: 25px 15px;

        border: 1.5px dashed #D8D0E8;

        border-radius: 14px;

        background:
            linear-gradient(180deg,
                #FCFAFF,
                #FFFFFF);

        text-align: center;

        transition: var(--transition);

        cursor: pointer;
    }


    .upload-area:hover {

        border-color: var(--purple);

        background: var(--purple-light);

        transform: translateY(-2px);
    }


    .upload-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 54px;
        height: 54px;

        margin-bottom: 13px;

        border-radius: 15px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 24px;
    }


    .upload-title {

        color: var(--black-soft);

        font-size: 13px;

        font-weight: 800;

        margin-bottom: 5px;
    }


    .upload-description {

        max-width: 220px;

        color: var(--muted);

        font-size: 11px;

        line-height: 1.5;
    }


    .upload-input {

        position: absolute;

        inset: 0;

        width: 100%;

        height: 100%;

        opacity: 0;

        cursor: pointer;
    }


    .upload-note {

        margin-top: 10px;

        color: var(--muted);

        font-size: 10px;

        line-height: 1.5;

        text-align: center;
    }


    /* =========================================================
           STATUS
        ========================================================= */

    .status-option {

        position: relative;

        display: flex;

        align-items: center;

        gap: 11px;

        padding: 13px;

        margin-bottom: 9px;

        border: 1px solid var(--border);

        border-radius: 11px;

        cursor: pointer;

        transition: var(--transition);
    }


    .status-option:hover {

        border-color: #D8D0E8;

        background: var(--purple-light);
    }


    .status-option input {

        accent-color: var(--purple);

        width: 16px;
        height: 16px;

        margin: 0;
    }


    .status-info strong {

        display: block;

        color: var(--black-soft);

        font-size: 12px;

        font-weight: 800;
    }


    .status-info span {

        display: block;

        margin-top: 2px;

        color: var(--muted);

        font-size: 10px;
    }


    .status-published-dot,
    .status-draft-dot {

        width: 8px;
        height: 8px;

        border-radius: 50%;

        flex-shrink: 0;
    }


    .status-published-dot {

        background: var(--green);
    }


    .status-draft-dot {

        background: var(--orange);
    }


    /* =========================================================
           ACTION BUTTONS
        ========================================================= */

    .form-actions {

        display: flex;

        align-items: center;

        gap: 10px;

        padding-top: 5px;
    }


    .btn-post {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        min-height: 47px;

        padding: 10px 21px;

        border: none;

        border-radius: 11px;

        background:
            linear-gradient(135deg,
                var(--purple),
                var(--purple-dark));

        color: white;

        font-size: 13px;

        font-weight: 800;

        transition: var(--transition);

        box-shadow:
            0 7px 18px rgba(109, 40, 217, 0.18);
    }


    .btn-post:hover {

        color: white;

        transform: translateY(-2px);

        background:
            linear-gradient(135deg,
                var(--purple-dark),
                var(--purple-deep));

        box-shadow:
            0 11px 24px rgba(109, 40, 217, 0.25);
    }


    .btn-cancel {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-height: 47px;

        padding: 10px 20px;

        border: none;

        border-radius: 11px;

        background: #F3F4F6;

        color: var(--black-soft);

        font-size: 13px;

        font-weight: 700;

        transition: var(--transition);
    }


    .btn-cancel:hover {

        background: var(--purple-soft);

        color: var(--purple-dark);

        transform: translateY(-2px);
    }


    /* =========================================================
           INFO CARD
        ========================================================= */

    .info-list {

        margin: 0;

        padding: 0;

        list-style: none;
    }


    .info-list li {

        display: flex;

        align-items: flex-start;

        gap: 10px;

        padding: 10px 0;

        border-bottom: 1px solid #F1F1F4;

        color: var(--muted);

        font-size: 11px;

        line-height: 1.5;
    }


    .info-list li:last-child {

        border-bottom: none;

        padding-bottom: 0;
    }


    .info-list li:first-child {

        padding-top: 0;
    }


    .info-number {

        display: flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

        width: 22px;
        height: 22px;

        border-radius: 7px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 10px;

        font-weight: 800;
    }


    /* =========================================================
           FOOTER
        ========================================================= */

    .page-footer {

        margin-top: 25px;

        padding: 17px 20px;

        border-top: 1px solid var(--border);

        color: var(--muted);

        font-size: 11px;

        text-align: center;
    }


    .page-footer strong {

        color: var(--purple-dark);
    }


    /* =========================================================
           FOCUS
        ========================================================= */

    button:focus-visible,
    a:focus-visible,
    input:focus-visible,
    textarea:focus-visible,
    select:focus-visible {

        outline: 3px solid rgba(139, 92, 246, 0.22);

        outline-offset: 2px;
    }


    /* =========================================================
           TABLET
        ========================================================= */

    @media (max-width: 1050px) {

        .editor-layout {

            grid-template-columns: 1fr;
        }


        .sidebar {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;
        }


        .sidebar-card+.sidebar-card {

            margin-top: 0;
        }
    }


    /* =========================================================
           MOBILE
        ========================================================= */

    @media (max-width: 768px) {

        .page-wrapper {

            padding:
                18px 14px 40px;
        }


        .page-header {

            flex-direction: column;

            align-items: flex-start;

            padding: 23px;

            border-radius: 18px;
        }


        .header-actions {

            width: 100%;

            justify-content: flex-start;
        }


        .header-actions .btn {

            flex: 1;

            min-width: 120px;
        }


        .editor-body {

            padding: 20px;
        }


        .editor-card-header {

            padding: 18px 20px;
        }


        textarea.form-control {

            min-height: 230px;
        }


        .sidebar {

            display: block;
        }


        .sidebar-card+.sidebar-card {

            margin-top: 15px;
        }


        .form-actions {

            flex-direction: column;

            align-items: stretch;
        }


        .btn-post,
        .btn-cancel {

            width: 100%;
        }
    }


    /* =========================================================
           SMALL MOBILE
        ========================================================= */

    @media (max-width: 480px) {

        .page-header h1 {

            font-size: 25px;
        }


        .header-actions {

            flex-direction: column;
        }


        .header-actions .btn {

            width: 100%;
        }


        .editor-body {

            padding: 16px;
        }


        .upload-area {

            min-height: 170px;
        }
    }


    /* =========================================================
           REDUCED MOTION
        ========================================================= */

    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {

            animation-duration: 0.01ms !important;

            animation-iteration-count: 1 !important;

            transition-duration: 0.01ms !important;

            scroll-behavior: auto !important;
        }
    }
    </style>

</head>


<body>

    <div class="page-wrapper">


        <!-- =========================================================
         PAGE HEADER
    ========================================================= -->

        <header class="page-header">

            <div class="header-content">

                <div class="header-icon">
                    📰
                </div>

                <h1>
                    Post News
                </h1>

                <p>
                    Publish announcements and updates for the MTU Badminton Club.
                </p>

            </div>


            <div class="header-actions">

                <a href="dashboard.php" class="btn btn-dashboard">
                    ← Dashboard
                </a>


                <a href="manage_news.php" class="btn btn-manage">
                    Manage News
                </a>

            </div>

        </header>


        <!-- =========================================================
         SUCCESS MESSAGE
    ========================================================= -->

        <?php if ($message !== ""): ?>

        <div class="custom-alert alert-success-custom">

            <div class="alert-icon">
                ✓
            </div>

            <div class="alert-content">

                <strong>
                    Success
                </strong>

                <span>
                    <?= e($message) ?>
                </span>

            </div>

        </div>

        <?php endif; ?>


        <!-- =========================================================
         ERROR MESSAGE
    ========================================================= -->

        <?php if ($error !== ""): ?>

        <div class="custom-alert alert-error-custom">

            <div class="alert-icon">
                !
            </div>

            <div class="alert-content">

                <strong>
                    Something went wrong
                </strong>

                <span>
                    <?= e($error) ?>
                </span>

            </div>

        </div>

        <?php endif; ?>


        <!-- =========================================================
         EDITOR LAYOUT
    ========================================================= -->

        <div class="editor-layout">


            <!-- =====================================================
             MAIN EDITOR
        ===================================================== -->

            <main class="editor-card">


                <div class="editor-card-header">

                    <div class="editor-card-icon">
                        ✎
                    </div>

                    <div>

                        <h2>
                            News Editor
                        </h2>

                        <p>
                            Create a new announcement for club members.
                        </p>

                    </div>

                </div>


                <div class="editor-body">

                    <form method="POST" enctype="multipart/form-data">

                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                        <!-- =================================================
                         TITLE
                    ================================================= -->

                        <div class="form-section">

                            <label for="news-title" class="form-label">
                                News Title
                                <span class="required">*</span>
                            </label>


                            <input id="news-title" type="text" name="title" class="form-control title-input"
                                placeholder="Enter an attractive news title..." value="<?= e($title ?? '') ?>"
                                maxlength="255" required>

                        </div>


                        <!-- =================================================
                         CONTENT
                    ================================================= -->

                        <div class="form-section">

                            <label for="news-content" class="form-label">
                                News Content
                                <span class="required">*</span>
                            </label>


                            <textarea id="news-content" name="content" class="form-control" rows="10"
                                placeholder="Write your news announcement here..."
                                required><?= e($content ?? '') ?></textarea>


                            <div class="mt-2 text-muted" style="font-size:11px;">
                                Write clear and informative content for club members.
                            </div>

                        </div>


                        <!-- =================================================
                         ACTIONS
                    ================================================= -->

                        <div class="form-actions">

                            <button type="submit" class="btn-post">
                                <span>✓</span>
                                Post News
                            </button>


                            <a href="dashboard.php" class="btn-cancel">
                                Cancel
                            </a>

                        </div>

                    </form>

                </div>

            </main>


            <!-- =====================================================
             SIDEBAR
        ===================================================== -->

            <aside class="sidebar">


                <!-- =================================================
                 IMAGE
            ================================================= -->

                <div class="sidebar-card">

                    <div class="sidebar-header">

                        <h3>
                            Featured Image
                        </h3>

                        <p>
                            Add an image to your announcement.
                        </p>

                    </div>


                    <div class="sidebar-body">

                        <label class="upload-area">

                            <div class="upload-icon">
                                ↑
                            </div>

                            <div class="upload-title">
                                Choose an image
                            </div>

                            <div class="upload-description">
                                Click here to select a JPG, PNG or WEBP image.
                            </div>


                            <input type="file" name="image" class="upload-input" accept=".jpg,.jpeg,.png,.webp"
                                form="newsForm">

                        </label>


                        <div class="upload-note">

                            Maximum file size: <strong>5MB</strong><br>

                            Recommended: high-quality landscape image

                        </div>

                    </div>

                </div>


                <!-- =================================================
                 STATUS
            ================================================= -->

                <div class="sidebar-card">

                    <div class="sidebar-header">

                        <h3>
                            Publication Status
                        </h3>

                        <p>
                            Choose when the news should appear.
                        </p>

                    </div>


                    <div class="sidebar-body">


                        <label class="status-option">

                            <span class="status-published-dot"></span>


                            <input type="radio" name="status" value="Published" checked form="newsForm">


                            <span class="status-info">

                                <strong>
                                    Published
                                </strong>

                                <span>
                                    Visible on the website.
                                </span>

                            </span>

                        </label>


                        <label class="status-option">

                            <span class="status-draft-dot"></span>


                            <input type="radio" name="status" value="Draft" form="newsForm">


                            <span class="status-info">

                                <strong>
                                    Draft
                                </strong>

                                <span>
                                    Save without publishing.
                                </span>

                            </span>

                        </label>

                    </div>

                </div>


                <!-- =================================================
                 QUICK TIPS
            ================================================= -->

                <div class="sidebar-card">

                    <div class="sidebar-header">

                        <h3>
                            Publishing Tips
                        </h3>

                        <p>
                            Keep your announcements effective.
                        </p>

                    </div>


                    <div class="sidebar-body">

                        <ul class="info-list">

                            <li>

                                <span class="info-number">
                                    1
                                </span>

                                <span>
                                    Use a short and descriptive title.
                                </span>

                            </li>


                            <li>

                                <span class="info-number">
                                    2
                                </span>

                                <span>
                                    Include important dates, locations and times.
                                </span>

                            </li>


                            <li>

                                <span class="info-number">
                                    3
                                </span>

                                <span>
                                    Use a clear image that represents the announcement.
                                </span>

                            </li>


                            <li>

                                <span class="info-number">
                                    4
                                </span>

                                <span>
                                    Use Draft when you are not ready to publish.
                                </span>

                            </li>

                        </ul>

                    </div>

                </div>

            </aside>

        </div>


        <!-- =========================================================
         FOOTER
    ========================================================= -->

        <div class="page-footer">

            <strong>MTU Badminton Club</strong>
            &nbsp;•&nbsp;
            Admin News Management

        </div>

    </div>


    <script>
    /*
     * =========================================================
     * CONNECT SIDEBAR CONTROLS TO THE ACTUAL FORM
     * =========================================================
     *
     * The main form needs an ID so the image and status controls
     * in the sidebar can submit together with it.
     */

    const form = document.querySelector('form');

    if (form) {

        form.id = 'newsForm';

    }


    /*
     * =========================================================
     * IMAGE NAME PREVIEW
     * =========================================================
     */

    const imageInput =
        document.querySelector('.upload-input');

    const uploadTitle =
        document.querySelector('.upload-title');

    const uploadDescription =
        document.querySelector('.upload-description');


    if (imageInput) {

        imageInput.addEventListener('change', function() {

            if (this.files && this.files.length > 0) {

                const file = this.files[0];

                uploadTitle.textContent =
                    file.name;

                uploadDescription.textContent =
                    'Image selected successfully.';

            }

        });

    }
    </script>


</body>

</html>