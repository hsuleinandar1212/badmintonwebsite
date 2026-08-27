<?php

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * SECURE LOGIN SYSTEM
 * ADMIN + MEMBER AUTHENTICATION
 * =========================================================
 *
 * SESSION TIMEOUT:
 * - Admin: 3 minutes
 * - Member: 3 minutes
 *
 * The timeout is checked whenever a protected page
 * includes this authentication file.
 */


/**
 * =========================================================
 * SESSION CONFIGURATION
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
 * SESSION TIMEOUT
 * =========================================================
 *
 * 3 minutes = 180 seconds
 */

const SESSION_TIMEOUT = 180;


/**
 * =========================================================
 * CHECK SESSION TIMEOUT
 * =========================================================
 */

function check_session_timeout(): void
{
    /**
     * User is not logged in.
     */
    if (
        !isset($_SESSION['logged_in']) ||
        $_SESSION['logged_in'] !== true
    ) {
        return;
    }


    /**
     * No login timestamp exists.
     *
     * This can happen with an old session created
     * before the timeout system was added.
     */
    if (!isset($_SESSION['login_time'])) {

        $_SESSION['login_time'] = time();

        return;
    }


    /**
     * Calculate elapsed time.
     */
    $elapsedTime =
        time() - (int) $_SESSION['login_time'];


    /**
     * Session expired.
     */
    if ($elapsedTime >= SESSION_TIMEOUT) {

        /**
         * Completely destroy the session.
         */
        $_SESSION = [];


        /**
         * Remove session cookie.
         */
        if (
            ini_get('session.use_cookies')
        ) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }


        /**
         * Destroy server-side session.
         */
        session_destroy();


        /**
         * Start a fresh session so we can
         * show the timeout message.
         */
        session_start();


        $_SESSION['session_expired'] = true;


        /**
         * Send user back to login page.
         */
        header(
            "Location: ../public/login.php?timeout=1"
        );

        exit();
    }
}


/**
 * =========================================================
 * CHECK TIMEOUT IMMEDIATELY
 * =========================================================
 *
 * This means every protected page that includes
 * auth.php automatically gets the 3-minute check.
 */

check_session_timeout();


/**
 * =========================================================
 * DATABASE
 * =========================================================
 */

require_once __DIR__ . "/../config/db.php";


/** @var PDO $pdo */


/**
 * =========================================================
 * CSRF PROTECTION
 * =========================================================
 */

require_once __DIR__ . "/../includes/csrf.php";


/**
 * =========================================================
 * REGENERATE CSRF TOKEN
 * =========================================================
 */

function regenerate_csrf_token(): void
{
    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}


/**
 * =========================================================
 * ADMIN LOGIN CHECK
 * =========================================================
 */

function is_admin_logged_in(): bool
{
    return
        isset($_SESSION['admin_id']) &&
        ($_SESSION['user_type'] ?? '') === 'admin' &&
        ($_SESSION['logged_in'] ?? false) === true;
}


/**
 * =========================================================
 * MEMBER LOGIN CHECK
 * =========================================================
 */

function is_member_logged_in(): bool
{
    return
        isset($_SESSION['member_id']) &&
        ($_SESSION['user_type'] ?? '') === 'member' &&
        ($_SESSION['logged_in'] ?? false) === true;
}


/**
 * =========================================================
 * GENERIC LOGIN CHECK
 * =========================================================
 */

function is_logged_in(): bool
{
    return
        ($_SESSION['logged_in'] ?? false) === true
        &&
        in_array(
            $_SESSION['user_type'] ?? '',
            ['admin', 'member'],
            true
        );
}


/**
 * =========================================================
 * REQUIRE ADMIN
 * =========================================================
 */

function require_admin(): void
{
    if (!is_admin_logged_in()) {

        header(
            "Location: ../public/login.php"
        );

        exit();
    }
}


/**
 * =========================================================
 * REQUIRE MEMBER
 * =========================================================
 */

function require_member(): void
{
    if (!is_member_logged_in()) {

        header(
            "Location: ../public/login.php"
        );

        exit();
    }
}


/**
 * =========================================================
 * REQUIRE LOGIN
 * =========================================================
 */

function require_login(): void
{
    if (!is_logged_in()) {

        header(
            "Location: ../public/login.php"
        );

        exit();
    }
}