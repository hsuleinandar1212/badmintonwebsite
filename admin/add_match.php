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
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$competitionName = '';
$matchType = 'University Competition';
$teamOne = 'MTU';
$teamTwo = '';
$matchDate = '';
$matchTime = '';
$venue = '';
$status = 'Upcoming';
$teamOneScore = '';
$teamTwoScore = '';
$description = '';

$errors = [];

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |----------------------------------------------------------------------
    | CSRF
    |----------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token']) ||
        !verify_csrf_token($_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid security token. Please refresh the page and try again.';
    }

    /*
    |----------------------------------------------------------------------
    | GET FORM DATA
    |----------------------------------------------------------------------
    */

    $competitionName = trim($_POST['competition_name'] ?? '');
    $matchType = trim($_POST['match_type'] ?? '');
    $teamOne = trim($_POST['team_one'] ?? '');
    $teamTwo = trim($_POST['team_two'] ?? '');
    $matchDate = trim($_POST['match_date'] ?? '');
    $matchTime = trim($_POST['match_time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $teamOneScore = trim($_POST['team_one_score'] ?? '');
    $teamTwoScore = trim($_POST['team_two_score'] ?? '');
    $description = trim($_POST['description'] ?? '');

    /*
    |----------------------------------------------------------------------
    | ALLOWED VALUES
    |----------------------------------------------------------------------
    */

    $allowedMatchTypes = [
        'Inter-University',
        'University Competition',
        'Department Competition',
        'Year Competition',
        'Friendly Match',
        'Other'
    ];

    $allowedStatuses = [
        'Upcoming',
        'Live',
        'Completed',
        'Cancelled'
    ];

    /*
    |----------------------------------------------------------------------
    | VALIDATION
    |----------------------------------------------------------------------
    */

    if ($competitionName === '') {
        $errors[] = 'Competition name is required.';
    }

    if (!in_array($matchType, $allowedMatchTypes, true)) {
        $errors[] = 'Please select a valid match type.';
    }

    if ($teamOne === '') {
        $errors[] = 'Team One is required.';
    }

    if ($teamTwo === '') {
        $errors[] = 'Team Two is required.';
    }

    if ($matchDate === '') {
        $errors[] = 'Match date is required.';
    }

    if (!in_array($status, $allowedStatuses, true)) {
        $errors[] = 'Please select a valid status.';
    }

    /*
    |----------------------------------------------------------------------
    | SCORE VALIDATION
    |----------------------------------------------------------------------
    */

    $teamOneScoreValue = null;
    $teamTwoScoreValue = null;

    if ($teamOneScore !== '') {

        if (
            !ctype_digit($teamOneScore) ||
            (int)$teamOneScore < 0
        ) {
            $errors[] = 'Team One score must be a valid number.';
        } else {
            $teamOneScoreValue = (int)$teamOneScore;
        }
    }

    if ($teamTwoScore !== '') {

        if (
            !ctype_digit($teamTwoScore) ||
            (int)$teamTwoScore < 0
        ) {
            $errors[] = 'Team Two score must be a valid number.';
        } else {
            $teamTwoScoreValue = (int)$teamTwoScore;
        }
    }

    /*
    |----------------------------------------------------------------------
    | INSERT
    |----------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            $sql = "
                INSERT INTO matches (
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
                    description
                )
                VALUES (
                    :competition_name,
                    :match_type,
                    :team_one,
                    :team_two,
                    :match_date,
                    :match_time,
                    :venue,
                    :status,
                    :team_one_score,
                    :team_two_score,
                    :description
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(
                ':competition_name',
                $competitionName,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':match_type',
                $matchType,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':team_one',
                $teamOne,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':team_two',
                $teamTwo,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':match_date',
                $matchDate,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':match_time',
                $matchTime !== '' ? $matchTime : null,
                $matchTime !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
            );

            $stmt->bindValue(
                ':venue',
                $venue !== '' ? $venue : null,
                $venue !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
            );

            $stmt->bindValue(
                ':status',
                $status,
                PDO::PARAM_STR
            );

            $stmt->bindValue(
                ':team_one_score',
                $teamOneScoreValue,
                $teamOneScoreValue !== null
                    ? PDO::PARAM_INT
                    : PDO::PARAM_NULL
            );

            $stmt->bindValue(
                ':team_two_score',
                $teamTwoScoreValue,
                $teamTwoScoreValue !== null
                    ? PDO::PARAM_INT
                    : PDO::PARAM_NULL
            );

            $stmt->bindValue(
                ':description',
                $description !== '' ? $description : null,
                $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
            );

            $stmt->execute();

            /*
            |------------------------------------------------------------------
            | SUCCESS
            |------------------------------------------------------------------
            */

            header('Location: matches.php?success=match_added');
            exit;

        } catch (PDOException $e) {

            $errors[] =
                'Unable to add match. Please check your database table and try again.';

            /*
            | For development only:
            | Uncomment the next line if you need the exact database error.
            |
            | $errors[] = $e->getMessage();
            */
        }
    }
}

