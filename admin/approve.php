<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed.");
    }

    $student_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($student_id) {
        // Prepared Statement (Fixes SQL Injection)
        $db = $GLOBALS['pdo'] ?? null;
        if ($db instanceof PDO) {
            $stmt = $db->prepare("UPDATE members SET status = 'approved' WHERE id = ?");
            $stmt->execute([$student_id]);
        }
    }
}

header('Location: admin/dashboard.php');
exit;
?>