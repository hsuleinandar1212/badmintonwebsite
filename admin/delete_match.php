<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

require_admin();

require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../includes/csrf.php';


/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: matches.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST['csrf_token']) ||
    !verify_csrf_token($_POST['csrf_token'])
) {

    $_SESSION['error'] =
        'Invalid security token. Please try again.';

    header('Location: matches.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| GET MATCH ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id || $id <= 0) {

    $_SESSION['error'] =
        'Invalid match ID.';

    header('Location: matches.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK MATCH EXISTS
|--------------------------------------------------------------------------
*/

$check = $pdo->prepare("
    SELECT id
    FROM matches
    WHERE id = ?
    LIMIT 1
");

$check->execute([$id]);


if (!$check->fetch()) {

    $_SESSION['error'] =
        'Match not found.';

    header('Location: matches.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE MATCH
|--------------------------------------------------------------------------
*/

try {

    $delete = $pdo->prepare("
        DELETE FROM matches
        WHERE id = ?
    ");

    $delete->execute([$id]);


    $_SESSION['success'] =
        'Match deleted successfully.';


} catch (PDOException $e) {

    $_SESSION['error'] =
        'Unable to delete the match. Please try again.';
}


/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

header('Location: matches.php');

exit;