/*
|--------------------------------------------------------------------------
| ADMIN NAME
|--------------------------------------------------------------------------
*/

$adminName = $_SESSION['admin_username']
    ?? $_SESSION['username']
    ?? 'Administrator';

$adminInitial = strtoupper(
    substr((string)$adminName, 0, 1)
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Add Match | MTU Badminton Club
    </title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
    :root {

        --primary: #8B5CF6;
        --primary-dark: #6D28D9;
        --indigo: #6366F1;

        --purple-soft: #F3E8FF;

        --background: #F6F7FB;

        --white: #FFFFFF;

        --text: #111827;
        --muted: #6B7280;

        --border: #E5E7EB;

        --green: #16A34A;
        --red: #DC2626;

        --sidebar: #11101A;

        --shadow:
            0 10px 35px rgba(15, 23, 42, .06);

        --radius: 18px;

        --transition:
            all .25s ease;
    }

    * {
        box-sizing: border-box;
    }

    body {

        margin: 0;

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

        min-height: 100vh;
    }

    a {
        text-decoration: none;
    }

    .brand {

        display: flex;

        align-items: center;

        gap: 12px;

        padding:
            4px 10px 28px;
    }

    .admin-mini {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 12px;

        margin-bottom: 10px;

        border-radius: 14px;

        background:
            rgba(255, 255, 255, .045);

        border:
            1px solid rgba(255, 255, 255, .06);
    }

    .admin-avatar {

        width: 35px;
        height: 35px;

        border-radius: 11px;

        display: flex;

        align-items: center;
        justify-content: center;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--indigo));

        color: white;

        font-size: 13px;

        font-weight: 800;
    }

    .admin-mini strong {

        display: block;

        max-width: 130px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

        font-size: 12px;
    }

    .admin-mini span {

        display: block;

        color: #71717A;

        font-size: 10px;

        margin-top: 2px;
    }

    /* =========================================================
           MAIN
        ========================================================= */

    .main {

        margin-left: 250px;

        min-height: 100vh;

        padding:
            30px 34px 50px;
    }

    .content {

        max-width: 1100px;

        margin: auto;
    }

    /* =========================================================
           TOPBAR
        ========================================================= */

    .topbar {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        margin-bottom: 25px;
    }

    .page-title h1 {

        margin: 0;

        font-size: 28px;

        font-weight: 850;

        letter-spacing: -.7px;
    }

    .page-title p {

        margin:
            6px 0 0;

        color: var(--muted);

        font-size: 13px;
    }

    .top-actions {

        display: flex;

        gap: 9px;
    }

    .top-button {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding:
            10px 14px;

        border-radius: 11px;

        font-size: 12px;

        font-weight: 750;

        border:
            1px solid var(--border);

        background: white;

        color: var(--text);

        transition:
            var(--transition);
    }

    .top-button:hover {

        color: var(--primary-dark);

        transform:
            translateY(-2px);

        box-shadow:
            0 10px 25px rgba(109, 40, 217, .10);
    }

    .top-button.primary {

        color: white;

        border: none;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));
    }

    /* =========================================================
           FORM CARD
        ========================================================= */

    .form-card {

        background: white;

        border:
            1px solid var(--border);

        border-radius: 20px;

        box-shadow:
            var(--shadow);

        overflow: hidden;
    }

    .form-header {

        padding:
            24px 26px;

        background:
            linear-gradient(135deg,
                #5B21B6,
                #7C3AED,
                #6366F1);

        color: white;
    }

    .form-header-icon {

        width: 45px;
        height: 45px;

        border-radius: 13px;

        display: flex;

        align-items: center;
        justify-content: center;

        background:
            rgba(255, 255, 255, .14);

        font-size: 21px;

        margin-bottom: 12px;
    }

    .form-header h2 {

        margin: 0;

        font-size: 21px;

        font-weight: 800;
    }

    .form-header p {

        margin:
            5px 0 0;

        opacity: .8;

        font-size: 12px;
    }

    .form-body {

        padding:
            28px;
    }

    /* =========================================================
           FORM
        ========================================================= */

    .form-label {

        font-size: 11px;

        font-weight: 800;

        color: #374151;

        margin-bottom: 7px;
    }

    .required {

        color: var(--red);
    }

    .form-control,
    .form-select {

        min-height: 45px;

        border:
            1px solid var(--border);

        border-radius: 11px;

        background: #FAFAFB;

        font-size: 12px;

        padding:
            10px 13px;

        transition:
            var(--transition);
    }

    .form-select {

        cursor: pointer;
    }

    .form-control:focus,
    .form-select:focus {

        background: white;

        border-color:
            var(--primary);

        box-shadow:
            0 0 0 4px rgba(139, 92, 246, .10);
    }

    textarea.form-control {

        min-height: 120px;

        resize: vertical;
    }

    .section-title {

        display: flex;

        align-items: center;

        gap: 9px;

        margin:
            5px 0 18px;

        padding-bottom: 10px;

        border-bottom:
            1px solid #F1F2F5;
    }

    .section-title i {

        width: 32px;
        height: 32px;

        display: flex;

        align-items: center;
        justify-content: center;

        border-radius: 9px;

        color: var(--primary-dark);

        background:
            var(--purple-soft);
    }

    .section-title strong {

        font-size: 14px;
    }

    .section-title span {

        display: block;

        color: var(--muted);

        font-size: 10px;

        margin-top: 2px;
    }

    /* =========================================================
           BUTTONS
        ========================================================= */

    .form-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        margin-top: 25px;

        padding-top: 20px;

        border-top:
            1px solid #F1F2F5;
    }

    .btn-cancel,
    .btn-submit {

        min-height: 45px;

        padding:
            0 20px;

        border-radius: 11px;

        font-size: 12px;

        font-weight: 800;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 7px;

        transition:
            var(--transition);
    }

    .btn-cancel {

        color: #374151;

        background: #F3F4F6;

        border:
            1px solid #E5E7EB;
    }

    .btn-cancel:hover {

        background: #E5E7EB;

        color: #111827;
    }

    .btn-submit {

        color: white;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));

        border: none;

        box-shadow:
            0 8px 20px rgba(109, 40, 217, .16);
    }

    .btn-submit:hover {

        color: white;

        transform:
            translateY(-2px);

        box-shadow:
            0 12px 25px rgba(109, 40, 217, .25);
    }

    /* =========================================================
           ALERT
        ========================================================= */

    .alert {

        border-radius: 13px;

        font-size: 12px;

        border: none;
    }

    .main {

        margin-left: 0;

        padding:
            22px;
    }

    .mobile-toggle {

        display: inline-flex;

        align-items: center;
        justify-content: center;
    }

    @media (max-width: 700px) {

        .main {

            padding:
                16px 14px 35px;
        }

        .topbar {

            align-items: flex-start;

            flex-direction: column;
        }

        .top-actions {

            width: 100%;
        }

        .top-button {

            flex: 1;

            justify-content: center;
        }

        .form-body {

            padding:
                20px 16px;
        }

        .form-header {

            padding:
                21px;
        }

        .form-actions {

            flex-direction: column-reverse;
        }

        .btn-cancel,
        .btn-submit {

            width: 100%;
        }
    }
    </style>

