<?php

declare(strict_types=1);

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * EMAIL SENDING SYSTEM
 * PHPMailer + SMTP
 * =========================================================
 *
 * Expected structure:
 *
 * mtu-badminton-system/
 * │
 * ├── PHPMailer/
 * │   └── src/
 * │       ├── Exception.php
 * │       ├── PHPMailer.php
 * │       └── SMTP.php
 * │
 * ├── send_email.php
 * ├── .env
 * └── ...
 *
 */


/**
 * =========================================================
 * PHPMailer NAMESPACE
 * =========================================================
 */

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


/**
 * =========================================================
 * LOAD PHPMailer
 * =========================================================
 *
 * send_email.php is located directly inside the
 * project root, so __DIR__ points to:
 *
 * mtu-badminton-system/
 *
 */

$phpMailerPath = __DIR__ . '/PHPMailer/src';


/**
 * Check required PHPMailer files.
 */

$requiredPhpMailerFiles = [
    $phpMailerPath . '/Exception.php',
    $phpMailerPath . '/PHPMailer.php',
    $phpMailerPath . '/SMTP.php',
];


foreach ($requiredPhpMailerFiles as $file) {

    if (!is_file($file)) {

        error_log(
            'MTU Badminton Club: PHPMailer file not found: ' . $file
        );

        throw new RuntimeException(
            'PHPMailer files were not found. Please check the PHPMailer/src folder.'
        );
    }
}


/**
 * Load PHPMailer classes.
 */

require_once $phpMailerPath . '/Exception.php';
require_once $phpMailerPath . '/PHPMailer.php';
require_once $phpMailerPath . '/SMTP.php';


/**
 * =========================================================
 * LOAD .ENV
 * =========================================================
 *
 * Supports:
 *
 * 1. vlucas/phpdotenv if available
 * 2. Simple fallback .env loader
 *
 */


/**
 * .env location.
 */

$envPath = __DIR__ . '/.env';


/**
 * =========================================================
 * OPTION 1 — Composer / phpdotenv
 * =========================================================
 */

if (class_exists('Dotenv\\Dotenv')) {

    if (is_file($envPath)) {

        try {

            $dotenv = Dotenv\Dotenv::createImmutable(
                __DIR__
            );

            $dotenv->safeLoad();

        } catch (Throwable $e) {

            error_log(
                'MTU Badminton Club: Failed to load .env: '
                . $e->getMessage()
            );
        }
    }
}


/**
 * =========================================================
 * OPTION 2 — FALLBACK .ENV LOADER
 * =========================================================
 *
 * This is used when phpdotenv is not available.
 *
 */

if (is_file($envPath)) {

    $envLines = file(
        $envPath,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );


    if ($envLines !== false) {

        foreach ($envLines as $line) {

            $line = trim($line);


            /**
             * Ignore empty lines.
             */

            if ($line === '') {
                continue;
            }


            /**
             * Ignore comments.
             */

            if (str_starts_with($line, '#')) {
                continue;
            }


            /**
             * Ignore malformed lines.
             */

            if (!str_contains($line, '=')) {
                continue;
            }


            /**
             * Separate key and value.
             */

            [$key, $value] = explode(
                '=',
                $line,
                2
            );


            $key = trim($key);
            $value = trim($value);


            /**
             * Ignore empty keys.
             */

            if ($key === '') {
                continue;
            }


            /**
             * Remove surrounding quotes.
             */

            if (
                strlen($value) >= 2 &&
                (
                    (
                        $value[0] === '"' &&
                        $value[strlen($value) - 1] === '"'
                    )
                    ||
                    (
                        $value[0] === "'" &&
                        $value[strlen($value) - 1] === "'"
                    )
                )
            ) {

                $value = substr(
                    $value,
                    1,
                    -1
                );
            }


            /**
             * Only add values that are not already loaded.
             */

            if (
                !isset($_ENV[$key]) &&
                getenv($key) === false
            ) {

                $_ENV[$key] = $value;

                putenv(
                    $key . '=' . $value
                );
            }
        }
    }
}


/**
 * =========================================================
 * ENVIRONMENT HELPER
 * =========================================================
 */

function getMailEnv(
    string $key,
    string $default = ''
): string {

    /**
     * First check $_ENV.
     */

    if (
        isset($_ENV[$key]) &&
        is_string($_ENV[$key])
    ) {

        return trim($_ENV[$key]);
    }


    /**
     * Then check getenv().
     */

    $value = getenv($key);


    if ($value !== false) {

        return trim((string) $value);
    }


    return $default;
}


/**
 * =========================================================
 * SEND CUSTOM EMAIL
 * =========================================================
 *
 * @param string $toEmail
 * @param string $toName
 * @param string $subject
 * @param string $htmlBody
 *
 * @return bool
 */

