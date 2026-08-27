<?php


require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/send_email.php";
require_once __DIR__ . "/../includes/csrf.php";



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: dashboard.php");
    exit();

}



if (
    !isset($_POST['csrf_token']) ||
    !verify_csrf_token($_POST['csrf_token'])
) {

    http_response_code(403);

    exit("Invalid security token.");

}



$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id || $id <= 0) {

    header("Location: dashboard.php");
    exit();

}




$stmt = $pdo->prepare(
    "SELECT username, email, status
     FROM members
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$id]);

$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {

    header("Location: dashboard.php");
    exit();

}


if ($member['status'] === 'Rejected') {

    header("Location: dashboard.php");
    exit();

}



$stmt = $pdo->prepare(
    "UPDATE members
     SET status = 'Rejected'
     WHERE id = ?
     AND status <> 'Rejected'"
);

$stmt->execute([$id]);

$updated = $stmt->rowCount() > 0;

if ($updated) {

    $name = $member['username'];

    $email = $member['email'];

    sendMemberEmail(
        $email,
        $name,
        "Rejected"
    );

}


header("Location: dashboard.php");

exit();

?>