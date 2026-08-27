<?php

session_start();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";

// Ensure the database connection is available in this scope.
$conn = $GLOBALS['conn'] ?? $GLOBALS['mysqli'] ?? null;


// =====================================================
// CHECK MEMBER LOGIN
// =====================================================

if (!isset($_SESSION['member_id'])) {

    header("Location: ../public/login.php");
    exit();

}


// =====================================================
// GET MEMBER ID
// =====================================================

$member_id = $_SESSION['member_id'];


// =====================================================
// GET LATEST MEMBER INFORMATION FROM DATABASE
// =====================================================

$stmt = $pdo->prepare("
    SELECT
        id,
        username,
        student_id,
        roll_number,
        department,
        academic_year,
        gender,
        phone,
        email,
        profile_picture,
        status,
        created_at
    FROM members
    WHERE id = ?
    LIMIT 1
");

$stmt->bindValue(1, $member_id, PDO::PARAM_INT);

$stmt->execute();

$member = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt->closeCursor();


// =====================================================
// MEMBER NOT FOUND
// =====================================================

if (!$member) {

    session_unset();
    session_destroy();

    header("Location: ../public/login.php");
    exit();

}


// =====================================================
// PROFILE PICTURE
// =====================================================

$profileImage = "";
$profileImageExists = false;


// Check if database contains a profile picture
if (!empty($member['profile_picture'])) {

    $storedImage = trim($member['profile_picture']);


    // -------------------------------------------------
    // Remove ../ from beginning if it exists
    // -------------------------------------------------

    while (strpos($storedImage, "../") === 0) {

        $storedImage = substr($storedImage, 3);

    }


    // -------------------------------------------------
    // If database contains only filename
    // Example:
    // member_5_123456.jpg
    // -------------------------------------------------

    if (
        strpos($storedImage, '/') === false &&
        strpos($storedImage, '\\') === false
    ) {

        $storedImage =
            "uploads/profile/" .
            basename($storedImage);

    }


    // -------------------------------------------------
    // Browser path
    // -------------------------------------------------

    $profileImage =
        "../" .
        ltrim($storedImage, "/");


    // -------------------------------------------------
    // Physical file path
    // -------------------------------------------------

    $serverImagePath =
        __DIR__ .
        "/../" .
        ltrim($storedImage, "/");


    // -------------------------------------------------
    // Check if image exists
    // -------------------------------------------------

    if (file_exists($serverImagePath)) {

        $profileImageExists = true;

    }


    // -------------------------------------------------
    // If old database path uses uploads/profiles/
    // check that too
    // -------------------------------------------------

    if (!$profileImageExists) {

        if (
            strpos(
                $storedImage,
                "uploads/profiles/"
            ) === 0
        ) {

            $oldPath =
                __DIR__ .
                "/../" .
                $storedImage;

            if (file_exists($oldPath)) {

                $profileImage =
                    "../" .
                    $storedImage;

                $profileImageExists = true;

            }

        }

    }

}


// =====================================================
// CACHE BUSTER FOR PROFILE IMAGE
// =====================================================

$imageVersion = time();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">

    <meta http-equiv="Pragma" content="no-cache">

    <meta http-equiv="Expires" content="0">

    <title>
        Member Dashboard | MTU Badminton Club
    </title>


    <!-- =================================================
         BOOTSTRAP
    ================================================== -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- =================================================
         CUSTOM CSS
    ================================================== -->

    <style>
    /* =========================================================
   MTU BADMINTON CLUB - MEMBER DASHBOARD
   Premium Sky Blue / Orange / Yellow Design
========================================================= */

    :root {
        --skyblue: #87ceeb;
        --deep-sky: #00bfff;
        --orange: #fb8500;
        --yellow: #ffb703;

        --dark: #17212b;
        --muted: #607d8b;

        --white: #ffffff;
        --light: #f4fbff;

        --glass: rgba(255, 255, 255, 0.88);

        --shadow:
            0 20px 55px rgba(23, 33, 43, 0.10);

        --orange-shadow:
            0 15px 40px rgba(251, 133, 0, 0.16);
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

        font-family:
            "Segoe UI",
            Arial,
            sans-serif;

        color: var(--dark);

        background:
            radial-gradient(circle at 10% 10%,
                rgba(135, 206, 235, .38),
                transparent 35%),

            radial-gradient(circle at 90% 20%,
                rgba(255, 183, 3, .20),
                transparent 35%),

            radial-gradient(circle at 50% 100%,
                rgba(251, 133, 0, .12),
                transparent 40%),

            linear-gradient(135deg,
                #eaf8ff 0%,
                #ffffff 48%,
                #fff8ee 100%);

        overflow-x: hidden;
    }


    /* =========================================================
   NAVBAR
========================================================= */

    .navbar {

        position: relative;

        min-height: 76px;

        background:
            linear-gradient(135deg,
                #0b2638,
                #123e56) !important;

        border-bottom:
            3px solid var(--orange);

        box-shadow:
            0 8px 30px rgba(0, 0, 0, .12);

        z-index: 100;
    }

    .navbar::after {

        content: "";

        position: absolute;

        left: 0;
        right: 0;
        bottom: -3px;

        height: 3px;

        background:
            linear-gradient(90deg,
                var(--skyblue),
                var(--yellow),
                var(--orange),
                var(--skyblue));

        background-size: 200% auto;

        animation:
            dashboardGradient 5s linear infinite;
    }

    @keyframes dashboardGradient {

        to {
            background-position: 200% center;
        }
    }


    .navbar .container {

        min-height: 76px;
    }


    .navbar-brand {

        font-size: 19px;

        font-weight: 900;

        letter-spacing: .5px;

        color: white !important;

        transition: .3s;
    }

    .navbar-brand:hover {

        transform:
            translateY(-2px);

        color:
            var(--yellow) !important;
    }


    /* =========================================================
   NAVBAR BUTTONS
========================================================= */

    .navbar .btn {

        border: none;

        border-radius: 12px;

        padding:
            9px 16px;

        font-size: 12px;

        font-weight: 800;

        transition: .3s;

        margin-left: 6px;
    }

    .navbar .btn-light {

        color:
            var(--dark);

        background:
            rgba(255, 255, 255, .95);

        box-shadow:
            0 5px 15px rgba(0, 0, 0, .10);
    }

    .navbar .btn-light:hover {

        transform:
            translateY(-3px);

        background:
            var(--yellow);

        color:
            var(--dark);

        box-shadow:
            0 8px 20px rgba(255, 183, 3, .30);
    }


    /* =========================================================
   MAIN CONTAINER
========================================================= */

    .container.py-5 {

        position: relative;

        max-width: 1250px;

        padding-top: 45px !important;

        padding-bottom: 70px !important;
    }


    /* =========================================================
   PAGE TITLE
========================================================= */

    .container.py-5>.mb-4 {

        position: relative;

        margin-bottom: 35px !important;

        animation:
            dashboardFadeDown .8s ease both;
    }

    @keyframes dashboardFadeDown {

        from {
            opacity: 0;
            transform: translateY(-25px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    .container.py-5>.mb-4 h2 {

        margin: 0;

        font-size:
            clamp(34px, 5vw, 52px);

        line-height: 1;

        font-weight: 900;

        letter-spacing: -1px;

        color: var(--dark);
    }

    .container.py-5>.mb-4 h2::after {

        content: "";

        display: block;

        width: 85px;

        height: 5px;

        margin-top: 15px;

        border-radius: 10px;

        background:
            linear-gradient(90deg,
                var(--yellow),
                var(--orange));

        box-shadow:
            0 5px 15px rgba(251, 133, 0, .25);
    }

    .container.py-5>.mb-4 p {

        margin-top: 15px;

        color: var(--muted);

        font-size: 14px;

        letter-spacing: .4px;
    }


    /* =========================================================
   ROW
========================================================= */

    .row.g-4 {

        animation:
            dashboardCardsAppear .9s ease .15s both;
    }

    @keyframes dashboardCardsAppear {

        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    /* =========================================================
   ALL CARDS
========================================================= */

    .profile-card {

        position: relative;

        height: 100%;

        border:
            1px solid rgba(255, 255, 255, .8) !important;

        border-radius: 28px !important;

        background:
            var(--glass) !important;

        backdrop-filter:
            blur(18px);

        -webkit-backdrop-filter:
            blur(18px);

        box-shadow:
            var(--shadow) !important;

        overflow: hidden;

        transition:
            transform .4s ease,
            box-shadow .4s ease;
    }

    .profile-card::before {

        content: "";

        position: absolute;

        top: 0;
        left: 0;
        right: 0;

        height: 5px;

        background:
            linear-gradient(90deg,
                var(--skyblue),
                var(--yellow),
                var(--orange));
    }

    .profile-card:hover {

        transform:
            translateY(-7px);

        box-shadow:
            var(--orange-shadow) !important;
    }


    /* =========================================================
   PROFILE CARD
========================================================= */

    .col-lg-4 .profile-card {

        background:
            linear-gradient(145deg,
                rgba(255, 255, 255, .94),
                rgba(235, 249, 255, .90)) !important;
    }

    .col-lg-4 .card-body {

        padding:
            38px 30px !important;
    }


    /* =========================================================
   PROFILE IMAGE
========================================================= */

    .profile-image,
    .no-photo {

        width: 145px !important;

        height: 145px !important;

        margin: 5px auto 0 !important;

        border-radius: 50% !important;

        position: relative;

        border:
            5px solid white !important;

        box-shadow:
            0 0 0 4px var(--skyblue),
            0 12px 30px rgba(0, 0, 0, .16);

        transition:
            transform .5s ease,
            box-shadow .5s ease;
    }

    .profile-image {

        object-fit: cover;

        display: block;

        background:
            #e8f8ff;
    }

    .profile-image:hover {

        transform:
            scale(1.05);

        box-shadow:
            0 0 0 5px var(--orange),
            0 15px 35px rgba(251, 133, 0, .25);
    }


    .no-photo {

        background:
            linear-gradient(135deg,
                #dff6ff,
                #fff4df);

        color:
            var(--muted);

        font-size: 13px;

        font-weight: 800;
    }


    /* =========================================================
   USERNAME
========================================================= */

    .col-lg-4 h4 {

        margin-top: 25px !important;

        font-size: 25px;

        font-weight: 900;

        color: var(--dark);

        letter-spacing: -.3px;
    }


    /* =========================================================
   STATUS BADGE
========================================================= */

    .col-lg-4 .badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding:
            8px 14px;

        border-radius: 30px;

        font-size: 11px;

        font-weight: 800;

        letter-spacing: .5px;

        box-shadow:
            0 5px 15px rgba(0, 0, 0, .10);
    }

    .col-lg-4 .badge.bg-success {

        background:
            linear-gradient(135deg,
                #16a34a,
                #22c55e) !important;
    }

    .col-lg-4 .badge.bg-warning {

        background:
            linear-gradient(135deg,
                var(--yellow),
                #ffd166) !important;

        color:
            #17212b !important;
    }

    .col-lg-4 .badge.bg-danger {

        background:
            linear-gradient(135deg,
                #dc2626,
                #ef4444) !important;
    }


    /* =========================================================
   DIVIDER
========================================================= */

    .profile-card hr {

        margin:
            28px 0;

        border: 0;

        height: 1px;

        background:
            linear-gradient(90deg,
                transparent,
                rgba(135, 206, 235, .55),
                rgba(251, 133, 0, .45),
                transparent);

        opacity: 1;
    }


    /* =========================================================
   PROFILE BUTTONS
========================================================= */

    .profile-card .btn {

        min-height: 46px;

        border-radius: 13px;

        font-size: 12px;

        font-weight: 800;

        letter-spacing: .3px;

        transition: .35s;
    }

    .profile-card .btn-primary {

        border: none;

        background:
            linear-gradient(135deg,
                var(--deep-sky),
                #168aad);

        box-shadow:
            0 8px 20px rgba(0, 191, 255, .20);
    }

    .profile-card .btn-primary:hover {

        transform:
            translateY(-3px);

        background:
            linear-gradient(135deg,
                var(--orange),
                var(--yellow));

        box-shadow:
            0 10px 25px rgba(251, 133, 0, .30);
    }

    .profile-card .btn-outline-primary {

        color:
            #087ea4;

        border:
            2px solid var(--skyblue);

        background:
            rgba(255, 255, 255, .65);
    }

    .profile-card .btn-outline-primary:hover {

        color:
            white;

        border-color:
            var(--orange);

        background:
            linear-gradient(135deg,
                var(--orange),
                var(--yellow));

        transform:
            translateY(-3px);
    }


    /* =========================================================
   INFORMATION CARD
========================================================= */

    .col-lg-8 .card-body {

        padding:
            38px !important;
    }

    .col-lg-8 h4 {

        font-size: 25px;

        font-weight: 900;

        margin-bottom: 30px !important;

        color:
            var(--dark);
    }

    .col-lg-8 h4::after {

        content: "";

        display: block;

        width: 60px;

        height: 4px;

        margin-top: 12px;

        border-radius: 10px;

        background:
            linear-gradient(90deg,
                var(--yellow),
                var(--orange));
    }


    /* =========================================================
   INFORMATION ITEMS
========================================================= */

    .col-lg-8 .row.g-4>div {

        position: relative;

        padding: 18px 20px;

        border-radius: 16px;

        background:
            rgba(238, 249, 255, .65);

        border:
            1px solid rgba(135, 206, 235, .20);

        transition:
            transform .3s ease,
            background .3s ease,
            border-color .3s ease;
    }

    .col-lg-8 .row.g-4>div:hover {

        transform:
            translateY(-4px);

        background:
            rgba(255, 248, 238, .85);

        border-color:
            rgba(251, 133, 0, .30);
    }


    /* =========================================================
   LABEL
========================================================= */

    .info-label {

        font-size: 10px !important;

        color:
            var(--orange) !important;

        font-weight: 900 !important;

        letter-spacing: 1.5px;

        margin-bottom: 7px !important;
    }


    /* =========================================================
   VALUE
========================================================= */

    .info-value {

        font-size: 15px !important;

        font-weight: 700 !important;

        color:
            var(--dark) !important;

        line-height: 1.5;

        word-break: break-word;
    }


    /* =========================================================
   EMAIL SPECIAL
========================================================= */

    .col-lg-8 .col-md-12 {

        background:
            linear-gradient(135deg,
                rgba(135, 206, 235, .12),
                rgba(255, 183, 3, .08)) !important;
    }


    /* =========================================================
   DECORATIVE BACKGROUND
========================================================= */

    body::before {

        content: "";

        position: fixed;

        width: 300px;

        height: 300px;

        border-radius: 50%;

        top: 120px;

        left: -150px;

        background:
            rgba(135, 206, 235, .25);

        filter:
            blur(80px);

        pointer-events: none;

        z-index: -1;

        animation:
            dashboardFloat 9s ease-in-out infinite alternate;
    }

    body::after {

        content: "";

        position: fixed;

        width: 350px;

        height: 350px;

        border-radius: 50%;

        right: -170px;

        bottom: -120px;

        background:
            rgba(251, 133, 0, .16);

        filter:
            blur(90px);

        pointer-events: none;

        z-index: -1;

        animation:
            dashboardFloat 11s ease-in-out infinite alternate-reverse;
    }

    @keyframes dashboardFloat {

        from {
            transform:
                translate(0, 0) scale(1);
        }

        to {
            transform:
                translate(40px, -30px) scale(1.15);
        }
    }


    /* =========================================================
   TABLET
========================================================= */

    @media (max-width: 991px) {

        .container.py-5 {

            padding-top:
                35px !important;
        }

        .col-lg-4 .profile-card {

            max-width:
                520px;

            margin:
                0 auto;
        }

        .col-lg-8 .profile-card {

            margin-top:
                5px;
        }
    }


    /* =========================================================
   MOBILE
========================================================= */

    @media (max-width: 768px) {

        .navbar {

            min-height:
                auto;

            padding:
                12px 0;
        }

        .navbar .container {

            flex-direction:
                column;

            gap: 12px;

            padding:
                5px 20px;
        }

        .navbar-brand {

            font-size:
                17px;
        }

        .navbar .d-flex {

            width:
                100%;

            justify-content:
                center;

            flex-wrap:
                wrap;
        }

        .navbar .d-flex>span {

            width:
                100%;

            text-align:
                center;

            margin:
                0 0 8px !important;
        }

        .container.py-5 {

            padding:
                30px 18px 50px !important;
        }

        .container.py-5>.mb-4 h2 {

            font-size:
                36px;
        }

        .col-lg-4 .card-body,
        .col-lg-8 .card-body {

            padding:
                28px 22px !important;
        }

        .profile-image,
        .no-photo {

            width:
                125px !important;

            height:
                125px !important;
        }

        .col-lg-8 .row.g-4>div {

            padding:
                15px 17px;
        }
    }


    /* =========================================================
   SMALL MOBILE
========================================================= */

    @media (max-width: 480px) {

        .navbar-brand {

            font-size:
                15px;
        }

        .navbar .btn {

            padding:
                8px 12px;

            font-size:
                10px;
        }

        .container.py-5>.mb-4 h2 {

            font-size:
                31px;
        }

        .container.py-5>.mb-4 p {

            font-size:
                12px;
        }

        .col-lg-4 h4 {

            font-size:
                22px;
        }

        .col-lg-8 h4 {

            font-size:
                22px;
        }

        .info-value {

            font-size:
                14px !important;
        }
    }


    /* =========================================================
   REDUCED MOTION
========================================================= */

    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {

            animation-duration:
                0.01ms !important;

            animation-iteration-count:
                1 !important;

            transition-duration:
                0.01ms !important;
        }
    }
    </style>

</head>


<body>


    <!-- =====================================================
     NAVBAR
===================================================== -->

    <nav class="navbar navbar-dark bg-primary">

        <div class="container">


            <!-- Logo -->

            <a class="navbar-brand" href="dashboard.php">

                🏸 MTU Badminton Club

            </a>


            <!-- User -->

            <div class="d-flex align-items-center">

                <span class="text-white me-3">

                    Welcome,

                    <strong>

                        <?= htmlspecialchars(
                        $member['username'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                    </strong>

                </span>


                <a href="../public/logout.php" class="btn btn-light btn-sm">

                    Logout

                </a>

                <a href="../public/index.php" class="btn btn-light btn-sm">

                    Home

                </a>

            </div>

        </div>

    </nav>



    <!-- =====================================================
     MAIN CONTENT
===================================================== -->

    <div class="container py-5">


        <!-- =================================================
         PAGE TITLE
    ================================================== -->

        <div class="mb-4">

            <h2>

                Member Dashboard

            </h2>

            <p class="text-muted">

                Welcome to the MTU Badminton Club member portal.

            </p>

        </div>



        <div class="row g-4">


            <!-- =================================================
             LEFT SIDE - PROFILE CARD
        ================================================== -->

            <div class="col-lg-4">

                <div class="card profile-card shadow-sm">

                    <div class="card-body text-center">


                        <!-- =====================================
                         PROFILE PICTURE
                    ====================================== -->

                        <?php if ($profileImageExists): ?>

                        <img src="<?= htmlspecialchars(
                                $profileImage,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>?v=<?= $imageVersion ?>" alt="Profile Picture" class="profile-image" onerror="
                                this.style.display='none';
                                document.getElementById('noPhoto').style.display='flex';
                            ">


                        <div id="noPhoto" class="no-photo" style="display: none;">

                            No Photo

                        </div>


                        <?php else: ?>

                        <div class="no-photo">

                            No Photo

                        </div>

                        <?php endif; ?>



                        <!-- =====================================
                         USERNAME
                    ====================================== -->

                        <h4 class="mt-3 mb-1">

                            <?= htmlspecialchars(
                            $member['username'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        </h4>



                        <!-- =====================================
                         MEMBER STATUS
                    ====================================== -->

                        <?php if (
                        isset($member['status']) &&
                        $member['status'] === 'Approved'
                    ): ?>

                        <span class="badge bg-success">

                            Approved Member

                        </span>


                        <?php elseif (
                        isset($member['status']) &&
                        $member['status'] === 'Pending'
                    ): ?>

                        <span class="badge bg-warning text-dark">

                            Pending

                        </span>


                        <?php else: ?>

                        <span class="badge bg-danger">

                            Rejected

                        </span>

                        <?php endif; ?>



                        <hr>



                        <!-- =====================================
                         VIEW PROFILE PICTURE
                    ====================================== -->

                        <?php if ($profileImageExists): ?>

                        <a href="<?= htmlspecialchars(
                                $profileImage,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" target="_blank" class="btn btn-primary w-100 mb-2">

                            View Profile Picture

                        </a>


                        <?php else: ?>

                        <button type="button" class="btn btn-secondary w-100 mb-2" disabled>

                            No Profile Picture

                        </button>

                        <?php endif; ?>



                        <!-- =====================================
                         EDIT PROFILE
                    ====================================== -->

                        <a href="edit_profile.php" class="btn btn-outline-primary w-100">

                            Edit Profile

                        </a>


                    </div>

                </div>

            </div>



            <!-- =================================================
             RIGHT SIDE - MEMBER INFORMATION
        ================================================== -->

            <div class="col-lg-8">

                <div class="card profile-card shadow-sm">

                    <div class="card-body">


                        <h4 class="mb-4">

                            My Information

                        </h4>


                        <div class="row g-4">



                            <!-- =================================
                             STUDENT ID
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    STUDENT ID

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['student_id'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             ROLL NUMBER
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    ROLL NUMBER

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['roll_number'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             DEPARTMENT
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    DEPARTMENT

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['department'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             ACADEMIC YEAR
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    ACADEMIC YEAR

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['academic_year'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             GENDER
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    GENDER

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['gender'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             PHONE
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    PHONE

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['phone'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             EMAIL
                        ================================== -->

                            <div class="col-md-12">

                                <div class="info-label">

                                    EMAIL

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['email'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             REGISTERED DATE
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    REGISTERED DATE

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['created_at'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>



                            <!-- =================================
                             MEMBERSHIP STATUS
                        ================================== -->

                            <div class="col-md-6">

                                <div class="info-label">

                                    MEMBERSHIP STATUS

                                </div>

                                <div class="info-value">

                                    <?= htmlspecialchars(
                                    $member['status'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>



    <!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>