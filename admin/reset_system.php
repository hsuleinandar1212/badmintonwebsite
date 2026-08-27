<?php

declare(strict_types=1);

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * PRODUCTION SYSTEM RESET
 * =========================================================
 *
 * KEEP:
 *   admins
 *
 * CLEAR:
 *   members
 *   news
 *
 * SECURITY:
 *   - Existing admin authentication
 *   - Session validation
 *   - CSRF protection
 *   - Admin password re-authentication
 *   - POST-only destructive action
 *   - Exact confirmation phrase
 *   - Database transaction
 *   - Audit logging
 *   - Security headers
 *
 * IMPORTANT:
 *   This action permanently deletes members and news.
 */

// =========================================================
// LOAD AUTHENTICATION
// =========================================================

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';


// =========================================================
// REQUIRE ADMIN
// =========================================================

require_admin();


// =========================================================
// DATABASE CHECK
// =========================================================

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    exit('Database connection unavailable.');
}


// =========================================================
// GET CURRENT ADMIN ID
// =========================================================

$adminId = filter_var(
    $_SESSION['admin_id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($adminId === false || $adminId <= 0) {
    http_response_code(403);
    exit('Invalid administrator session.');
}


// =========================================================
// SECURITY HEADERS
// =========================================================

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

header(
    'Content-Security-Policy: '
    . "default-src 'self'; "
    . "style-src 'self' 'unsafe-inline'; "
    . "script-src 'self' 'unsafe-inline'; "
    . "form-action 'self'; "
    . "frame-ancestors 'none';"
);


// =========================================================
// CSRF TOKEN
// =========================================================

$csrfToken = csrf_token();


// =========================================================
// MESSAGES
// =========================================================

$successMessage = '';
$errorMessage   = '';


// =========================================================
// AUDIT LOG
// =========================================================

function write_reset_audit_log(
    int $adminId,
    string $result
): void {

    /*
     * Recommended:
     *
     * Move this directory outside the public web root
     * before deploying the website.
     */

    $logDirectory =
        dirname(__DIR__) .
        '/storage/logs';

    if (!is_dir($logDirectory)) {

        @mkdir(
            $logDirectory,
            0700,
            true
        );
    }

    $logFile =
        $logDirectory .
        '/admin_audit.log';


    $ipAddress =
        $_SERVER['REMOTE_ADDR'] ?? 'unknown';


    $userAgent =
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';


    /*
     * Never log:
     * - passwords
     * - CSRF tokens
     * - session IDs
     * - member information
     */

    $entry = [

        'timestamp' =>
            date('Y-m-d H:i:s'),

        'event' =>
            'SYSTEM_RESET',

        'admin_id' =>
            $adminId,

        'result' =>
            $result,

        'ip' =>
            $ipAddress,

        'user_agent' =>
            mb_substr(
                $userAgent,
                0,
                500
            )
    ];


    @file_put_contents(
        $logFile,

        json_encode(
            $entry,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        ) . PHP_EOL,

        FILE_APPEND | LOCK_EX
    );
}


// =========================================================
// HANDLE RESET
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $transactionStarted = false;

    try {

        // =================================================
        // CSRF CHECK
        // =================================================

        $submittedToken =
            (string)(
                $_POST['csrf_token'] ?? ''
            );


        if ($submittedToken === '') {

            write_reset_audit_log(
                $adminId,
                'CSRF_MISSING'
            );

            throw new RuntimeException(
                'Security validation failed.'
            );
        }


        verify_csrf_token(
            $submittedToken
        );


        // =================================================
        // PASSWORD
        // =================================================

        $currentPassword =
            (string)(
                $_POST['current_password'] ?? ''
            );


        if ($currentPassword === '') {

            write_reset_audit_log(
                $adminId,
                'PASSWORD_MISSING'
            );

            throw new RuntimeException(
                'Current admin password is required.'
            );
        }


        // =================================================
        // CONFIRMATION
        // =================================================

        $confirmation =
            trim(
                (string)(
                    $_POST['confirmation'] ?? ''
                )
            );


        if ($confirmation !== 'RESET SYSTEM') {

            write_reset_audit_log(
                $adminId,
                'CONFIRMATION_FAILED'
            );

            throw new RuntimeException(
                'Please type RESET SYSTEM exactly.'
            );
        }


        // =================================================
        // GET ADMIN ACCOUNT
        // =================================================
        //
        // Your admins table must contain:
        //
        // id
        // username
        // password
        //
        // password must contain password_hash()
        // output.
        //
        // =================================================

        $stmt = $pdo->prepare(
            'SELECT id, username, password
             FROM admins
             WHERE id = :id
             LIMIT 1'
        );


        $stmt->execute([
            ':id' => $adminId
        ]);


        $admin = $stmt->fetch(
            PDO::FETCH_ASSOC
        );


        if (!$admin) {

            write_reset_audit_log(
                $adminId,
                'ADMIN_NOT_FOUND'
            );

            throw new RuntimeException(
                'Administrator account could not be verified.'
            );
        }


        // =================================================
        // VERIFY PASSWORD
        // =================================================

        if (
            !isset($admin['password']) ||
            !password_verify(
                $currentPassword,
                (string)$admin['password']
            )
        ) {

            write_reset_audit_log(
                $adminId,
                'PASSWORD_FAILED'
            );

            throw new RuntimeException(
                'The current admin password is incorrect.'
            );
        }


        // =================================================
        // OPTIONAL PASSWORD HASH UPGRADE
        // =================================================

        if (
            password_needs_rehash(
                (string)$admin['password'],
                PASSWORD_DEFAULT
            )
        ) {

            $newHash = password_hash(
                $currentPassword,
                PASSWORD_DEFAULT
            );


            if ($newHash !== false) {

                $update = $pdo->prepare(
                    'UPDATE admins
                     SET password = :password
                     WHERE id = :id'
                );


                $update->execute([
                    ':password' => $newHash,
                    ':id' => $adminId
                ]);
            }
        }


        // =================================================
        // REGENERATE SESSION
        // =================================================

        session_regenerate_id(true);


        // =================================================
        // START TRANSACTION
        // =================================================

        $pdo->beginTransaction();

        $transactionStarted = true;


        // =================================================
        // COUNT MEMBERS
        // =================================================

        $memberCount = (int)$pdo
            ->query(
                'SELECT COUNT(*) FROM members'
            )
            ->fetchColumn();


        // =================================================
        // COUNT NEWS
        // =================================================

        $newsCount = (int)$pdo
            ->query(
                'SELECT COUNT(*) FROM news'
            )
            ->fetchColumn();


        // =================================================
        // DELETE MEMBERS
        // =================================================

        $pdo->exec(
            'DELETE FROM members'
        );


        // =================================================
        // DELETE NEWS
        // =================================================

        $pdo->exec(
            'DELETE FROM news'
        );


        // =================================================
        // VERIFY MEMBERS
        // =================================================

        $remainingMembers = (int)$pdo
            ->query(
                'SELECT COUNT(*) FROM members'
            )
            ->fetchColumn();


        // =================================================
        // VERIFY NEWS
        // =================================================

        $remainingNews = (int)$pdo
            ->query(
                'SELECT COUNT(*) FROM news'
            )
            ->fetchColumn();


        if (
            $remainingMembers !== 0 ||
            $remainingNews !== 0
        ) {

            throw new RuntimeException(
                'Reset verification failed.'
            );
        }


        // =================================================
        // COMMIT
        // =================================================

        $pdo->commit();

        $transactionStarted = false;


        // =================================================
        // RESET AUTO_INCREMENT
        // =================================================
        //
        // These are deliberately outside the transaction.
        //
        // ALTER TABLE is DDL and can cause an implicit commit.
        //
        // =================================================

        $pdo->exec(
            'ALTER TABLE members AUTO_INCREMENT = 1'
        );


        $pdo->exec(
            'ALTER TABLE news AUTO_INCREMENT = 1'
        );


        // =================================================
        // AUDIT SUCCESS
        // =================================================

        write_reset_audit_log(
            $adminId,

            'SUCCESS'
            . ' members_deleted='
            . $memberCount
            . ' news_deleted='
            . $newsCount
        );


        // =================================================
        // SUCCESS MESSAGE
        // =================================================

        $successMessage =
            'System reset completed successfully. '
            . number_format($memberCount)
            . ' members and '
            . number_format($newsCount)
            . ' news records were deleted. '
            . 'Administrator accounts were preserved.';


        // =================================================
        // NEW CSRF TOKEN
        // =================================================

        if (
            function_exists(
                'regenerate_csrf_token'
            )
        ) {

            regenerate_csrf_token();
        }


        $csrfToken =
            csrf_token();


    } catch (Throwable $e) {

        // =================================================
        // ROLLBACK
        // =================================================

        if (
            $transactionStarted &&
            $pdo->inTransaction()
        ) {

            $pdo->rollBack();
        }


        // =================================================
        // AUDIT FAILURE
        // =================================================

        write_reset_audit_log(
            $adminId,
            'FAILED'
        );


        // =================================================
        // SERVER ERROR LOG
        // =================================================
        //
        // Detailed error goes to server logs.
        // It is NOT exposed to the browser.
        //
        // =================================================

        error_log(
            'MTU SYSTEM RESET ERROR: '
            . $e->getMessage()
        );


        // =================================================
        // USER-FRIENDLY ERROR
        // =================================================

        $errorMessage =
            'The system reset could not be completed. '
            . 'Please verify the information and try again.';

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Danger Zone | MTU Badminton Club
    </title>


    <style>
    * {
        box-sizing: border-box;
    }


    body {

        margin: 0;

        min-height: 100vh;

        font-family:
            Inter,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            sans-serif;

        background:
            linear-gradient(135deg,
                #0f172a 0%,
                #1e1b4b 55%,
                #312e81 100%);

        color: #0f172a;

        padding: 30px;
    }


    .page {

        width: 100%;

        max-width: 760px;

        margin: 0 auto;
    }


    .card {

        background: #ffffff;

        border-radius: 24px;

        overflow: hidden;

        box-shadow:
            0 30px 80px rgba(0, 0, 0, .35);
    }


    .header {

        background:
            linear-gradient(135deg,
                #991b1b,
                #dc2626);

        color: #ffffff;

        padding: 32px;
    }


    .header-top {

        display: flex;

        align-items: center;

        gap: 18px;
    }


    .warning-icon {

        width: 62px;

        height: 62px;

        flex-shrink: 0;

        border-radius: 18px;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            rgba(255, 255, 255, .16);

        font-size: 30px;
    }


    .header h1 {

        margin: 0 0 6px;

        font-size: 30px;
    }


    .header p {

        margin: 0;

        opacity: .9;

        line-height: 1.5;
    }


    .content {

        padding: 32px;
    }


    .alert {

        padding: 18px;

        border-radius: 14px;

        margin-bottom: 24px;

        line-height: 1.55;
    }


    .alert-success {

        background: #dcfce7;

        border: 1px solid #86efac;

        color: #166534;
    }


    .alert-error {

        background: #fee2e2;

        border: 1px solid #fca5a5;

        color: #991b1b;
    }


    .danger-box {

        border: 1px solid #fecaca;

        background: #fff7f7;

        border-radius: 16px;

        padding: 20px;

        margin-bottom: 26px;
    }


    .danger-box h2 {

        margin: 0 0 12px;

        color: #991b1b;

        font-size: 18px;
    }


    .danger-box p {

        margin: 0;

        color: #7f1d1d;

        line-height: 1.6;
    }


    .data-grid {

        display: grid;

        grid-template-columns:
            repeat(3, 1fr);

        gap: 12px;

        margin: 22px 0 28px;
    }


    .data-card {

        padding: 18px;

        border-radius: 14px;

        background: #f8fafc;

        border: 1px solid #e5e7eb;

        text-align: center;
    }


    .data-card .name {

        display: block;

        font-weight: 800;

        margin-bottom: 8px;
    }


    .keep {

        color: #15803d;
    }


    .delete {

        color: #dc2626;
    }


    .form-group {

        margin-bottom: 20px;
    }


    .form-group label {

        display: block;

        margin-bottom: 8px;

        font-weight: 700;

        color: #1f2937;
    }


    .form-group input {

        width: 100%;

        padding: 14px 16px;

        border-radius: 12px;

        border: 1px solid #cbd5e1;

        outline: none;

        font-size: 16px;

        transition:
            border-color .2s,
            box-shadow .2s;
    }


    .form-group input:focus {

        border-color: #6366f1;

        box-shadow:
            0 0 0 4px rgba(99, 102, 241, .12);
    }


    .help {

        margin-top: 7px;

        font-size: 13px;

        color: #64748b;
    }


    .confirmation {

        background: #f8fafc;

        border-radius: 14px;

        padding: 18px;

        margin-bottom: 24px;

        line-height: 1.7;
    }


    .confirmation strong {

        color: #dc2626;
    }


    .actions {

        display: flex;

        gap: 12px;
    }


    .button {

        flex: 1;

        border: 0;

        border-radius: 12px;

        padding: 14px 20px;

        font-size: 15px;

        font-weight: 800;

        cursor: pointer;

        text-decoration: none;

        text-align: center;

        transition:
            transform .2s,
            box-shadow .2s;
    }


    .button:hover {

        transform: translateY(-2px);
    }


    .cancel {

        background: #e2e8f0;

        color: #334155;
    }


    .reset {

        background: #dc2626;

        color: #ffffff;

        box-shadow:
            0 8px 20px rgba(220, 38, 38, .25);
    }


    .reset:hover {

        background: #b91c1c;
    }


    .footer-note {

        margin-top: 22px;

        text-align: center;

        font-size: 13px;

        color: #64748b;

        line-height: 1.5;
    }


    @media (max-width: 650px) {

        body {
            padding: 15px;
        }

        .header,
        .content {
            padding: 24px;
        }

        .data-grid {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column;
        }

        .header h1 {
            font-size: 25px;
        }

    }
    </style>

</head>


<body>

    <div class="page">

        <div class="card">


            <!-- HEADER -->

            <div class="header">

                <div class="header-top">

                    <div class="warning-icon">
                        ⚠
                    </div>

                    <div>

                        <h1>
                            Danger Zone
                        </h1>

                        <p>
                            Permanently reset MTU Badminton Club data
                        </p>

                    </div>

                </div>

            </div>


            <!-- CONTENT -->

            <div class="content">


                <?php if ($successMessage !== ''): ?>

                <div class="alert alert-success">

                    <?= htmlspecialchars(
                    $successMessage,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                </div>

                <?php endif; ?>


                <?php if ($errorMessage !== ''): ?>

                <div class="alert alert-error">

                    <?= htmlspecialchars(
                    $errorMessage,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                </div>

                <?php endif; ?>


                <!-- WARNING -->

                <div class="danger-box">

                    <h2>
                        ⚠ Permanent Data Destruction
                    </h2>

                    <p>

                        This operation permanently removes all
                        member and news records. The database tables
                        themselves remain intact.

                        <br><br>

                        <strong>
                            Administrator accounts are never deleted.
                        </strong>

                    </p>

                </div>


                <!-- DATA STATUS -->

                <div class="data-grid">


                    <div class="data-card">

                        <span class="name">
                            admins
                        </span>

                        <span class="keep">
                            ✓ PRESERVED
                        </span>

                    </div>


                    <div class="data-card">

                        <span class="name">
                            members
                        </span>

                        <span class="delete">
                            ✕ CLEARED
                        </span>

                    </div>


                    <div class="data-card">

                        <span class="name">
                            news
                        </span>

                        <span class="delete">
                            ✕ CLEARED
                        </span>

                    </div>


                </div>


                <!-- RESET FORM -->

                <form method="POST" action="" id="resetForm" autocomplete="off">


                    <!-- CSRF -->

                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                    $csrfToken,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="current_password">
                            Current Admin Password
                        </label>


                        <input type="password" id="current_password" name="current_password"
                            autocomplete="current-password" required>


                        <div class="help">

                            Re-enter your current administrator
                            password to authorize this action.

                        </div>

                    </div>


                    <!-- CONFIRMATION -->

                    <div class="form-group">

                        <label for="confirmation">
                            Confirmation
                        </label>


                        <input type="text" id="confirmation" name="confirmation" placeholder="RESET SYSTEM"
                            autocomplete="off" required>


                        <div class="help">

                            Type
                            <strong>RESET SYSTEM</strong>
                            exactly.

                        </div>

                    </div>


                    <!-- SUMMARY -->

                    <div class="confirmation">

                        <strong>
                            The following data will be permanently deleted:
                        </strong>

                        <br><br>

                        • All member accounts<br>

                        • All member profile information<br>

                        • All member profile picture references<br>

                        • All news records<br>

                        • Member ID counter reset<br>

                        • News ID counter reset

                        <br><br>

                        <strong>
                            Administrator accounts will NOT be deleted.
                        </strong>

                    </div>


                    <!-- BUTTONS -->

                    <div class="actions">


                        <a href="dashboard.php" class="button cancel">
                            Cancel
                        </a>


                        <button type="submit" class="button reset">
                            Permanently Reset System
                        </button>


                    </div>


                </form>


                <div class="footer-note">

                    Protected by administrator authentication,
                    CSRF validation, password re-authentication
                    and audit logging.

                </div>


            </div>

        </div>

    </div>


    <script>
    document
        .getElementById('resetForm')
        .addEventListener(
            'submit',
            function(event) {

                const confirmation =
                    document.getElementById(
                        'confirmation'
                    ).value;


                if (
                    confirmation !==
                    'RESET SYSTEM'
                ) {

                    event.preventDefault();

                    alert(
                        'Please type RESET SYSTEM exactly.'
                    );

                    return;
                }


                const confirmed =
                    window.confirm(
                        'FINAL WARNING\\n\\n' +

                        'ALL members will be deleted.\\n' +

                        'ALL news will be deleted.\\n\\n' +

                        'Administrator accounts will remain.\\n\\n' +

                        'This action cannot be undone.\\n\\n' +

                        'Are you absolutely sure?'
                    );


                if (!confirmed) {

                    event.preventDefault();

                }

            }
        );
    </script>


</body>

</html>