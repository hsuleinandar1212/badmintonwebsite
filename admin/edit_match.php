<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';


/*
|--------------------------------------------------------------------------
| ESCAPE OUTPUT
|--------------------------------------------------------------------------
*/

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| GET MATCH ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {

    header('Location: matches.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| FETCH MATCH
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        competition_name,
        match_type,
        team_one,
        team_two,
        match_date,
        match_time,
        venue,
        status,
        team_one_score,
        team_two_score,
        description,
        created_at,
        updated_at
    FROM matches
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$match = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$match) {

    header('Location: matches.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| MATCH TYPE OPTIONS
|--------------------------------------------------------------------------
*/

$matchTypes = [

    'Inter-University',
    'University Competition',
    'Department Competition',
    'Year Competition',
    'Friendly Match',
    'Other'

];


/*
|--------------------------------------------------------------------------
| STATUS OPTIONS
|--------------------------------------------------------------------------
*/

$statusOptions = [

    'Upcoming',
    'Live',
    'Completed',
    'Cancelled'

];


/*
|--------------------------------------------------------------------------
| FORM PROCESSING
|--------------------------------------------------------------------------
*/

$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {

        $errors[] = 'Invalid security token. Please refresh the page and try again.';
    }


    /*
    |--------------------------------------------------------------------------
    | GET FORM VALUES
    |--------------------------------------------------------------------------
    */

    $competitionName = trim(
        $_POST['competition_name'] ?? ''
    );

    $matchType = trim(
        $_POST['match_type'] ?? ''
    );

    $teamOne = trim(
        $_POST['team_one'] ?? ''
    );

    $teamTwo = trim(
        $_POST['team_two'] ?? ''
    );

    $matchDate = trim(
        $_POST['match_date'] ?? ''
    );

    $matchTime = trim(
        $_POST['match_time'] ?? ''
    );

    $venue = trim(
        $_POST['venue'] ?? ''
    );

    $status = trim(
        $_POST['status'] ?? ''
    );

    $teamOneScore = trim(
        $_POST['team_one_score'] ?? ''
    );

    $teamTwoScore = trim(
        $_POST['team_two_score'] ?? ''
    );

    $description = trim(
        $_POST['description'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($competitionName === '') {

        $errors[] = 'Competition name is required.';
    }

    if (!in_array($matchType, $matchTypes, true)) {

        $errors[] = 'Please select a valid match type.';
    }

    if ($teamOne === '') {

        $errors[] = 'Team one is required.';
    }

    if ($teamTwo === '') {

        $errors[] = 'Team two is required.';
    }

    if ($teamOne !== '' && $teamTwo !== '') {

        if (
            mb_strtolower($teamOne) ===
            mb_strtolower($teamTwo)
        ) {

            $errors[] = 'Team one and team two cannot be the same.';
        }
    }

    if ($matchDate === '') {

        $errors[] = 'Match date is required.';
    } else {

        $dateObject = DateTime::createFromFormat(
            'Y-m-d',
            $matchDate
        );

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $matchDate
        ) {

            $errors[] = 'Please enter a valid match date.';
        }
    }

    if (!in_array($status, $statusOptions, true)) {

        $errors[] = 'Please select a valid match status.';
    }


    /*
    |--------------------------------------------------------------------------
    | SCORE VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($teamOneScore !== '') {

        if (
            !ctype_digit($teamOneScore) ||
            (int)$teamOneScore < 0
        ) {

            $errors[] = 'Team one score must be a valid number.';
        }
    }

    if ($teamTwoScore !== '') {

        if (
            !ctype_digit($teamTwoScore) ||
            (int)$teamTwoScore < 0
        ) {

            $errors[] = 'Team two score must be a valid number.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | COMPLETED MATCH SCORE
    |--------------------------------------------------------------------------
    */

    if ($status === 'Completed') {

        if (
            $teamOneScore === '' ||
            $teamTwoScore === ''
        ) {

            $errors[] =
                'Please enter both scores for a completed match.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $teamOneScoreValue =
            $teamOneScore === ''
            ? null
            : (int)$teamOneScore;

        $teamTwoScoreValue =
            $teamTwoScore === ''
            ? null
            : (int)$teamTwoScore;

        $matchTimeValue =
            $matchTime === ''
            ? null
            : $matchTime;

        $venueValue =
            $venue === ''
            ? null
            : $venue;

        $descriptionValue =
            $description === ''
            ? null
            : $description;


        try {

            $update = $pdo->prepare("
                UPDATE matches
                SET
                    competition_name = ?,
                    match_type = ?,
                    team_one = ?,
                    team_two = ?,
                    match_date = ?,
                    match_time = ?,
                    venue = ?,
                    status = ?,
                    team_one_score = ?,
                    team_two_score = ?,
                    description = ?
                WHERE id = ?
            ");


            $update->execute([

                $competitionName,
                $matchType,
                $teamOne,
                $teamTwo,
                $matchDate,
                $matchTimeValue,
                $venueValue,
                $status,
                $teamOneScoreValue,
                $teamTwoScoreValue,
                $descriptionValue,
                $id

            ]);


            header(
                'Location: matches.php?updated=1'
            );

            exit;

        } catch (PDOException $e) {

            $errors[] =
                'Unable to update the match. Please try again.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KEEP ENTERED VALUES AFTER ERROR
    |--------------------------------------------------------------------------
    */

    $match['competition_name'] = $competitionName;
    $match['match_type'] = $matchType;
    $match['team_one'] = $teamOne;
    $match['team_two'] = $teamTwo;
    $match['match_date'] = $matchDate;
    $match['match_time'] = $matchTime;
    $match['venue'] = $venue;
    $match['status'] = $status;
    $match['team_one_score'] =
        $teamOneScore === ''
        ? null
        : $teamOneScore;
    $match['team_two_score'] =
        $teamTwoScore === ''
        ? null
        : $teamTwoScore;
    $match['description'] = $description;
}


/*
|--------------------------------------------------------------------------
| ADMIN NAME
|--------------------------------------------------------------------------
*/

$adminName = $_SESSION['admin_username']
    ?? $_SESSION['username']
    ?? 'Administrator';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Edit Match | MTU Badminton Club
    </title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <style>
    :root {

        --primary: #8B5CF6;
        --primary-dark: #6D28D9;
        --indigo: #6366F1;

        --background: #F6F7FB;

        --text: #111827;
        --muted: #6B7280;

        --border: #E5E7EB;

        --danger: #DC2626;

    }


    * {
        box-sizing: border-box;
    }


    body {

        margin: 0;

        min-height: 100vh;

        background:
            radial-gradient(circle at top right,
                rgba(139, 92, 246, .08),
                transparent 30%),
            var(--background);

        color: var(--text);

        font-family:
            Inter,
            Poppins,
            "Segoe UI",
            Arial,
            sans-serif;

    }


    .main {

        margin-left: 250px;

        padding:
            35px;

    }


    .container-box {

        max-width: 1100px;

        margin: auto;

    }


    .page-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-bottom: 25px;

    }


    .page-header h1 {

        margin: 0;

        font-size: 28px;

        font-weight: 800;

    }


    .page-header p {

        margin:
            5px 0 0;

        color: var(--muted);

        font-size: 13px;

    }


    .back-btn {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding:
            10px 15px;

        border-radius: 10px;

        background: white;

        border:
            1px solid var(--border);

        color: #374151;

        text-decoration: none;

        font-size: 12px;

        font-weight: 700;

    }


    .back-btn:hover {

        color: var(--primary-dark);

        border-color: var(--primary);

    }


    .card-box {

        background: white;

        border:
            1px solid var(--border);

        border-radius: 18px;

        padding: 28px;

        box-shadow:
            0 10px 35px rgba(15, 23, 42, .06);

    }


    .section-title {

        display: flex;

        align-items: center;

        gap: 10px;

        margin-bottom: 20px;

        padding-bottom: 14px;

        border-bottom:
            1px solid #F1F2F5;

    }


    .section-icon {

        width: 38px;

        height: 38px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 11px;

        background: #F3E8FF;

        color: var(--primary-dark);

    }


    .section-title strong {

        font-size: 15px;

    }


    .section-title span {

        display: block;

        color: var(--muted);

        font-size: 10px;

        margin-top: 2px;

    }


    .form-label {

        font-size: 11px;

        font-weight: 800;

        color: #374151;

        margin-bottom: 7px;

    }


    .form-control,
    .form-select {

        min-height: 45px;

        border:
            1px solid var(--border);

        border-radius: 10px;

        font-size: 12px;

    }


    .form-control:focus,
    .form-select:focus {

        border-color: var(--primary);

        box-shadow:
            0 0 0 4px rgba(139, 92, 246, .10);

    }


    textarea.form-control {

        min-height: 120px;

        resize: vertical;

    }


    .btn-save {

        border: none;

        border-radius: 11px;

        padding:
            12px 22px;

        color: white;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));

        font-size: 12px;

        font-weight: 800;

    }


    .btn-save:hover {

        color: white;

        transform: translateY(-1px);

        box-shadow:
            0 8px 20px rgba(109, 40, 217, .20);

    }


    .btn-cancel {

        border:
            1px solid var(--border);

        border-radius: 11px;

        padding:
            12px 22px;

        background: white;

        color: #374151;

        font-size: 12px;

        font-weight: 800;

        text-decoration: none;

    }


    .alert {

        font-size: 12px;

        border-radius: 11px;

    }


    @media (max-width: 1050px) {

        .main {

            margin-left: 0;

            padding: 20px;

        }

    }


    @media (max-width: 600px) {

        .main {

            padding: 14px;

        }

        .card-box {

            padding: 20px;

        }

        .page-header {

            align-items: flex-start;

            flex-direction: column;

            gap: 12px;

        }

    }
    </style>

</head>


<body>


    <main class="main">

        <div class="container-box">


            <!-- HEADER -->

            <div class="page-header">

                <div>

                    <h1>
                        <i class="bi bi-pencil-square"></i>
                        Edit Match
                    </h1>

                    <p>
                        Update match information and result.
                    </p>

                </div>


                <a href="matches.php" class="back-btn">

                    <i class="bi bi-arrow-left"></i>

                    Back to Matches

                </a>

            </div>


            <!-- ERRORS -->

            <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <strong>
                    Please fix the following:
                </strong>

                <ul class="mb-0 mt-2">

                    <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                    <?php endforeach; ?>

                </ul>

            </div>

            <?php endif; ?>


            <!-- FORM -->

            <div class="card-box">


                <form method="POST" action="edit_match.php?id=<?= (int)$id ?>">


                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                    <!-- BASIC INFORMATION -->

                    <div class="section-title">

                        <div class="section-icon">

                            <i class="bi bi-trophy-fill"></i>

                        </div>

                        <div>

                            <strong>
                                Competition Information
                            </strong>

                            <span>
                                Define what type of competition this match belongs to.
                            </span>

                        </div>

                    </div>


                    <div class="row g-3 mb-4">


                        <!-- COMPETITION -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Competition Name *
                            </label>

                            <input type="text" name="competition_name" class="form-control"
                                value="<?= e($match['competition_name']) ?>" placeholder="e.g. MTU vs MIIT" required>

                        </div>


                        <!-- MATCH TYPE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Match Type *
                            </label>

                            <select name="match_type" class="form-select" required>

                                <option value="">
                                    Select Match Type
                                </option>

                                <?php foreach ($matchTypes as $type): ?>

                                <option value="<?= e($type) ?>" <?= $match['match_type'] === $type ? 'selected' : '' ?>>

                                    <?= e($type) ?>

                                </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- TEAM ONE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Team One *
                            </label>

                            <input type="text" name="team_one" class="form-control" value="<?= e($match['team_one']) ?>"
                                placeholder="e.g. MTU" required>

                        </div>


                        <!-- TEAM TWO -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Team Two *
                            </label>

                            <input type="text" name="team_two" class="form-control" value="<?= e($match['team_two']) ?>"
                                placeholder="e.g. MIIT" required>

                        </div>

                    </div>


                    <!-- DATE -->

                    <div class="section-title">

                        <div class="section-icon">

                            <i class="bi bi-calendar-event-fill"></i>

                        </div>

                        <div>

                            <strong>
                                Match Schedule
                            </strong>

                            <span>
                                Set when and where the match will take place.
                            </span>

                        </div>

                    </div>


                    <div class="row g-3 mb-4">


                        <div class="col-md-4">

                            <label class="form-label">
                                Match Date *
                            </label>

                            <input type="date" name="match_date" class="form-control"
                                value="<?= e($match['match_date']) ?>" required>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Match Time
                            </label>

                            <input type="time" name="match_time" class="form-control"
                                value="<?= e($match['match_time'] ?? '') ?>">

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Venue
                            </label>

                            <input type="text" name="venue" class="form-control" value="<?= e($match['venue'] ?? '') ?>"
                                placeholder="e.g. MTU Indoor Court">

                        </div>

                    </div>


                    <!-- RESULT -->

                    <div class="section-title">

                        <div class="section-icon">

                            <i class="bi bi-bar-chart-fill"></i>

                        </div>

                        <div>

                            <strong>
                                Match Result
                            </strong>

                            <span>
                                Update the current status and score.
                            </span>

                        </div>

                    </div>


                    <div class="row g-3 mb-4">


                        <!-- STATUS -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Status *
                            </label>

                            <select name="status" id="status" class="form-select" required>

                                <option value="">
                                    Select Status
                                </option>

                                <?php foreach ($statusOptions as $status): ?>

                                <option value="<?= e($status) ?>" <?= $match['status'] === $status ? 'selected' : '' ?>>

                                    <?= e($status) ?>

                                </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- SCORE ONE -->

                        <div class="col-md-4">

                            <label class="form-label">
                                <?= e($match['team_one']) ?> Score
                            </label>

                            <input type="number" name="team_one_score" class="form-control" min="0"
                                value="<?= e($match['team_one_score'] ?? '') ?>" placeholder="0">

                        </div>


                        <!-- SCORE TWO -->

                        <div class="col-md-4">

                            <label class="form-label">
                                <?= e($match['team_two']) ?> Score
                            </label>

                            <input type="number" name="team_two_score" class="form-control" min="0"
                                value="<?= e($match['team_two_score'] ?? '') ?>" placeholder="0">

                        </div>

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="section-title">

                        <div class="section-icon">

                            <i class="bi bi-card-text"></i>

                        </div>

                        <div>

                            <strong>
                                Description
                            </strong>

                            <span>
                                Add optional information about the match.
                            </span>

                        </div>

                    </div>


                    <div class="mb-4">

                        <textarea name="description" class="form-control"
                            placeholder="Match details, announcements, results, etc."><?= e($match['description'] ?? '') ?></textarea>

                    </div>


                    <!-- ACTIONS -->

                    <div class="d-flex gap-2 justify-content-end">

                        <a href="matches.php" class="btn-cancel">

                            Cancel

                        </a>


                        <button type="submit" class="btn-save">

                            <i class="bi bi-check-lg me-1"></i>

                            Save Changes

                        </button>

                    </div>


                </form>


            </div>


        </div>

    </main>


</body>

</html>