</head>

<body>



    <!-- =========================================================
     MAIN
========================================================= -->

    <main class="main">

        <div class="content">

            <!-- TOPBAR -->

            <div class="topbar">

                <div class="page-title">

                    <div style="display:flex;align-items:center;gap:10px;">

                        <button class="mobile-toggle" id="mobileToggle" type="button">
                            <i class="bi bi-list"></i>
                        </button>

                        <div>

                            <h1>
                                Add Match
                            </h1>

                            <p>
                                Create a new badminton match or competition.
                            </p>

                        </div>

                    </div>

                </div>

                <div class="top-actions">

                    <a href="matches.php" class="top-button">
                        <i class="bi bi-trophy"></i>
                        Manage Matches
                    </a>

                </div>

            </div>


            <!-- ERRORS -->

            <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <div class="fw-bold mb-1">
                    Please fix the following:
                </div>

                <ul class="mb-0 ps-3">

                    <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                    <?php endforeach; ?>

                </ul>

            </div>

            <?php endif; ?>


            <!-- FORM CARD -->

            <section class="form-card">

                <div class="form-header">

                    <div class="form-header-icon">

                        <i class="bi bi-trophy-fill"></i>

                    </div>

                    <h2>
                        Create Match
                    </h2>

                    <p>
                        Add an upcoming competition, friendly match,
                        university event, department competition or
                        year-level competition.
                    </p>

                </div>


                <div class="form-body">

                    <form method="POST" action="">

                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                        <!-- =================================================
                         COMPETITION
                    ================================================== -->

                        <div class="section-title">

                            <i class="bi bi-award-fill"></i>

                            <div>

                                <strong>
                                    Competition Information
                                </strong>

                                <span>
                                    Select what type of event this match belongs to.
                                </span>

                            </div>

                        </div>


                        <div class="row g-3">

                            <!-- COMPETITION NAME -->

                            <div class="col-md-7">

                                <label class="form-label">

                                    Competition / Event Name
                                    <span class="required">*</span>

                                </label>

                                <input type="text" name="competition_name" class="form-control" maxlength="255"
                                    placeholder="Example: MTU vs MIIT" value="<?= e($competitionName) ?>" required>

                            </div>


                            <!-- MATCH TYPE -->

                            <div class="col-md-5">

                                <label class="form-label">

                                    Match Type
                                    <span class="required">*</span>

                                </label>

                                <select name="match_type" class="form-select" required>

                                    <option value="">
                                        -- Select Match Type --
                                    </option>

                                    <option value="Inter-University"
                                        <?= $matchType === 'Inter-University' ? 'selected' : '' ?>>
                                        Inter-University
                                    </option>

                                    <option value="University Competition"
                                        <?= $matchType === 'University Competition' ? 'selected' : '' ?>>
                                        University Competition
                                    </option>

                                    <option value="Department Competition"
                                        <?= $matchType === 'Department Competition' ? 'selected' : '' ?>>
                                        Department Competition
                                    </option>

                                    <option value="Year Competition"
                                        <?= $matchType === 'Year Competition' ? 'selected' : '' ?>>
                                        Year Competition
                                    </option>

                                    <option value="Friendly Match"
                                        <?= $matchType === 'Friendly Match' ? 'selected' : '' ?>>
                                        Friendly Match
                                    </option>

                                    <option value="Other" <?= $matchType === 'Other' ? 'selected' : '' ?>>
                                        Other
                                    </option>

                                </select>

                            </div>

                        </div>


                        <!-- =================================================
                         TEAMS
                    ================================================== -->

                        <div class="section-title" style="margin-top:30px;">

                            <i class="bi bi-people-fill"></i>

                            <div>

                                <strong>
                                    Match Participants
                                </strong>

                                <span>
                                    Enter the two sides participating in the match.
                                </span>

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label">

                                    Team One
                                    <span class="required">*</span>

                                </label>

                                <input type="text" name="team_one" class="form-control" maxlength="255"
                                    placeholder="Example: MTU" value="<?= e($teamOne) ?>" required>

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">

                                    Team Two
                                    <span class="required">*</span>

                                </label>

                                <input type="text" name="team_two" class="form-control" maxlength="255"
                                    placeholder="Example: MIIT" value="<?= e($teamTwo) ?>" required>

                            </div>

                        </div>


                        <!-- =================================================
                         DATE / TIME / VENUE / STATUS
                    ================================================== -->

                        <div class="section-title" style="margin-top:30px;">

                            <i class="bi bi-calendar-event-fill"></i>

                            <div>

                                <strong>
                                    Match Schedule
                                </strong>

                                <span>
                                    Set the date, time, venue and current status.
                                </span>

                            </div>

                        </div>


                        <div class="row g-3">

                            <!-- DATE -->

                            <div class="col-md-3">

                                <label class="form-label">

                                    Match Date
                                    <span class="required">*</span>

                                </label>

                                <input type="date" name="match_date" class="form-control" value="<?= e($matchDate) ?>"
                                    required>

                            </div>


                            <!-- TIME -->

                            <div class="col-md-3">

                                <label class="form-label">
                                    Match Time
                                </label>

                                <input type="time" name="match_time" class="form-control" value="<?= e($matchTime) ?>">

                            </div>


                            <!-- VENUE -->

                            <div class="col-md-3">

                                <label class="form-label">
                                    Venue
                                </label>

                                <input type="text" name="venue" class="form-control" maxlength="255"
                                    placeholder="Example: MTU Indoor Court" value="<?= e($venue) ?>">

                            </div>


                            <!-- STATUS -->

                            <div class="col-md-3">

                                <label class="form-label">

                                    Status
                                    <span class="required">*</span>

                                </label>

                                <select name="status" class="form-select" required>

                                    <option value="">
                                        -- Select Status --
                                    </option>

                                    <option value="Upcoming" <?= $status === 'Upcoming' ? 'selected' : '' ?>>
                                        Upcoming
                                    </option>

                                    <option value="Live" <?= $status === 'Live' ? 'selected' : '' ?>>
                                        Live
                                    </option>

                                    <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>
                                        Completed
                                    </option>

                                    <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>
                                        Cancelled
                                    </option>

                                </select>

                            </div>

                        </div>


                        <!-- =================================================
                         SCORE
                    ================================================== -->

                        <div class="section-title" style="margin-top:30px;">

                            <i class="bi bi-bar-chart-fill"></i>

                            <div>

                                <strong>
                                    Score
                                </strong>

                                <span>
                                    Leave blank if the match has not been played.
                                </span>

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label">
                                    Team One Score
                                </label>

                                <input type="number" name="team_one_score" class="form-control" min="0"
                                    placeholder="Example: 21" value="<?= e($teamOneScore) ?>">

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                    Team Two Score
                                </label>

                                <input type="number" name="team_two_score" class="form-control" min="0"
                                    placeholder="Example: 18" value="<?= e($teamTwoScore) ?>">

                            </div>

                        </div>


                        <!-- =================================================
                         DESCRIPTION
                    ================================================== -->

                        <div class="section-title" style="margin-top:30px;">

                            <i class="bi bi-card-text"></i>

                            <div>

                                <strong>
                                    Description
                                </strong>

                                <span>
                                    Add optional information about the match.
                                </span>

                            </div>

                        </div>


                        <textarea name="description" class="form-control"
                            placeholder="Example: First Year students vs Fifth Year students..."><?= e($description) ?></textarea>


                        <!-- =================================================
                         ACTIONS
                    ================================================== -->

                        <div class="form-actions">

                            <a href="matches.php" class="btn-cancel">
                                <i class="bi bi-arrow-left"></i>
                                Cancel
                            </a>

                            <button type="submit" class="btn-submit">
                                <i class="bi bi-plus-circle-fill"></i>
                                Add Match
                            </button>

                        </div>

                    </form>

                </div>

            </section>

        </div>

    </main>



</body>

</html>