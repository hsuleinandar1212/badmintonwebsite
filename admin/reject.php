<?php

declare(strict_types=1);

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * REJECT MEMBER
 * =========================================================
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../send_email.php';


/**
 * =========================================================
 * REQUIRE ADMIN
 * =========================================================
 */

require_admin();


/**
 * =========================================================
 * ONLY POST REQUESTS
 * =========================================================
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: dashboard.php');

    exit();
}


/**
 * =========================================================
 * CSRF PROTECTION
 * =========================================================
 */

if (
    !verify_csrf_token(
        $_POST['csrf_token'] ?? ''
    )
) {

    die('CSRF validation failed.');
}


/**
 * =========================================================
 * GET MEMBER ID
 * =========================================================
 */

$memberId = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


if (!$memberId || $memberId <= 0) {

    header(
        'Location: dashboard.php?error=invalid_member'
    );

    exit();
}


try {

    /**
     * =====================================================
     * GET MEMBER
     * =====================================================
     */

    $stmt = $pdo->prepare(
        "SELECT
            id,
            username,
            email,
            status
         FROM members
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([
        $memberId
    ]);

    $member = $stmt->fetch(PDO::FETCH_ASSOC);


    /**
     * =====================================================
     * MEMBER NOT FOUND
     * =====================================================
     */

    if (!$member) {

        header(
            'Location: dashboard.php?error=member_not_found'
        );

        exit();
    }


    /**
     * =====================================================
     * UPDATE MEMBER STATUS
     * =====================================================
     */

    $update = $pdo->prepare(
        "UPDATE members
         SET status = 'Rejected'
         WHERE id = ?"
    );

    $update->execute([
        $memberId
    ]);


    /**
     * =====================================================
     * SEND REJECTION EMAIL
     * =====================================================
     */

    $emailSent = false;


    if (
        !empty($member['email']) &&
        filter_var(
            $member['email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $safeName = htmlspecialchars(
            (string) $member['username'],
            ENT_QUOTES,
            'UTF-8'
        );


        $subject =
            'Membership Application Update - MTU Badminton Club';


        $htmlBody = '

        <!DOCTYPE html>
        <html lang="en">

        <head>

            <meta charset="UTF-8">

            <meta name="viewport"
                  content="width=device-width, initial-scale=1.0">

            <title>MTU Badminton Club</title>

        </head>

        <body style="
            margin:0;
            padding:30px;
            background:#f8fafc;
            font-family:Arial,sans-serif;
            color:#1e293b;
        ">

            <div style="
                max-width:600px;
                margin:auto;
                background:#ffffff;
                border-radius:16px;
                padding:35px;
                box-shadow:0 10px 30px rgba(0,0,0,0.08);
            ">

                <h1 style="
                    color:#6d28d9;
                    margin-top:0;
                ">
                    MTU Badminton Club
                </h1>

                <h2>
                    Membership Application Update
                </h2>

                <p>
                    Hello <strong>' . $safeName . '</strong>,
                </p>

                <p>
                    Thank you for your interest in joining
                    the <strong>MTU Badminton Club</strong>.
                </p>

                <p>
                    After reviewing your membership
                    application, we are unable to approve
                    your registration at this time.
                </p>

                <div style="
                    margin:25px 0;
                    padding:18px;
                    background:#fef2f2;
                    border:1px solid #fecaca;
                    border-radius:12px;
                    color:#991b1b;
                ">

                    <strong>
                        Application Status: Rejected
                    </strong>

                </div>

                <p>
                    If you believe this was a mistake,
                    please contact the MTU Badminton Club
                    administration.
                </p>

                <p>
                    Best regards,<br>
                    <strong>MTU Badminton Club</strong>
                </p>

            </div>

        </body>

        </html>
        ';


        $emailSent = sendCustomEmail(
            (string) $member['email'],
            (string) $member['username'],
            $subject,
            $htmlBody
        );
    }


    /**
     * =====================================================
     * REDIRECT
     * =====================================================
     */

    if ($emailSent) {

        header(
            'Location: dashboard.php?success=rejected_email_sent'
        );

    } else {

        header(
            'Location: dashboard.php?success=rejected_email_failed'
        );
    }

    exit();


} catch (PDOException $e) {

    error_log(
        'MTU Badminton Club reject error: '
        . $e->getMessage()
    );

    header(
        'Location: dashboard.php?error=reject_failed'
    );

    exit();
}