function sendCustomEmail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody
): bool {

    /**
     * =====================================================
     * VALIDATE EMAIL
     * =====================================================
     */

    if (
        !filter_var(
            $toEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        error_log(
            'MTU Badminton Club: Invalid recipient email address.'
        );

        return false;
    }


    /**
     * =====================================================
     * GET SMTP SETTINGS
     * =====================================================
     */

    $smtpHost = getMailEnv(
        'MAIL_HOST'
    );


    $smtpPort = (int) getMailEnv(
        'MAIL_PORT',
        '587'
    );


    $smtpUsername = getMailEnv(
        'MAIL_USERNAME'
    );


    $smtpPassword = getMailEnv(
        'MAIL_PASSWORD'
    );


    $smtpEncryption = strtolower(
        getMailEnv(
            'MAIL_ENCRYPTION',
            'tls'
        )
    );


    $mailFromAddress = getMailEnv(
        'MAIL_FROM_ADDRESS',
        $smtpUsername
    );


    $mailFromName = getMailEnv(
        'MAIL_FROM_NAME',
        'MTU Badminton Club'
    );


    /**
     * =====================================================
     * VALIDATE SMTP CONFIGURATION
     * =====================================================
     */

    if ($smtpHost === '') {

        error_log(
            'MTU Badminton Club: MAIL_HOST is missing.'
        );

        return false;
    }


    if ($smtpUsername === '') {

        error_log(
            'MTU Badminton Club: MAIL_USERNAME is missing.'
        );

        return false;
    }


    if ($smtpPassword === '') {

        error_log(
            'MTU Badminton Club: MAIL_PASSWORD is missing.'
        );

        return false;
    }


    if ($mailFromAddress === '') {

        error_log(
            'MTU Badminton Club: MAIL_FROM_ADDRESS is missing.'
        );

        return false;
    }


    /**
     * Validate sender email.
     */

    if (
        !filter_var(
            $mailFromAddress,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        error_log(
            'MTU Badminton Club: MAIL_FROM_ADDRESS is invalid.'
        );

        return false;
    }


    /**
     * Validate SMTP port.
     */

    if (
        $smtpPort < 1 ||
        $smtpPort > 65535
    ) {

        error_log(
            'MTU Badminton Club: Invalid SMTP port.'
        );

        return false;
    }


    /**
     * =====================================================
     * CREATE PHPMailer
     * =====================================================
     */

    $mail = new PHPMailer(true);


    try {

        /**
         * =================================================
         * SMTP MODE
         * =================================================
         */

        $mail->isSMTP();


        /**
         * SMTP host.
         */

        $mail->Host = $smtpHost;


        /**
         * SMTP authentication.
         */

        $mail->SMTPAuth = true;

        $mail->Username = $smtpUsername;

        $mail->Password = $smtpPassword;


        /**
         * =================================================
         * ENCRYPTION
         * =================================================
         */

        if (
            $smtpEncryption === 'ssl' ||
            $smtpEncryption === 'smtps'
        ) {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;

        } elseif (
            $smtpEncryption === 'tls' ||
            $smtpEncryption === 'starttls'
        ) {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;

        } elseif (
            $smtpEncryption === '' ||
            $smtpEncryption === 'none'
        ) {

            $mail->SMTPSecure = '';

            $mail->SMTPAutoTLS = false;

        } else {

            error_log(
                'MTU Badminton Club: Unsupported MAIL_ENCRYPTION value.'
            );

            return false;
        }


        /**
         * SMTP port.
         */

        $mail->Port = $smtpPort;


        /**
         * =================================================
         * EMAIL SETTINGS
         * =================================================
         */

        $mail->CharSet = 'UTF-8';

        $mail->Encoding = 'base64';

        $mail->Priority = 3;


        /**
         * =================================================
         * SENDER
         * =================================================
         */

        $mail->setFrom(
            $mailFromAddress,
            $mailFromName
        );


        /**
         * =================================================
         * RECIPIENT
         * =================================================
         */

        $mail->addAddress(
            $toEmail,
            $toName
        );


        /**
         * =================================================
         * EMAIL CONTENT
         * =================================================
         */

        $mail->isHTML(true);


        $mail->Subject = $subject;


        $mail->Body = $htmlBody;


        /**
         * =================================================
         * PLAIN TEXT VERSION
         * =================================================
         */

        $plainText = str_replace(
            [
                '<br>',
                '<br/>',
                '<br />',
                '</p>',
                '</div>',
                '</li>'
            ],
            PHP_EOL,
            $htmlBody
        );


        $plainText = strip_tags(
            $plainText
        );


        $plainText = html_entity_decode(
            $plainText,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );


        $mail->AltBody = trim(
            $plainText
        );


        /**
         * =================================================
         * SEND EMAIL
         * =================================================
         */

        $mail->send();


        /**
         * Successfully sent.
         */

        return true;


    } catch (Exception $e) {

        /**
         * Log the actual PHPMailer error.
         *
         * Never show SMTP credentials to users.
         */

        error_log(
            'MTU Badminton Club email error: '
            . $mail->ErrorInfo
        );


        return false;


    } catch (Throwable $e) {

        /**
         * Catch unexpected PHP errors.
         */

        error_log(
            'MTU Badminton Club unexpected email error: '
            . $e->getMessage()
        );


        return false;
    }
}