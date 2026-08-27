<?php

declare(strict_types=1);

require_once __DIR__ . "/../includes/auth.php";
require_admin();

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/csrf.php";

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
| SEARCH / FILTER
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

/*
|--------------------------------------------------------------------------
| GET MATCHES
|--------------------------------------------------------------------------
*/

$sql = "
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
    WHERE 1=1
";

$params = [];

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= "
        AND (
            competition_name LIKE ?
            OR team_one LIKE ?
            OR team_two LIKE ?
            OR venue LIKE ?
            OR match_type LIKE ?
        )
    ";

    $searchLike = '%' . $search . '%';

    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
}

/*
|--------------------------------------------------------------------------
| STATUS FILTER
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    'Upcoming',
    'Live',
    'Completed',
    'Cancelled'
];

if (
    $statusFilter !== '' &&
    in_array($statusFilter, $allowedStatuses, true)
) {

    $sql .= " AND status = ? ";

    $params[] = $statusFilter;
}

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY
        match_date DESC,
        match_time DESC,
        id DESC
";

/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| COUNTS
|--------------------------------------------------------------------------
*/

$countStmt = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Upcoming') AS upcoming,
        SUM(status = 'Live') AS live,
        SUM(status = 'Completed') AS completed,
        SUM(status = 'Cancelled') AS cancelled
    FROM matches
");

$counts = $countStmt->fetch(PDO::FETCH_ASSOC);

$totalMatches = (int)($counts['total'] ?? 0);
$upcomingMatches = (int)($counts['upcoming'] ?? 0);
$liveMatches = (int)($counts['live'] ?? 0);
$completedMatches = (int)($counts['completed'] ?? 0);
$cancelledMatches = (int)($counts['cancelled'] ?? 0);

