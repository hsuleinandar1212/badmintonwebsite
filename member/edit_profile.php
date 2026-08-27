<?php

session_start();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";

// =====================================================
// CHECK MEMBER LOGIN
// =====================================================

if (!isset($_SESSION['member_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$member_id = $_SESSION['member_id'];

$message = "";
$error = "";


// =====================================================
// GET CURRENT MEMBER DATA
// =====================================================

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$member_id]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die("Member not found.");
}


// =====================================================
// UPDATE PROFILE
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username      = trim($_POST['username'] ?? '');
    $student_id    = trim($_POST['student_id'] ?? '');
    $roll_number   = trim($_POST['roll_number'] ?? '');
    $department    = trim($_POST['department'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $gender        = trim($_POST['gender'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');


    // =================================================
    // KEEP OLD PROFILE PICTURE
    // =================================================

    $profile_picture = $member['profile_picture'] ?? '';


    // =================================================
    // CHECK NEW PROFILE PICTURE
    // =================================================

    if (
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the profile picture.";

        } else {

            $file = $_FILES['profile_picture'];

            // Allowed image types
            $allowed_types = [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp'
            ];

            // Check file type
            if (!in_array($file['type'], $allowed_types)) {

                $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

            }

            // Check file size - maximum 5MB
            elseif ($file['size'] > 5 * 1024 * 1024) {

                $error = "Profile picture must be less than 5MB.";

            }

            else {

                // =============================================
                // UPLOAD DIRECTORY
                // =============================================

                $upload_dir = __DIR__ . "/../uploads/profile/";

                // Create folder if it does not exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }


                // =============================================
                // GET FILE EXTENSION
                // =============================================

                $extension = strtolower(
                    pathinfo($file['name'], PATHINFO_EXTENSION)
                );


                // =============================================
                // CREATE UNIQUE FILE NAME
                // =============================================

                $new_filename =
                    "member_" .
                    $member_id .
                    "_" .
                    time() .
                    "." .
                    $extension;


                $target_file = $upload_dir . $new_filename;


                // =============================================
                // MOVE IMAGE
                // =============================================

                if (move_uploaded_file(
                    $file['tmp_name'],
                    $target_file
                )) {

                    // =========================================
                    // DELETE OLD PROFILE PICTURE
                    // =========================================

                    if (!empty($member['profile_picture'])) {

                        $old_file =
                            __DIR__ .
                            "/../" .
                            $member['profile_picture'];

                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }


                    // =========================================
                    // SAVE NEW PATH
                    // =========================================

                    $profile_picture =
                        "uploads/profile/" .
                        $new_filename;

                } else {

                    $error = "Failed to save the profile picture.";
                }
            }
        }
    }


    // =====================================================
    // UPDATE DATABASE
    // =====================================================

    if (empty($error)) {

        $update = $pdo->prepare("
            UPDATE members SET
                username = ?,
                student_id = ?,
                roll_number = ?,
                department = ?,
                academic_year = ?,
                gender = ?,
                phone = ?,
                email = ?,
                profile_picture = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        if ($update->execute([
            $username,
            $student_id,
            $roll_number,
            $department,
            $academic_year,
            $gender,
            $phone,
            $email,
            $profile_picture,
            $member_id
        ])) {

            $message = "Profile updated successfully!";


            // =============================================
            // GET UPDATED MEMBER DATA
            // =============================================

            $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$member_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

        } else {

            $error = "Failed to update profile.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Profile - MTU Badminton Club</title>


    <style>
    /* =========================================================
   MTU BADMINTON CLUB
   EDIT PROFILE - PREMIUM DESIGN
========================================================= */

    :root {

        --skyblue: #87ceeb;
        --deep-sky: #00bfff;

        --orange: #fb8500;
        --yellow: #ffb703;

        --dark: #17212b;
        --muted: #607d8b;

        --white: #ffffff;

        --light-blue: #eef9ff;
        --light-orange: #fff7ed;

        --border-blue: rgba(135, 206, 235, .35);
        --border-orange: rgba(251, 133, 0, .30);

        --shadow:
            0 25px 70px rgba(23, 33, 43, .12);
    }


    /* =========================================================
   RESET
========================================================= */

    * {

        box-sizing: border-box;

    }


    /* =========================================================
   BODY
========================================================= */

    body {

        margin: 0;

        min-height: 100vh;

        font-family:
            "Segoe UI",
            Arial,
            sans-serif;

        color: var(--dark);

        background:

            radial-gradient(circle at 10% 10%,
                rgba(135, 206, 235, .45),
                transparent 35%),

            radial-gradient(circle at 90% 20%,
                rgba(255, 183, 3, .25),
                transparent 35%),

            radial-gradient(circle at 50% 100%,
                rgba(251, 133, 0, .16),
                transparent 40%),

            linear-gradient(135deg,
                #e5f7ff 0%,
                #ffffff 48%,
                #fff7ec 100%);

        overflow-x: hidden;

    }


    /* =========================================================
   DECORATIVE BACKGROUND
========================================================= */

    body::before {

        content: "";

        position: fixed;

        width: 330px;
        height: 330px;

        border-radius: 50%;

        left: -170px;
        top: 130px;

        background:
            rgba(135, 206, 235, .28);

        filter:
            blur(80px);

        pointer-events: none;

        z-index: -1;

        animation:
            editFloatOne 10s ease-in-out infinite alternate;

    }


    body::after {

        content: "";

        position: fixed;

        width: 380px;
        height: 380px;

        border-radius: 50%;

        right: -190px;
        bottom: -120px;

        background:
            rgba(251, 133, 0, .18);

        filter:
            blur(90px);

        pointer-events: none;

        z-index: -1;

        animation:
            editFloatTwo 12s ease-in-out infinite alternate;

    }


    @keyframes editFloatOne {

        from {

            transform:
                translate(0, 0) scale(1);

        }

        to {

            transform:
                translate(50px, -40px) scale(1.18);

        }

    }


    @keyframes editFloatTwo {

        from {

            transform:
                translate(0, 0) scale(1);

        }

        to {

            transform:
                translate(-45px, -30px) scale(1.15);

        }

    }


    /* =========================================================
   MAIN CONTAINER
========================================================= */

    .container {

        width: min(94%, 950px);

        margin:
            55px auto 80px;

        position: relative;

        animation:
            editPageAppear .8s ease both;

    }


    @keyframes editPageAppear {

        from {

            opacity: 0;

            transform:
                translateY(30px);

        }

        to {

            opacity: 1;

            transform:
                translateY(0);

        }

    }


    /* =========================================================
   MAIN CARD
========================================================= */

    .card {

        position: relative;

        background:
            rgba(255, 255, 255, .88);

        border:
            1px solid rgba(255, 255, 255, .9);

        border-radius:
            30px;

        padding:
            45px;

        box-shadow:
            var(--shadow);

        backdrop-filter:
            blur(20px);

        -webkit-backdrop-filter:
            blur(20px);

        overflow: hidden;

    }


    /* TOP GRADIENT LINE */

    .card::before {

        content: "";

        position: absolute;

        top: 0;
        left: 0;
        right: 0;

        height: 6px;

        background:
            linear-gradient(90deg,
                var(--skyblue),
                var(--yellow),
                var(--orange),
                var(--skyblue));

        background-size:
            200% auto;

        animation:
            editGradient 5s linear infinite;

    }


    @keyframes editGradient {

        to {

            background-position:
                200% center;

        }

    }


    /* =========================================================
   PAGE TITLE
========================================================= */

    .card>h2 {

        margin:
            0 0 30px;

        font-size:
            clamp(32px, 5vw, 48px);

        font-weight:
            900;

        letter-spacing:
            -1px;

        color:
            var(--dark);

    }


    .card>h2::after {

        content: "";

        display: block;

        width: 75px;

        height: 5px;

        margin-top: 14px;

        border-radius:
            20px;

        background:
            linear-gradient(90deg,
                var(--yellow),
                var(--orange));

        box-shadow:
            0 5px 15px rgba(251, 133, 0, .25);

    }


    /* =========================================================
   SUCCESS MESSAGE
========================================================= */

    .success {

        position: relative;

        padding:
            15px 18px;

        margin-bottom:
            25px;

        border:
            1px solid rgba(34, 197, 94, .25);

        border-radius:
            14px;

        background:
            linear-gradient(135deg,
                rgba(220, 252, 231, .95),
                rgba(240, 253, 244, .90));

        color:
            #166534;

        font-size:
            13px;

        font-weight:
            700;

        box-shadow:
            0 8px 25px rgba(34, 197, 94, .08);

        animation:
            messageAppear .5s ease both;

    }


    .success::before {

        content:
            "✓";

        display:
            inline-flex;

        align-items:
            center;

        justify-content:
            center;

        width:
            25px;

        height:
            25px;

        margin-right:
            8px;

        border-radius:
            50%;

        background:
            #22c55e;

        color:
            white;

        font-weight:
            900;

    }


    .success {
        display:
            flex;

        align-items:
            center;
    }


    @keyframes messageAppear {

        from {

            opacity: 0;

            transform:
                translateY(-10px);

        }

        to {

            opacity: 1;

            transform:
                translateY(0);

        }

    }


    /* =========================================================
   ERROR MESSAGE
========================================================= */

    .error {

        position: relative;

        padding:
            15px 18px;

        margin-bottom:
            25px;

        border:
            1px solid rgba(239, 68, 68, .25);

        border-radius:
            14px;

        background:
            linear-gradient(135deg,
                rgba(254, 226, 226, .95),
                rgba(255, 245, 245, .90));

        color:
            #991b1b;

        font-size:
            13px;

        font-weight:
            700;

        box-shadow:
            0 8px 25px rgba(239, 68, 68, .08);

        animation:
            messageAppear .5s ease both;

    }


    .error::before {

        content:
            "!";

        display:
            inline-flex;

        align-items:
            center;

        justify-content:
            center;

        width:
            25px;

        height:
            25px;

        margin-right:
            8px;

        border-radius:
            50%;

        background:
            #ef4444;

        color:
            white;

        font-weight:
            900;

    }


    .error {

        display:
            flex;

        align-items:
            center;

    }


    /* =========================================================
   PROFILE SECTION
========================================================= */

    .profile-section {

        position: relative;

        text-align:
            center;

        margin-bottom:
            38px;

        padding:
            30px 25px;

        border-radius:
            22px;

        background:
            linear-gradient(135deg,
                rgba(238, 249, 255, .85),
                rgba(255, 248, 238, .75));

        border:
            1px solid rgba(135, 206, 235, .25);

    }


    /* =========================================================
   PROFILE IMAGE
========================================================= */

    .profile-image,
    .default-image {

        width:
            155px;

        height:
            155px;

        margin:
            0 auto;

        border-radius:
            50%;

        border:
            5px solid white;

        box-shadow:

            0 0 0 4px var(--skyblue),

            0 15px 35px rgba(23, 33, 43, .16);

        transition:
            .45s ease;

    }


    .profile-image {

        display:
            block;

        object-fit:
            cover;

        background:
            #e8f8ff;

    }


    .profile-image:hover {

        transform:
            scale(1.06);

        box-shadow:

            0 0 0 5px var(--orange),

            0 18px 40px rgba(251, 133, 0, .25);

    }


    .default-image {

        display:
            inline-flex;

        align-items:
            center;

        justify-content:
            center;

        background:
            linear-gradient(135deg,
                #dff6ff,
                #fff1dc);

        font-size:
            55px;

        color:
            var(--muted);

    }


    /* =========================================================
   UPLOAD LABEL
========================================================= */

    .upload-label {

        display:
            block;

        margin-top:
            20px;

        margin-bottom:
            10px;

        color:
            var(--dark);

        font-size:
            13px;

        font-weight:
            900;

        letter-spacing:
            .5px;

    }


    /* =========================================================
   FILE INPUT
========================================================= */

    .profile-section input[type="file"] {

        display:
            block;

        width:
            min(100%, 420px);

        margin:
            0 auto;

        padding:
            11px 13px;

        border:
            1px dashed var(--skyblue);

        border-radius:
            12px;

        background:
            rgba(255, 255, 255, .8);

        color:
            var(--muted);

        font-size:
            12px;

        cursor:
            pointer;

        transition:
            .3s;

    }


    .profile-section input[type="file"]:hover {

        border-color:
            var(--orange);

        background:
            rgba(255, 248, 238, .95);

    }


    .profile-section input[type="file"]::file-selector-button {

        margin-right:
            12px;

        padding:
            8px 13px;

        border:
            none;

        border-radius:
            9px;

        background:
            linear-gradient(135deg,
                var(--skyblue),
                var(--deep-sky));

        color:
            white;

        font-weight:
            800;

        font-size:
            11px;

        cursor:
            pointer;

        transition:
            .3s;

    }


    .profile-section input[type="file"]::file-selector-button:hover {

        background:
            linear-gradient(135deg,
                var(--orange),
                var(--yellow));

    }


    /* =========================================================
   FORM GRID
========================================================= */

    .row {

        display:
            grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap:
            22px;

        margin-bottom:
            0;

    }


    /* =========================================================
   FORM GROUP SPACING
========================================================= */

    .row>div {

        min-width:
            0;

    }


    /* =========================================================
   LABEL
========================================================= */

    label {

        display:
            block;

        margin-bottom:
            8px;

        color:
            var(--dark);

        font-size:
            11px;

        font-weight:
            900;

        letter-spacing:
            1px;

        text-transform:
            uppercase;

    }


    /* =========================================================
   INPUT / SELECT
========================================================= */

    input,
    select {

        width:
            100%;

        height:
            50px;

        margin-bottom:
            22px;

        padding:
            0 15px;

        border:
            1px solid rgba(135, 206, 235, .35);

        border-radius:
            13px;

        outline:
            none;

        background:
            rgba(255, 255, 255, .82);

        color:
            var(--dark);

        font-family:
            inherit;

        font-size:
            14px;

        font-weight:
            600;

        box-shadow:
            0 5px 15px rgba(23, 33, 43, .035);

        transition:
            border-color .3s,
            box-shadow .3s,
            transform .3s,
            background .3s;

    }


    input:hover,
    select:hover {

        border-color:
            rgba(0, 191, 255, .55);

        background:
            white;

    }


    input:focus,
    select:focus {

        border-color:
            var(--orange);

        background:
            white;

        box-shadow:
            0 0 0 4px rgba(251, 133, 0, .10),

            0 8px 20px rgba(251, 133, 0, .08);

        transform:
            translateY(-1px);

    }


    /* =========================================================
   SELECT
========================================================= */

    select {

        cursor:
            pointer;

    }


    /* =========================================================
   INPUT PLACEHOLDER
========================================================= */

    input::placeholder {

        color:
            #9aaab3;

    }


    /* =========================================================
   EMAIL FIELD
========================================================= */

    input[type="email"] {

        background:
            linear-gradient(135deg,
                rgba(238, 249, 255, .75),
                rgba(255, 255, 255, .85));

    }


    /* =========================================================
   BUTTON CONTAINER
========================================================= */

    .buttons {

        display:
            flex;

        flex-wrap:
            wrap;

        gap:
            12px;

        margin-top:
            12px;

        padding-top:
            25px;

        border-top:
            1px solid rgba(135, 206, 235, .25);

    }


    /* =========================================================
   ALL BUTTONS
========================================================= */

    .btn {

        min-height:
            48px;

        padding:
            12px 22px;

        border:
            none;

        border-radius:
            13px;

        text-decoration:
            none;

        display:
            inline-flex;

        align-items:
            center;

        justify-content:
            center;

        font-family:
            inherit;

        font-size:
            12px;

        font-weight:
            900;

        letter-spacing:
            .4px;

        cursor:
            pointer;

        transition:
            .35s ease;

    }


    /* =========================================================
   SAVE BUTTON
========================================================= */

    .save-btn {

        color:
            white;

        background:
            linear-gradient(135deg,
                var(--orange),
                var(--yellow));

        box-shadow:
            0 8px 22px rgba(251, 133, 0, .22);

    }


    .save-btn:hover {

        color:
            white;

        transform:
            translateY(-4px);

        box-shadow:
            0 13px 30px rgba(251, 133, 0, .35);

    }


    /* =========================================================
   CANCEL BUTTON
========================================================= */

    .back-btn {

        color:
            var(--dark);

        background:
            #edf2f5;

        border:
            1px solid #d7e0e5;

    }


    .back-btn:hover {

        color:
            white;

        background:
            #607d8b;

        border-color:
            #607d8b;

        transform:
            translateY(-4px);

    }


    /* =========================================================
   HOME / BACK BUTTON
========================================================= */

    .home-btn {

        color:
            white;

        background:
            linear-gradient(135deg,
                var(--deep-sky),
                #168aad);

        box-shadow:
            0 8px 20px rgba(0, 191, 255, .18);

    }


    .home-btn:hover {

        color:
            white;

        transform:
            translateY(-4px);

        background:
            linear-gradient(135deg,
                #168aad,
                var(--deep-sky));

        box-shadow:
            0 12px 25px rgba(0, 191, 255, .28);

    }


    /* =========================================================
   BUTTON ACTIVE
========================================================= */

    .btn:active {

        transform:
            translateY(0) scale(.98);

    }


    /* =========================================================
   RESPONSIVE TABLET
========================================================= */

    @media (max-width: 700px) {

        .container {

            width:
                94%;

            margin:
                30px auto 50px;

        }

        .card {

            padding:
                30px 24px;

            border-radius:
                24px;

        }

        .row {

            grid-template-columns:
                1fr;

            gap:
                0;

        }

        .card>h2 {

            font-size:
                36px;

        }

    }


    /* =========================================================
   MOBILE
========================================================= */

    @media (max-width: 480px) {

        .container {

            width:
                95%;

            margin:
                20px auto 40px;

        }

        .card {

            padding:
                25px 18px;

            border-radius:
                22px;

        }

        .card>h2 {

            font-size:
                31px;

            margin-bottom:
                25px;

        }

        .profile-section {

            padding:
                25px 15px;

            border-radius:
                18px;

        }

        .profile-image,
        .default-image {

            width:
                125px;

            height:
                125px;

        }

        .default-image {

            font-size:
                45px;

        }

        input,
        select {

            height:
                48px;

            font-size:
                13px;

        }

        .buttons {

            flex-direction:
                column;

        }

        .btn {

            width:
                100%;

        }

    }


    /* =========================================================
   REDUCED MOTION
========================================================= */

    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {

            animation:
                none !important;

            transition:
                none !important;

        }

    }
    </style>
</head>


<body>


    <div class="container">


        <div class="card">


            <h2>Edit Profile</h2>


            <!-- SUCCESS MESSAGE -->

            <?php if (!empty($message)): ?>

            <div class="success">

                <?= htmlspecialchars($message) ?>

            </div>

            <?php endif; ?>


            <!-- ERROR MESSAGE -->

            <?php if (!empty($error)): ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

            <?php endif; ?>


            <!-- ========================================= -->
            <!-- PROFILE FORM -->
            <!-- ========================================= -->

            <form method="POST" enctype="multipart/form-data">


                <!-- PROFILE PICTURE -->

                <div class="profile-section">


                    <?php

                $image_path = "";

                if (!empty($member['profile_picture'])) {

                    $image_path =
                        "../" .
                        $member['profile_picture'];
                }

                ?>


                    <?php if (
                    !empty($member['profile_picture']) &&
                    file_exists(
                        __DIR__ .
                        "/../" .
                        $member['profile_picture']
                    )
                ): ?>


                    <img src="<?= htmlspecialchars($image_path) ?>" class="profile-image" alt="Profile Picture">


                    <?php else: ?>


                    <div class="default-image">

                        👤

                    </div>


                    <?php endif; ?>


                    <label class="upload-label">

                        Change Profile Picture

                    </label>


                    <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">


                </div>


                <!-- USERNAME -->

                <label>

                    Username

                </label>


                <input type="text" name="username" value="<?= htmlspecialchars(
                    $member['username'] ?? ''
                ) ?>" required>


                <!-- STUDENT ID + ROLL NUMBER -->

                <div class="row">


                    <div>

                        <label>

                            Student ID

                        </label>


                        <input type="text" name="student_id" value="<?= htmlspecialchars(
                            $member['student_id'] ?? ''
                        ) ?>" required>

                    </div>


                    <div>

                        <label>

                            Roll Number

                        </label>


                        <input type="text" name="roll_number" value="<?= htmlspecialchars(
                            $member['roll_number'] ?? ''
                        ) ?>" required>

                    </div>


                </div>


                <!-- DEPARTMENT + ACADEMIC YEAR -->

                <div class="row">


                    <div>

                        <label>

                            Department

                        </label>


                        <input type="text" name="department" value="<?= htmlspecialchars(
                            $member['department'] ?? ''
                        ) ?>" required>

                    </div>


                    <div>

                        <label>

                            Academic Year

                        </label>


                        <input type="text" name="academic_year" value="<?= htmlspecialchars(
                            $member['academic_year'] ?? ''
                        ) ?>" required>

                    </div>


                </div>


                <!-- GENDER + PHONE -->

                <div class="row">


                    <div>

                        <label>

                            Gender

                        </label>


                        <select name="gender" required>


                            <option value="Male" <?= (
                                ($member['gender'] ?? '') === 'Male'
                            )
                            ? 'selected'
                            : ''
                            ?>>

                                Male

                            </option>


                            <option value="Female" <?= (
                                ($member['gender'] ?? '') === 'Female'
                            )
                            ? 'selected'
                            : ''
                            ?>>

                                Female

                            </option>


                        </select>

                    </div>


                    <div>

                        <label>

                            Phone

                        </label>


                        <input type="text" name="phone" value="<?= htmlspecialchars(
                            $member['phone'] ?? ''
                        ) ?>" required>

                    </div>


                </div>


                <!-- EMAIL -->

                <label>

                    Email

                </label>


                <input type="email" name="email" value="<?= htmlspecialchars(
                    $member['email'] ?? ''
                ) ?>" required>


                <!-- BUTTONS -->

                <div class="buttons">

                    <button type="submit" class="btn save-btn">
                        Save Changes
                    </button>

                    <a href="dashboard.php" class="btn back-btn">
                        Cancel
                    </a>

                    <a href="dashboard.php" class="btn home-btn">
                        🏠 Back
                    </a>

                </div>


            </form>


        </div>


    </div>


</body>

</html>