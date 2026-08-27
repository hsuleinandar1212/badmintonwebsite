<?php

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * SECURE LOGIN SYSTEM
 * =========================================================
 */


/**
 * =========================================================
 * SECURE SESSION CONFIGURATION
 * =========================================================
 */

if (session_status() === PHP_SESSION_NONE) {

    $isHttps =
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}


/**
 * =========================================================
 * DATABASE
 * =========================================================
 */

require_once __DIR__ . "/../config/db.php";

/** @var PDO $pdo Connection initialized by config/db.php. */


/**
 * =========================================================
 * CSRF PROTECTION
 * =========================================================
 */

require_once __DIR__ . "/../includes/csrf.php";

function regenerate_csrf_token(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if (
    isset($_SESSION['admin_id']) &&
    ($_SESSION['user_type'] ?? '') === 'admin'
) {

    header(
        "Location: ../admin/dashboard.php"
    );

    exit();
}


if (
    isset($_SESSION['member_id']) &&
    ($_SESSION['user_type'] ?? '') === 'member'
) {

    header(
        "Location: ../member/dashboard.php"
    );

    exit();
}


/**
 * =========================================================
 * VARIABLES
 * =========================================================
 */

$error = "";

$username = trim(
    $_POST['username'] ?? ''
);


/**
 * =========================================================
 * LOGIN PROCESS
 * =========================================================
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /**
     * -----------------------------------------------------
     * CSRF CHECK
     * -----------------------------------------------------
     */

    $csrfToken =
        $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrfToken)) {

        $error =
            "Invalid security request. Please refresh the page and try again.";

    } else {

        /**
         * -------------------------------------------------
         * GET FORM DATA
         * -------------------------------------------------
         */

        $username = trim(
            $_POST['username'] ?? ''
        );

        $password =
            $_POST['password'] ?? '';


        /**
         * -------------------------------------------------
         * BASIC VALIDATION
         * -------------------------------------------------
         */

        if (
            $username === '' ||
            $password === ''
        ) {

            $error =
                "Please enter username and password.";

        } elseif (strlen($username) > 100) {

            $error =
                "Invalid username or password.";

        } elseif (strlen($password) > 255) {

            $error =
                "Invalid username or password.";

        } else {

            /**
             * =============================================
             * 1. CHECK ADMIN ACCOUNT
             * =============================================
             */

            $stmt = $pdo->prepare(
                "SELECT
                    id,
                    username,
                    password
                 FROM admins
                 WHERE username = ?
                 LIMIT 1"
            );


            if ($stmt) {

                $stmt->bindParam(
                    1,
                    $username
                );

                $stmt->execute();

                $admin =
                    $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt->closeCursor();


                /**
                 * -----------------------------------------
                 * ADMIN LOGIN SUCCESS
                 * -----------------------------------------
                 */

                if (
                    $admin &&
                    password_verify(
                        $password,
                        $admin['password']
                    )
                ) {

                    /**
                     * Regenerate session ID
                     * to prevent session fixation.
                     */
                    session_regenerate_id(true);


                    /**
                     * Remove member session data.
                     */
                    unset(
                        $_SESSION['member_id'],
                        $_SESSION['member_username'],
                        $_SESSION['member_profile_picture']
                    );


                    /**
                     * Create admin session.
                     */
                    $_SESSION['admin_id'] =
                        (int) $admin['id'];

                    $_SESSION['admin_username'] =
                        $admin['username'];

                    $_SESSION['user_type'] =
                        'admin';

                    $_SESSION['logged_in'] =
                        true;
                        
                    $_SESSION['login_time'] = time();


                    /**
                     * Regenerate CSRF token
                     * after authentication.
                     */
                    regenerate_csrf_token();


                    /**
                     * Redirect admin.
                     */
                    header(
                        "Location: ../admin/dashboard.php"
                    );

                    exit();
                }
            }


            /**
             * =============================================
             * 2. CHECK MEMBER ACCOUNT
             * =============================================
             */

            $stmt = $pdo->prepare(
                "SELECT
                    id,
                    username,
                    password,
                    status,
                    profile_picture
                 FROM members
                 WHERE username = ?
                 LIMIT 1"
            );


            if ($stmt) {

                $stmt->bindParam(
                    1,
                    $username
                );

                $stmt->execute();

                $member =
                    $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt->closeCursor();


                /**
                 * -----------------------------------------
                 * MEMBER FOUND
                 * -----------------------------------------
                 */

                if ($member) {

                    /**
                     * -------------------------------------
                     * CHECK PASSWORD
                     * -------------------------------------
                     */

                    if (
                        !password_verify(
                            $password,
                            $member['password']
                        )
                    ) {

                        $error =
                            "Invalid username or password.";

                    }

                    /**
                     * -------------------------------------
                     * CHECK ACCOUNT STATUS
                     * -------------------------------------
                     */

                    elseif (
                        $member['status'] !== 'Approved'
                    ) {

                        if (
                            $member['status'] === 'Pending'
                        ) {

                            $error =
                                "Your membership is still pending approval.";

                        } elseif (
                            $member['status'] === 'Rejected'
                        ) {

                            $error =
                                "Your membership application was rejected.";

                        } else {

                            $error =
                                "Your account is not available for login.";
                        }

                    }

                    /**
                     * -------------------------------------
                     * MEMBER LOGIN SUCCESS
                     * -------------------------------------
                     */

                    else {

                        /**
                         * Regenerate session ID.
                         */
                        session_regenerate_id(true);


                        /**
                         * Remove admin session data.
                         */
                        unset(
                            $_SESSION['admin_id'],
                            $_SESSION['admin_username']
                        );


                        /**
                         * Create member session.
                         */
                        $_SESSION['member_id'] =
    (int) $member['id'];

$_SESSION['member_username'] =
    $member['username'];

$_SESSION['member_profile_picture'] =
    $member['profile_picture'];

$_SESSION['user_type'] =
    'member';

$_SESSION['logged_in'] =
    true;

$_SESSION['login_time'] =
    time();


                        /**
                         * Regenerate CSRF token.
                         */
                        regenerate_csrf_token();


                        /**
                         * Redirect member.
                         */
                        header(
                            "Location: ../member/dashboard.php"
                        );

                        exit();
                    }

                } else {

                    /**
                     * Generic error.
                     *
                     * This does not reveal whether
                     * the username exists.
                     */
                    $error =
                        "Invalid username or password.";
                }
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

    <title>
        MTU Badminton Club | Login
    </title>


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    <style>
    /* =====================================================
           RESET
        ===================================================== */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }


    /* =====================================================
           VARIABLES
        ===================================================== */

    :root {

        --sky-blue: #87ceeb;
        --deep-sky-blue: #00bfff;

        --light-blue: #e0f7fa;

        --white: #ffffff;

        --text-dark: #17232b;

        --text-muted: #607d8b;

        --yellow: #ffb703;

        --orange: #fb8500;

        --orange-dark: #e56d00;

        --danger: #dc2626;

        --danger-bg: #fef2f2;

        --danger-border: #fecaca;

    }


    /* =====================================================
           BODY
        ===================================================== */

    body {

        min-height: 100vh;

        display: flex;

        flex-direction: column;

        font-family:
            "Segoe UI",
            Arial,
            sans-serif;

        color: var(--text-dark);

        background:

            radial-gradient(circle at 15% 15%,
                rgba(135, 206, 235, 0.45),
                transparent 38%),

            radial-gradient(circle at 85% 20%,
                rgba(255, 183, 3, 0.20),
                transparent 38%),

            radial-gradient(circle at 50% 100%,
                rgba(251, 133, 0, 0.12),
                transparent 45%),

            linear-gradient(135deg,
                #e0f2fe 0%,
                #ffffff 50%,
                #fff7ed 100%);

    }


    /* =====================================================
           NAVBAR
        ===================================================== */

    nav {

        width: 100%;

        min-height: 82px;

        display: flex;

        align-items: center;

        padding:
            12px 7%;

        background:
            rgba(255, 255, 255, 0.90);

        backdrop-filter:
            blur(18px);

        -webkit-backdrop-filter:
            blur(18px);

        border-bottom:
            2px solid rgba(255,
                183,
                3,
                0.70);

        box-shadow:
            0 4px 20px rgba(251,
                133,
                0,
                0.10);

        z-index: 10;

    }


    /* =====================================================
           LOGO
        ===================================================== */

    .logo-area {

        display: flex;

        align-items: center;

        gap: 13px;

    }


    .logo-area img {

        width: 58px;

        height: 58px;

        object-fit: contain;

        filter:
            drop-shadow(0 0 8px rgba(251,
                    133,
                    0,
                    0.35));

        animation:
            logoFloat 3s ease-in-out infinite;

        transition:
            0.3s ease;

    }


    .logo-area img:hover {

        transform:
            scale(1.10) rotate(-4deg);

    }


    @keyframes logoFloat {

        0%,
        100% {

            transform:
                translateY(0);

        }

        50% {

            transform:
                translateY(-6px);

        }

    }


    .logo-text {

        font-size: 18px;

        font-weight: 900;

        letter-spacing: 1px;

    }


    .logo-text .mtu {

        color:
            var(--orange);

    }


    .logo-text .badminton {

        color:
            var(--yellow);

    }


    /* =====================================================
           LOGIN WRAPPER
        ===================================================== */

    .login-wrapper {

        flex: 1;

        display: flex;

        justify-content: center;

        align-items: center;

        padding:
            50px 20px;

    }


    /* =====================================================
           LOGIN CARD
        ===================================================== */

    .card {

        width: 100%;

        max-width: 850px;

        display: flex;

        overflow: hidden;

        border-radius: 26px;

        background:
            rgba(255,
                255,
                255,
                0.92);

        border:
            2px solid rgba(255,
                183,
                3,
                0.75);

        box-shadow:

            0 25px 60px rgba(0,
                0,
                0,
                0.10),

            0 0 25px rgba(255,
                183,
                3,
                0.18);

        backdrop-filter:
            blur(15px);

        -webkit-backdrop-filter:
            blur(15px);

        transition:
            0.4s ease;

    }


    .card:hover {

        transform:
            translateY(-4px);

        border-color:
            var(--orange);

        box-shadow:

            0 30px 70px rgba(0,
                0,
                0,
                0.14),

            0 0 35px rgba(255,
                183,
                3,
                0.25);

    }


    /* =====================================================
           LEFT IMAGE
        ===================================================== */

    .left-img {

        flex: 1;

        min-height: 450px;

        padding: 45px;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            linear-gradient(145deg,
                #ffffff,
                #f0faff);

        border-right:
            1px solid rgba(255,
                183,
                3,
                0.30);

    }


    .left-img img {

        width: 100%;

        max-width: 280px;

        height: auto;

        object-fit: contain;

        filter:
            drop-shadow(0 15px 20px rgba(0,
                    0,
                    0,
                    0.12));

        animation:
            heroFloat 4s ease-in-out infinite;

    }


    @keyframes heroFloat {

        0%,
        100% {

            transform:
                translateY(0);

        }

        50% {

            transform:
                translateY(-8px);

        }

    }


    /* =====================================================
           LOGIN AREA
        ===================================================== */

    .login-card {

        flex: 1;

        padding: 45px;

        display: flex;

        flex-direction: column;

        justify-content: center;

        background:

            linear-gradient(145deg,
                rgba(255,
                    255,
                    255,
                    0.96),
                rgba(255,
                    247,
                    237,
                    0.96));

    }


    .login-card h2 {

        margin-bottom: 25px;

        text-align: center;

        font-size: 24px;

        line-height: 1.3;

        font-weight: 800;

    }


    /* =====================================================
           ERROR MESSAGE
        ===================================================== */

    .alert {

        display: flex;

        align-items: flex-start;

        gap: 9px;

        width: 100%;

        margin-bottom: 20px;

        padding: 12px 14px;

        border-radius: 12px;

        font-size: 13px;

        line-height: 1.5;

    }


    .alert-danger {

        color:
            #991b1b;

        background:
            var(--danger-bg);

        border:
            1px solid var(--danger-border);

    }


    /* =====================================================
           FORM GROUP
        ===================================================== */

    .form-group {

        margin-bottom: 20px;

    }


    .form-group label {

        display: block;

        margin-bottom: 8px;

        color:
            var(--text-muted);

        font-size: 14px;

        font-weight: 700;

    }


    .form-group label i {

        color:
            var(--orange);

        margin-right: 5px;

    }


    /* =====================================================
           INPUT
        ===================================================== */

    input[type="text"],
    input[type="password"] {

        width: 100%;

        padding:
            13px 16px;

        border:
            2px solid rgba(255,
                183,
                3,
                0.70);

        border-radius: 14px;

        outline: none;

        background:
            var(--white);

        color:
            var(--text-dark);

        font-size: 14px;

        transition:
            0.3s ease;

    }


    input[type="text"]:focus,
    input[type="password"]:focus {

        border-color:
            var(--orange);

        box-shadow:
            0 0 0 4px rgba(251,
                133,
                0,
                0.10);

    }


    input::placeholder {

        color:
            #9aa9b0;

    }


    /* =====================================================
           PASSWORD
        ===================================================== */

    .password-container {

        position: relative;

        width: 100%;

    }


    .password-container input {

        padding-right:
            48px;

    }


    .toggle-password {

        position: absolute;

        top: 50%;

        right: 16px;

        transform:
            translateY(-50%);

        color:
            var(--text-muted);

        font-size: 16px;

        cursor: pointer;

        transition:
            0.2s ease;

    }


    .toggle-password:hover {

        color:
            var(--orange);

    }


    /* =====================================================
           BUTTON
        ===================================================== */

    .actions {

        margin-top: 5px;

    }


    input[type="submit"] {

        width: 100%;

        padding: 13px;

        border: none;

        border-radius: 14px;

        background:

            linear-gradient(135deg,
                var(--yellow),
                var(--orange));

        color:
            #ffffff;

        font-size: 15px;

        font-weight: 800;

        letter-spacing: 1px;

        cursor: pointer;

        box-shadow:
            0 8px 20px rgba(251,
                133,
                0,
                0.25);

        transition:
            0.3s ease;

    }


    input[type="submit"]:hover {

        transform:
            translateY(-2px);

        background:

            linear-gradient(135deg,
                var(--orange),
                var(--orange-dark));

        box-shadow:
            0 12px 25px rgba(251,
                133,
                0,
                0.35);

    }


    input[type="submit"]:active {

        transform:
            translateY(0);

    }


    /* =====================================================
           REGISTER
        ===================================================== */

    .options {

        display: flex;

        justify-content: center;

        align-items: center;

        flex-wrap: wrap;

        gap: 5px;

        margin-top: 22px;

        color:
            var(--text-muted);

        font-size: 13px;

        font-weight: 600;

    }


    .options a {

        color:
            var(--orange);

        font-weight: 800;

        text-decoration: none;

    }


    .options a:hover {

        color:
            var(--yellow);

        text-decoration:
            underline;

    }


    /* =====================================================
           FOOTER
        ===================================================== */

    footer {

        padding:
            18px 20px;

        text-align:
            center;

        color:
            var(--text-muted);

        font-size:
            12px;

        font-weight:
            600;

    }


    /* =====================================================
           TABLET
        ===================================================== */

    @media (max-width: 800px) {

        .login-wrapper {

            padding:
                35px 15px;

        }


        .left-img {

            padding:
                30px;

        }


        .login-card {

            padding:
                35px 28px;

        }

    }


    /* =====================================================
           MOBILE
        ===================================================== */

    @media (max-width: 650px) {

        nav {

            min-height:
                70px;

            padding:
                10px 18px;

        }


        .logo-area {

            gap: 9px;

        }


        .logo-area img {

            width:
                45px;

            height:
                45px;

        }


        .logo-text {

            font-size:
                13px;

            letter-spacing:
                0.5px;

        }


        .login-wrapper {

            padding:
                25px 12px;

            align-items:
                flex-start;

        }


        .card {

            flex-direction:
                column;

            max-width:
                430px;

            border-radius:
                20px;

        }


        .left-img {

            min-height:
                auto;

            padding:
                25px;

            border-right:
                none;

            border-bottom:
                1px solid rgba(255,
                    183,
                    3,
                    0.30);

        }


        .left-img img {

            width:
                150px;

            max-width:
                60%;

        }


        .login-card {

            padding:
                28px 22px;

        }


        .login-card h2 {

            font-size:
                20px;

            margin-bottom:
                20px;

        }


        .form-group {

            margin-bottom:
                17px;

        }


        .form-group label {

            font-size:
                13px;

        }


        input[type="text"],
        input[type="password"] {

            padding:
                12px 14px;

            font-size:
                14px;

        }


        input[type="submit"] {

            padding:
                12px;

        }


        .options {

            font-size:
                12px;

        }

    }


    /* =====================================================
           SMALL MOBILE
        ===================================================== */

    @media (max-width: 380px) {

        .logo-text {

            font-size:
                11px;

        }


        .login-card {

            padding:
                24px 17px;

        }


        .login-card h2 {

            font-size:
                18px;

        }


        .left-img img {

            width:
                125px;

        }

    }
    </style>

</head>


<body>


    <!-- =====================================================
         NAVBAR
    ===================================================== -->

    <nav>

        <div class="logo-area">

            <img src="../assets/images/mtubd.jpg" alt="MTU Badminton Club">

            <div class="logo-text">

                <span class="mtu">
                    MTU
                </span>

                <span class="badminton">
                    BADMINTON
                </span>

                CLUB

            </div>

        </div>

    </nav>


    <!-- =====================================================
         LOGIN
    ===================================================== -->

    <main class="login-wrapper">

        <div class="card">


            <!-- LEFT SIDE -->

            <div class="left-img">

                <img src="../assets/images/mtubd.jpg" alt="MTU Badminton Club Logo">

            </div>


            <!-- RIGHT SIDE -->

            <div class="login-card">

                <h2>
                    Welcome to MTU Court Central
                </h2>


                <!-- ERROR -->

                <?php if ($error !== ''): ?>

                <div class="alert alert-danger" role="alert">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>

                        <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                    </span>

                </div>

                <?php endif; ?>


                <!-- =================================================
                     LOGIN FORM
                ================================================== -->

                <form method="POST" action="<?= htmlspecialchars(
                        $_SERVER['PHP_SELF'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" autocomplete="on">


                    <!-- CSRF -->

                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                            csrf_token(),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">


                    <!-- USERNAME -->

                    <div class="form-group">

                        <label for="username">

                            <i class="fa-regular fa-user"></i>

                            Username

                        </label>


                        <input type="text" id="username" name="username" required maxlength="100"
                            autocomplete="username" placeholder="Enter your username" value="<?= htmlspecialchars(
                                $username,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">

                            <i class="fa-solid fa-lock"></i>

                            Password

                        </label>


                        <div class="password-container">

                            <input type="password" id="password" name="password" required maxlength="255"
                                autocomplete="current-password" placeholder="Enter your password">


                            <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword" title="Show password"
                                aria-label="Show password" role="button" tabindex="0"></i>

                        </div>

                    </div>


                    <!-- LOGIN -->

                    <div class="actions">

                        <input type="submit" value="Login">

                    </div>

                </form>


                <!-- REGISTER -->

                <div class="options">

                    <span>
                        Don't have an account?
                    </span>

                    <a href="register.php">
                        Register here
                    </a>

                </div>

            </div>

        </div>

    </main>


    <!-- =====================================================
         FOOTER
    ===================================================== -->

    <footer>

        © 2026 MTU Badminton Club |
        Mandalay Technological University

    </footer>


    <!-- =====================================================
         JAVASCRIPT
    ===================================================== -->

    <script>
    /**
     * =====================================================
     * PASSWORD VISIBILITY
     * =====================================================
     */

    document.addEventListener("DOMContentLoaded", () => {
        const togglePassword = document.getElementById("togglePassword");
        const password = document.getElementById("password");

        if (!togglePassword || !password) return;

        const togglePasswordVisibility = () => {
            const isHidden = password.type === "password";
            const labelText = isHidden ? "Hide password" : "Show password";

            password.type = isHidden ? "text" : "password";
            togglePassword.classList.toggle("fa-eye", isHidden);
            togglePassword.classList.toggle("fa-eye-slash", !isHidden);
            togglePassword.setAttribute("title", labelText);
            togglePassword.setAttribute("aria-label", labelText);
        };

        togglePassword.addEventListener("click", togglePasswordVisibility);
        togglePassword.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                togglePasswordVisibility();
            }
        });
    });
    </script>


</body>

</html>