/*
|--------------------------------------------------------------------------
| ADMIN DISPLAY NAME
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
        MTU Badminton Club | Matches
    </title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
    /* =========================================================
       ROOT
    ========================================================= */

    :root {

        --primary: #8B5CF6;
        --primary-dark: #6D28D9;
        --primary-deep: #5B21B6;

        --indigo: #6366F1;

        --purple-soft: #F3E8FF;
        --purple-light: #FAF5FF;

        --sidebar: #11101A;
        --sidebar-light: #1B1926;

        --background: #F6F7FB;

        --white: #FFFFFF;

        --text: #111827;
        --text-soft: #374151;
        --muted: #6B7280;

        --border: #E5E7EB;

        --green: #16A34A;
        --green-soft: #DCFCE7;

        --orange: #D97706;
        --orange-soft: #FEF3C7;

        --red: #DC2626;
        --red-soft: #FEE2E2;

        --blue: #2563EB;
        --blue-soft: #DBEAFE;

        --shadow:
            0 10px 35px rgba(15, 23, 42, .06);

        --shadow-hover:
            0 18px 45px rgba(109, 40, 217, .12);

        --radius: 18px;

        --transition:
            all .25s ease;
    }


    /* =========================================================
       GLOBAL
    ========================================================= */

    * {
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
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

        animation:
            pageEnter .45s ease;
    }


    @keyframes pageEnter {

        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    a {
        text-decoration: none;
    }


    /* =========================================================
       SIDEBAR
    ========================================================= */

    .sidebar {

        position: fixed;

        left: 0;
        top: 0;

        width: 250px;

        height: 100vh;

        background:
            linear-gradient(180deg,
                #11101A,
                #171421);

        color: white;

        z-index: 1000;

        display: flex;

        flex-direction: column;

        padding: 24px 16px;

        box-shadow:
            8px 0 35px rgba(0, 0, 0, .08);
    }


    /* BRAND */

    .brand {

        display: flex;

        align-items: center;

        gap: 12px;

        padding:
            4px 10px 28px;
    }


    .brand-logo {

        width: 44px;

        height: 44px;

        border-radius: 14px;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--indigo));

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 22px;

        box-shadow:
            0 8px 20px rgba(139, 92, 246, .25);
    }


    .brand-text strong {

        display: block;

        font-size: 15px;

        letter-spacing: .2px;
    }


    .brand-text span {

        display: block;

        margin-top: 2px;

        color: #A1A1AA;

        font-size: 10px;

        font-weight: 700;

        letter-spacing: 1.2px;

        text-transform: uppercase;
    }


    /* NAV */

    .nav-section {

        margin:
            8px 10px 10px;

        color: #71717A;

        font-size: 10px;

        font-weight: 800;

        letter-spacing: 1.2px;

        text-transform: uppercase;
    }


    .side-nav {

        display: flex;

        flex-direction: column;

        gap: 5px;
    }


    .side-link {

        display: flex;

        align-items: center;

        gap: 12px;

        padding:
            12px 13px;

        border-radius: 12px;

        color: #A1A1AA;

        font-size: 13px;

        font-weight: 600;

        transition:
            var(--transition);
    }


    .side-link i {

        width: 20px;

        font-size: 17px;

        text-align: center;
    }


    .side-link:hover {

        background:
            rgba(139, 92, 246, .10);

        color: white;

        transform:
            translateX(2px);
    }


    .side-link.active {

        background:
            linear-gradient(135deg,
                rgba(139, 92, 246, .28),
                rgba(99, 102, 241, .18));

        color: white;

        box-shadow:
            inset 0 0 0 1px rgba(139, 92, 246, .12);
    }


    .side-link.danger:hover {

        background:
            rgba(220, 38, 38, .13);

        color: #FCA5A5;
    }


    /* SIDEBAR BOTTOM */

    .sidebar-bottom {

        margin-top: auto;
    }


    .admin-mini {

        display: flex;

        align-items: center;

        gap: 10px;

        padding:
            12px;

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
            28px 34px 50px;
    }


    .content {

        max-width: 1600px;

        margin: auto;
    }


    /* =========================================================
       TOPBAR
    ========================================================= */

    .topbar {

        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-bottom: 28px;
    }


    .page-title {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .page-title h1 {

        margin: 0;

        font-size: 29px;

        font-weight: 850;

        letter-spacing: -.9px;

        color: #111827;
    }


    .page-title p {

        margin:
            6px 0 0;

        color: var(--muted);

        font-size: 13px;
    }


    .top-actions {

        display: flex;

        align-items: center;

        gap: 9px;

        flex-wrap: wrap;
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

        color: var(--text-soft);

        transition:
            var(--transition);

        box-shadow:
            0 3px 12px rgba(15, 23, 42, .03);
    }


    .top-button:hover {

        transform:
            translateY(-2px);

        border-color:
            rgba(139, 92, 246, .25);

        color:
            var(--primary-dark);

        box-shadow:
            var(--shadow-hover);
    }


    .top-button.primary {

        border: none;

        color: white;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));
    }


    .top-button.primary:hover {

        color: white;

        box-shadow:
            0 9px 22px rgba(109, 40, 217, .22);
    }


    .top-button.danger {

        color: var(--red);

        background:
            #FFF7F7;

        border-color:
            #FECACA;
    }


    .top-button.danger:hover {

        color: white;

        background:
            var(--red);

        border-color:
            var(--red);
    }


    /* =========================================================
       HEADER BANNER
    ========================================================= */

    .hero {

        position: relative;

        overflow: hidden;

        padding:
            25px 28px;

        border-radius: 20px;

        margin-bottom: 22px;

        color: white;

        background:
            linear-gradient(115deg,
                #5B21B6 0%,
                #7C3AED 45%,
                #6366F1 100%);

        box-shadow:
            0 16px 38px rgba(91, 33, 182, .18);
    }


    .hero::before {

        content: "";

        position: absolute;

        width: 230px;

        height: 230px;

        right: 50px;

        top: -150px;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, .08);
    }


    .hero::after {

        content: "";

        position: absolute;

        width: 150px;

        height: 150px;

        right: -35px;

        bottom: -80px;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, .07);
    }


    .hero-content {

        position: relative;

        z-index: 2;
    }


    .hero small {

        display: block;

        margin-bottom: 5px;

        opacity: .75;

        font-size: 11px;

        font-weight: 700;

        letter-spacing: .8px;

        text-transform: uppercase;
    }


    .hero h2 {

        margin: 0;

        font-size: 23px;

        font-weight: 800;

        letter-spacing: -.5px;
    }


    .hero p {

        margin:
            7px 0 0;

        opacity: .82;

        font-size: 12px;
    }


    /* =========================================================
       STAT CARDS
    ========================================================= */

    .stats {

        display: grid;

        grid-template-columns:
            repeat(5, 1fr);

        gap: 14px;

        margin-bottom: 24px;
    }


    .stat-card {

        position: relative;

        overflow: hidden;

        min-height: 130px;

        padding: 18px;

        border-radius: 18px;

        background: white;

        border:
            1px solid var(--border);

        box-shadow:
            var(--shadow);

        transition:
            var(--transition);
    }


    .stat-card:hover {

        transform:
            translateY(-4px);

        box-shadow:
            var(--shadow-hover);

        border-color:
            rgba(139, 92, 246, .20);
    }


    .stat-icon {

        width: 38px;

        height: 38px;

        border-radius: 11px;

        display: flex;

        align-items: center;

        justify-content: center;

        color:
            var(--primary-dark);

        background:
            var(--purple-soft);

        font-size: 17px;
    }


    .stat-card.live .stat-icon {

        color:
            var(--red);

        background:
            var(--red-soft);
    }


    .stat-card.upcoming .stat-icon {

        color:
            var(--orange);

        background:
            var(--orange-soft);
    }


    .stat-card.completed .stat-icon {

        color:
            var(--green);

        background:
            var(--green-soft);
    }


    .stat-card.cancelled .stat-icon {

        color:
            var(--red);

        background:
            var(--red-soft);
    }


    .stat-label {

        margin-top: 13px;

        color:
            var(--muted);

        font-size: 9px;

        font-weight: 800;

        letter-spacing: .8px;

        text-transform: uppercase;
    }


    .stat-number {

        margin-top: 3px;

        color:
            #111827;

        font-size: 27px;

        line-height: 1;

        font-weight: 850;
    }


    /* =========================================================
       FILTER CARD
    ========================================================= */

    .filter-card {

        background: white;

        border:
            1px solid var(--border);

        border-radius: 18px;

        padding: 20px;

        margin-bottom: 22px;

        box-shadow:
            var(--shadow);
    }


    .filter-title {

        display: flex;

        align-items: center;

        gap: 10px;

        margin-bottom: 14px;
    }


    .filter-icon {

        width: 34px;

        height: 34px;

        border-radius: 10px;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            var(--purple-soft);

        color:
            var(--primary-dark);
    }


    .filter-title strong {

        display: block;

        font-size: 14px;
    }


    .filter-title span {

        display: block;

        color:
            var(--muted);

        font-size: 10px;

        margin-top: 2px;
    }


    .filter-form {

        display: grid;

        grid-template-columns:
            1fr 190px auto auto;

        gap: 9px;

        align-items: center;
    }


    .input-wrap {

        position: relative;
    }


    .input-wrap i {

        position: absolute;

        left: 14px;

        top: 50%;

        transform:
            translateY(-50%);

        color:
            #9CA3AF;
    }


    .filter-input,
    .filter-select {

        width: 100%;

        height: 45px;

        border:
            1px solid var(--border);

        border-radius: 11px;

        background:
            #FAFAFB;

        outline: none;

        font-size: 12px;

        transition:
            var(--transition);
    }


    .filter-input {

        padding:
            0 14px 0 40px;
    }


    .filter-select {

        padding:
            0 12px;
    }


    .filter-input:focus,
    .filter-select:focus {

        background: white;

        border-color:
            var(--primary);

        box-shadow:
            0 0 0 4px rgba(139, 92, 246, .10);
    }


    .filter-button {

        height: 45px;

        padding:
            0 18px;

        border: none;

        border-radius: 11px;

        background:
            linear-gradient(135deg,
                var(--primary),
                var(--primary-dark));

        color: white;

        font-size: 12px;

        font-weight: 800;

        transition:
            var(--transition);
    }


    .filter-button:hover {

        transform:
            translateY(-1px);

        box-shadow:
            0 8px 20px rgba(109, 40, 217, .18);
    }


    .clear-button {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        height: 45px;

        padding:
            0 15px;

        border:
            1px solid var(--border);

        border-radius: 11px;

        color:
            var(--text-soft);

        background:
            #F9FAFB;

        font-size: 12px;

        font-weight: 700;

        transition:
            var(--transition);
    }


    .clear-button:hover {

        background:
            var(--purple-soft);

        color:
            var(--primary-dark);

        border-color:
            rgba(139, 92, 246, .2);
    }


    /* =========================================================
       MATCH HEADER
    ========================================================= */

    .matches-heading {

        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-bottom: 13px;
    }


    .matches-title {

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .matches-title h2 {

        margin: 0;

        font-size: 20px;

        font-weight: 850;

        letter-spacing: -.4px;
    }


    .matches-title span {

        padding:
            6px 10px;

        border-radius: 999px;

        background:
            var(--purple-soft);

        color:
            var(--primary-dark);

        font-size: 10px;

        font-weight: 800;
    }


    .matches-info {

        color:
            var(--muted);

        font-size: 11px;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .table-card {

        overflow: hidden;

        background: white;

        border:
            1px solid var(--border);

        border-radius: 18px;

        box-shadow:
            var(--shadow);
    }


    .table-scroll {

        width: 100%;

        overflow-x: auto;

        scrollbar-width: thin;

        scrollbar-color:
            var(--primary) #F3E8FF;
    }


    .table-scroll::-webkit-scrollbar {

        height: 7px;
    }


    .table-scroll::-webkit-scrollbar-track {

        background:
            #F3E8FF;
    }


    .table-scroll::-webkit-scrollbar-thumb {

        background:
            var(--primary);

        border-radius: 999px;
    }


    table {

        width: 100%;

        min-width: 1450px;

        border-collapse: separate;

        border-spacing: 0;
    }


    thead th {

        position: sticky;

        top: 0;

        z-index: 5;

        padding:
            14px 12px;

        background:
            #171421;

        color:
            #E5E7EB;

        border: none;

        font-size: 9px;

        font-weight: 800;

        letter-spacing: .75px;

        text-transform: uppercase;

        white-space: nowrap;

        text-align: center;
    }


    thead th:first-child {

        border-top-left-radius:
            16px;
    }


    thead th:last-child {

        border-top-right-radius:
            16px;
    }


    tbody td {

        padding:
            14px 12px;

        border-bottom:
            1px solid #F1F2F5;

        color:
            var(--text-soft);

        font-size: 11px;

        font-weight: 500;

        text-align: center;

        vertical-align: middle;

        white-space: nowrap;
    }


    tbody tr {

        transition:
            var(--transition);
    }


    tbody tr:hover {

        background:
            #FBF9FF;
    }


    tbody tr:last-child td {

        border-bottom: none;
    }


    tbody td:first-child {

        color:
            #9CA3AF;

        font-weight: 800;
    }


    .competition-name {

        color:
            var(--primary-dark);

        font-weight: 800;

        max-width: 240px;

        white-space: normal;

        line-height: 1.4;
    }


    .team {

        color:
            #111827;

        font-weight: 800;

        font-size: 12px;
    }


    .vs {

        display: block;

        margin:
            3px 0;

        color:
            #9CA3AF;

        font-size: 8px;

        font-weight: 800;

        letter-spacing: 1px;
    }


    .score {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 13px;

        font-weight: 850;
    }


    .score-one {

        color:
            var(--primary-dark);
    }


    .score-two {

        color:
            #4338CA;
    }


    .score-divider {

        color:
            #9CA3AF;
    }


    .type-badge {

        display: inline-flex;

        padding:
            6px 9px;

        border-radius: 999px;

        background:
            var(--purple-soft);

        color:
            var(--primary-dark);

        font-size: 9px;

        font-weight: 800;

        max-width: 160px;

        white-space: normal;

        line-height: 1.2;

        justify-content: center;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .status-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding:
            6px 10px;

        border-radius: 999px;

        font-size: 9px;

        font-weight: 850;
    }


    .status-badge::before {

        content: "";

        width: 5px;

        height: 5px;

        border-radius: 50%;

        background:
            currentColor;
    }


    .status-upcoming {

        color:
            #92400E;

        background:
            var(--orange-soft);
    }


    .status-live {

        color:
            #991B1B;

        background:
            var(--red-soft);

        animation:
            livePulse 1.5s infinite;
    }


    .status-completed {

        color:
            #166534;

        background:
            var(--green-soft);
    }


    .status-cancelled {

        color:
            #991B1B;

        background:
            var(--red-soft);
    }


    @keyframes livePulse {

        0%,
        100% {
            box-shadow:
                0 0 0 0 rgba(220, 38, 38, .15);
        }

        50% {
            box-shadow:
                0 0 0 5px rgba(220, 38, 38, 0);
        }
    }


    /* =========================================================
       ACTION BUTTONS
    ========================================================= */

    .actions {

        display: flex;

        justify-content: center;

        align-items: center;

        gap: 6px;
    }


    .table-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 5px;

        border: none;

        border-radius: 8px;

        padding:
            7px 10px;

        font-size: 9px;

        font-weight: 800;

        transition:
            var(--transition);

        cursor: pointer;
    }


    .table-btn:hover {

        transform:
            translateY(-1px);
    }


    .edit-btn {

        background:
            var(--purple-soft);

        color:
            var(--primary-dark);
    }


    .edit-btn:hover {

        color: white;

        background:
            var(--primary);
    }


    .delete-btn {

        background:
            var(--red-soft);

        color:
            #991B1B;
    }


    .delete-btn:hover {

        color: white;

        background:
            var(--red);
    }


    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .empty-state {

        padding:
            75px 20px !important;

        color:
            var(--muted) !important;

        text-align:
            center !important;
    }


    .empty-icon {

        width: 60px;

        height: 60px;

        margin:
            0 auto 13px;

        border-radius: 17px;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            var(--purple-soft);

        color:
            var(--primary);

        font-size: 25px;
    }


    .empty-state strong {

        display: block;

        color:
            var(--text);

        font-size: 14px;

        margin-bottom: 4px;
    }


    .empty-state div:last-child {

        font-size: 11px;
    }


    /* =========================================================
       MOBILE TOGGLE
    ========================================================= */

    .mobile-toggle {

        display: none;

        width: 42px;

        height: 42px;

        border:
            1px solid var(--border);

        border-radius: 11px;

        background: white;

        color:
            var(--text);

        font-size: 18px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1350px) {

        .stats {

            grid-template-columns:
                repeat(3, 1fr);
        }

    }


    @media (max-width: 1100px) {

        .filter-form {

            grid-template-columns:
                1fr 180px;
        }


        .filter-button,
        .clear-button {

            width: 100%;
        }

    }


    @media (max-width: 1050px) {

        .sidebar {

            transform:
                translateX(-100%);

            transition:
                transform .3s ease;
        }


        .sidebar.open {

            transform:
                translateX(0);
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


        .topbar {

            gap: 12px;
        }

    }


    @media (max-width: 768px) {

        .main {

            padding:
                16px 14px 35px;
        }


        .topbar {

            align-items:
                flex-start;

            flex-direction:
                column;
        }


        .page-title h1 {

            font-size: 24px;
        }


        .top-actions {

            width: 100%;

            justify-content:
                flex-start;
        }


        .top-button {

            flex: 1;

            justify-content:
                center;
        }


        .hero {

            padding:
                21px;
        }


        .hero h2 {

            font-size: 20px;
        }


        .stats {

            grid-template-columns:
                1fr 1fr;

            gap: 10px;
        }


        .filter-form {

            grid-template-columns:
                1fr;
        }


        .filter-button,
        .clear-button {

            width: 100%;
        }


        .matches-heading {

            align-items:
                flex-start;

            gap: 10px;

            flex-direction:
                column;
        }


        .matches-info {

            display: none;
        }

    }


    @media (max-width: 480px) {

        .stats {

            grid-template-columns:
                1fr;
        }


        .top-actions {

            display: grid;

            grid-template-columns:
                1fr 1fr;
        }


        .top-button {

            width: 100%;
        }


        .hero {

            border-radius:
                16px;
        }


        .filter-card,
        .table-card {

            border-radius:
                15px;
        }

    }


    /* =========================================================
       ACCESSIBILITY
    ========================================================= */

    button:focus-visible,
    a:focus-visible,
    input:focus-visible,
    select:focus-visible {

        outline:
            3px solid rgba(139, 92, 246, .25);

        outline-offset:
            2px;
    }


    @media (prefers-reduced-motion: reduce) {

        *,
        *::before,
        *::after {

            animation-duration:
                .01ms !important;

            transition-duration:
                .01ms !important;

            scroll-behavior:
                auto !important;
        }
    }
    </style>

</head>


<body>


    <!-- =========================================================
     SIDEBAR
========================================================= -->

    <aside class="sidebar" id="sidebar">


        <div class="brand">

            <div class="brand-logo">
                🏸
            </div>

            <div class="brand-text">

                <strong>
                    MTU Badminton
                </strong>

                <span>
                    Club Admin
                </span>

            </div>

        </div>


        <div class="nav-section">
            Main Menu
        </div>


        <nav class="side-nav">


            <a href="dashboard.php" class="side-link">

                <i class="bi bi-grid-1x2-fill"></i>

                Dashboard

            </a>


            <a href="members.php" class="side-link">

                <i class="bi bi-people-fill"></i>

                Members

            </a>


            <a href="manage_news.php" class="side-link">

                <i class="bi bi-newspaper"></i>

                Manage News

            </a>


            <a href="add_news.php" class="side-link">

                <i class="bi bi-plus-circle-fill"></i>

                Post News

            </a>


            <a href="add_match.php" class="side-link">

                <i class="bi bi-trophy-fill"></i>

                Add Match

            </a>


            <a href="matches.php" class="side-link active">

                <i class="bi bi-calendar2-event-fill"></i>

                Matches

            </a>


        </nav>


        <div class="nav-section mt-4">
            System
        </div>


        <nav class="side-nav">


            <a href="../public/index.php" class="side-link" target="_blank">

                <i class="bi bi-globe2"></i>

                View Website

            </a>


            <a href="reset_system.php" class="side-link danger">

                <i class="bi bi-exclamation-triangle-fill"></i>

                Danger Zone

            </a>


        </nav>


        <div class="sidebar-bottom">


            <div class="admin-mini">

                <div class="admin-avatar">

                    <?= e($adminInitial) ?>

                </div>


                <div>

                    <strong>
                        <?= e($adminName) ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>


            <a href="../public/logout.php" class="side-link">

                <i class="bi bi-box-arrow-right"></i>

                Logout

            </a>


        </div>


    </aside>


    <!-- =========================================================
     MAIN
========================================================= -->

    <main class="main">


        <div class="content">


            <!-- =====================================================
             TOP BAR
        ====================================================== -->

            <div class="topbar">


                <div class="page-title">


                    <button class="mobile-toggle" id="mobileToggle" type="button" aria-label="Open navigation">

                        <i class="bi bi-list"></i>

                    </button>


                    <div>

                        <h1>
                            Matches
                        </h1>

                        <p>
                            Manage MTU Badminton Club competitions and matches.
                        </p>

                    </div>


                </div>


                <div class="top-actions">


                    <a href="../public/index.php" target="_blank" class="top-button">

                        <i class="bi bi-box-arrow-up-right"></i>

                        Website

                    </a>


                    <a href="add_match.php" class="top-button primary">

                        <i class="bi bi-plus-lg"></i>

                        Add Match

                    </a>


                    <a href="reset_system.php" class="top-button danger">

                        <i class="bi bi-shield-exclamation"></i>

                        Reset

                    </a>


                </div>


            </div>


            <!-- =====================================================
             HERO
        ====================================================== -->

            <section class="hero">


                <div class="hero-content">


                    <small>
                        MTU Badminton Club
                    </small>


                    <h2>
                        Competition & Match Management 🏸
                    </h2>


                    <p>
                        Create, update and manage university,
                        department, year and friendly matches.
                    </p>


                </div>


            </section>


            <!-- =====================================================
             STATISTICS
        ====================================================== -->

            <section class="stats">


                <div class="stat-card">

                    <div class="stat-icon">

                        <i class="bi bi-trophy-fill"></i>

                    </div>

                    <div class="stat-label">
                        Total Matches
                    </div>

                    <div class="stat-number">
                        <?= $totalMatches ?>
                    </div>

                </div>


                <div class="stat-card upcoming">

                    <div class="stat-icon">

                        <i class="bi bi-calendar-event-fill"></i>

                    </div>

                    <div class="stat-label">
                        Upcoming
                    </div>

                    <div class="stat-number">
                        <?= $upcomingMatches ?>
                    </div>

                </div>


                <div class="stat-card live">

                    <div class="stat-icon">

                        <i class="bi bi-broadcast-pin"></i>

                    </div>

                    <div class="stat-label">
                        Live
                    </div>

                    <div class="stat-number">
                        <?= $liveMatches ?>
                    </div>

                </div>


                <div class="stat-card completed">

                    <div class="stat-icon">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>

                    <div class="stat-label">
                        Completed
                    </div>

                    <div class="stat-number">
                        <?= $completedMatches ?>
                    </div>

                </div>


                <div class="stat-card cancelled">

                    <div class="stat-icon">

                        <i class="bi bi-x-circle-fill"></i>

                    </div>

                    <div class="stat-label">
                        Cancelled
                    </div>

                    <div class="stat-number">
                        <?= $cancelledMatches ?>
                    </div>

                </div>


            </section>


            <!-- =====================================================
             SEARCH / FILTER
        ====================================================== -->

            <section class="filter-card">


                <div class="filter-title">


                    <div class="filter-icon">

                        <i class="bi bi-search"></i>

                    </div>


                    <div>

                        <strong>
                            Find Matches
                        </strong>

                        <span>
                            Search by competition, team, venue or match type.
                        </span>

                    </div>


                </div>


                <form method="GET" class="filter-form">


                    <div class="input-wrap">

                        <i class="bi bi-search"></i>

                        <input type="text" name="search" class="filter-input"
                            placeholder="Search competition, team, venue..." value="<?= e($search) ?>">

                    </div>


                    <select name="status" class="filter-select">

                        <option value="">
                            All Status
                        </option>

                        <?php foreach ($allowedStatuses as $status): ?>

                        <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                            <?= e($status) ?>
                        </option>

                        <?php endforeach; ?>

                    </select>


                    <button type="submit" class="filter-button">

                        <i class="bi bi-search me-1"></i>

                        Search

                    </button>


                    <?php if ($search !== '' || $statusFilter !== ''): ?>

                    <a href="matches.php" class="clear-button">

                        <i class="bi bi-x-lg me-1"></i>

                        Clear

                    </a>

                    <?php else: ?>

                    <span></span>

                    <?php endif; ?>


                </form>


            </section>


            <!-- =====================================================
             MATCHES HEADING
        ====================================================== -->

            <div class="matches-heading">


                <div class="matches-title">


                    <h2>
                        All Matches
                    </h2>


                    <span>
                        <?= count($matches) ?> Results
                    </span>


                </div>


                <div class="matches-info">

                    <i class="bi bi-info-circle me-1"></i>

                    Latest matches appear first

                </div>


            </div>


            <!-- =====================================================
             TABLE
        ====================================================== -->

            <section class="table-card">


                <div class="table-scroll">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Competition
                                </th>

                                <th>
                                    Match Type
                                </th>

                                <th>
                                    Teams
                                </th>

                                <th>
                                    Score
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Time
                                </th>

                                <th>
                                    Venue
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (count($matches) > 0): ?>


                            <?php foreach ($matches as $match): ?>


                            <?php

                            $status = $match['status'];

                            $statusClass = match ($status) {

                                'Upcoming' =>
                                    'status-upcoming',

                                'Live' =>
                                    'status-live',

                                'Completed' =>
                                    'status-completed',

                                'Cancelled' =>
                                    'status-cancelled',

                                default =>
                                    'status-upcoming'
                            };


                            $matchDate = '';

                            if (!empty($match['match_date'])) {

                                $timestamp =
                                    strtotime(
                                        (string)$match['match_date']
                                    );

                                if ($timestamp !== false) {

                                    $matchDate =
                                        date(
                                            'd M Y',
                                            $timestamp
                                        );
                                }
                            }


                            $matchTime = '';

                            if (!empty($match['match_time'])) {

                                $timestamp =
                                    strtotime(
                                        (string)$match['match_time']
                                    );

                                if ($timestamp !== false) {

                                    $matchTime =
                                        date(
                                            'h:i A',
                                            $timestamp
                                        );
                                }
                            }


                            $teamOneScore =
                                $match['team_one_score'];

                            $teamTwoScore =
                                $match['team_two_score'];

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= (int)$match['id'] ?>

                                </td>


                                <!-- COMPETITION -->

                                <td>

                                    <div class="competition-name">

                                        <?= e($match['competition_name']) ?>

                                    </div>

                                </td>


                                <!-- MATCH TYPE -->

                                <td>

                                    <span class="type-badge">

                                        <?= e($match['match_type']) ?>

                                    </span>

                                </td>


                                <!-- TEAMS -->

                                <td>

                                    <div class="team">

                                        <?= e($match['team_one']) ?>

                                    </div>

                                    <span class="vs">
                                        VS
                                    </span>

                                    <div class="team">

                                        <?= e($match['team_two']) ?>

                                    </div>

                                </td>


                                <!-- SCORE -->

                                <td>

                                    <?php if (
                                        $teamOneScore !== null ||
                                        $teamTwoScore !== null
                                    ): ?>

                                    <div class="score">

                                        <span class="score-one">

                                            <?= $teamOneScore !== null
                                                    ? (int)$teamOneScore
                                                    : '-' ?>

                                        </span>

                                        <span class="score-divider">
                                            :
                                        </span>

                                        <span class="score-two">

                                            <?= $teamTwoScore !== null
                                                    ? (int)$teamTwoScore
                                                    : '-' ?>

                                        </span>

                                    </div>

                                    <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                    <?php endif; ?>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?= e($matchDate) ?>

                                </td>


                                <!-- TIME -->

                                <td>

                                    <?= $matchTime !== ''
                                        ? e($matchTime)
                                        : '—' ?>

                                </td>


                                <!-- VENUE -->

                                <td>

                                    <?= !empty($match['venue'])
                                        ? e($match['venue'])
                                        : '—' ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <span class="status-badge <?= e($statusClass) ?>">

                                        <?= e($status) ?>

                                    </span>

                                </td>


                                <!-- DESCRIPTION -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $match['description']
                                        )
                                    ): ?>

                                    <?= e(
                                            mb_strimwidth(
                                                (string)$match['description'],
                                                0,
                                                70,
                                                '...'
                                            )
                                        ) ?>

                                    <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions">


                                        <!-- EDIT -->

                                        <a href="edit_match.php?id=<?= (int)$match['id'] ?>" class="table-btn edit-btn"
                                            title="Edit Match">

                                            <i class="bi bi-pencil-fill"></i>

                                            Edit

                                        </a>


                                        <!-- DELETE -->

                                        <form method="POST" action="delete_match.php" style="display:inline;">


                                            <input type="hidden" name="id" value="<?= (int)$match['id'] ?>">


                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                                            <button type="submit" class="table-btn delete-btn" title="Delete Match"
                                                onclick="return confirm('Are you sure you want to permanently delete this match?');">

                                                <i class="bi bi-trash3-fill"></i>

                                                Delete

                                            </button>


                                        </form>


                                    </div>

                                </td>


                            </tr>


                            <?php endforeach; ?>


                            <?php else: ?>


                            <tr>

                                <td colspan="11" class="empty-state">


                                    <div class="empty-icon">

                                        <i class="bi bi-trophy"></i>

                                    </div>


                                    <strong>
                                        No matches found
                                    </strong>


                                    <div>

                                        <?php if (
                                        $search !== '' ||
                                        $statusFilter !== ''
                                    ): ?>

                                        Try changing your search
                                        or filter.

                                        <?php else: ?>

                                        No matches have been
                                        added yet.

                                        <?php endif; ?>

                                    </div>


                                </td>

                            </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </section>


        </div>


    </main>


    <!-- =========================================================
     MOBILE SIDEBAR SCRIPT
========================================================= -->

    <script>
    const mobileToggle =
        document.getElementById('mobileToggle');

    const sidebar =
        document.getElementById('sidebar');


    if (mobileToggle && sidebar) {

        mobileToggle.addEventListener(
            'click',
            function() {

                sidebar.classList.toggle('open');

            }
        );


        document.addEventListener(
            'click',
            function(event) {

                if (
                    window.innerWidth <= 1050 &&
                    sidebar.classList.contains('open') &&
                    !sidebar.contains(event.target) &&
                    !mobileToggle.contains(event.target)
                ) {

                    sidebar.classList.remove('open');

                }

            }
        );

    }
    </script>


</body>

</html>

<?php

$stmt->closeCursor();

?>