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

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$totalResult = $pdo->query(
    "SELECT COUNT(*) AS total FROM members"
);

$total = $totalResult->fetch(PDO::FETCH_ASSOC);


$pendingResult = $pdo->query(
    "SELECT COUNT(*) AS total
     FROM members
     WHERE status = 'Pending'"
);

$pending = $pendingResult->fetch(PDO::FETCH_ASSOC);


$approvedResult = $pdo->query(
    "SELECT COUNT(*) AS total
     FROM members
     WHERE status = 'Approved'"
);

$approved = $approvedResult->fetch(PDO::FETCH_ASSOC);


$rejectedResult = $pdo->query(
    "SELECT COUNT(*) AS total
     FROM members
     WHERE status = 'Rejected'"
);

$rejected = $rejectedResult->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');


if ($search !== '') {

    $searchLike = "%" . $search . "%";

    $stmt = $pdo->prepare(
        "SELECT
            id,
            student_id,
            username,
            roll_number,
            department,
            academic_year,
            gender,
            phone,
            email,
            created_at,
            updated_at,
            status
         FROM members
         WHERE
            student_id LIKE ?
            OR username LIKE ?
            OR email LIKE ?
         ORDER BY id DESC"
    );

    $stmt->execute([
        $searchLike,
        $searchLike,
        $searchLike,
    ]);

    $result = $stmt->fetchAll();

} else {

    $stmt = $pdo->prepare(
        "SELECT
            id,
            student_id,
            username,
            roll_number,
            department,
            academic_year,
            gender,
            phone,
            email,
            created_at,
            updated_at,
            status
         FROM members
         ORDER BY id DESC"
    );

    $stmt->execute();

    $result = $stmt->fetchAll();
}


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
        MTU Badminton Club | Admin Dashboard
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

        /*
     * IMPORTANT:
     * Sidebar can now scroll vertically
     * when the menu becomes taller than
     * the screen.
     */

        overflow-y: auto;

        overflow-x: hidden;

        scrollbar-width: thin;

        scrollbar-color:
            rgba(139, 92, 246, .55) transparent;

        overscroll-behavior: contain;

        scroll-behavior: smooth;
    }


    /* Chrome / Edge / Safari scrollbar */

    .sidebar::-webkit-scrollbar {

        width: 5px;
    }


    .sidebar::-webkit-scrollbar-track {

        background: transparent;
    }


    .sidebar::-webkit-scrollbar-thumb {

        background:
            rgba(139, 92, 246, .45);

        border-radius: 999px;
    }


    .sidebar::-webkit-scrollbar-thumb:hover {

        background:
            rgba(139, 92, 246, .85);
    }


    /* =========================================================
   BRAND
========================================================= */

    .brand {

        display: flex;

        align-items: center;

        gap: 12px;

        padding:
            4px 10px 28px;

        flex-shrink: 0;
    }


    .brand-logo {

        width: 44px;

        height: 44px;

        flex-shrink: 0;

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


    /* =========================================================
   NAVIGATION
========================================================= */

    .nav-section {

        margin:
            8px 10px 10px;

        color: #71717A;

        font-size: 10px;

        font-weight: 800;

        letter-spacing: 1.2px;

        text-transform: uppercase;

        flex-shrink: 0;
    }


    .side-nav {

        display: flex;

        flex-direction: column;

        gap: 5px;

        flex-shrink: 0;
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

        flex-shrink: 0;
    }


    .side-link i {

        width: 20px;

        font-size: 17px;

        text-align: center;

        flex-shrink: 0;
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


    /* =========================================================
   SIDEBAR BOTTOM
========================================================= */

    .sidebar-bottom {

        margin-top: auto;

        padding-top: 20px;

        flex-shrink: 0;
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

        flex-shrink: 0;

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

        background: var(--red);

        border-color: var(--red);
    }


    /* =========================================================
   WELCOME
========================================================= */

    .welcome {

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


    .welcome::before {

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


    .welcome::after {

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


    .welcome-content {

        position: relative;

        z-index: 2;
    }


    .welcome small {

        display: block;

        margin-bottom: 5px;

        opacity: .75;

        font-size: 11px;

        font-weight: 700;

        letter-spacing: .8px;

        text-transform: uppercase;
    }


    .welcome h2 {

        margin: 0;

        font-size: 23px;

        font-weight: 800;

        letter-spacing: -.5px;
    }


    .welcome p {

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
            repeat(4, 1fr);

        gap: 16px;

        margin-bottom: 24px;
    }


    .stat-card {

        position: relative;

        overflow: hidden;

        min-height: 145px;

        padding: 21px;

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
            translateY(-5px);

        box-shadow:
            var(--shadow-hover);

        border-color:
            rgba(139, 92, 246, .20);
    }


    .stat-card::after {

        content: "";

        position: absolute;

        width: 105px;

        height: 105px;

        right: -35px;

        bottom: -42px;

        border-radius: 50%;

        background:
            var(--purple-soft);
    }


    .stat-card.pending::after {
        background:
            var(--orange-soft);
    }


    .stat-card.approved::after {
        background:
            var(--green-soft);
    }


    .stat-card.rejected::after {
        background:
            var(--red-soft);
    }


    .stat-top {

        position: relative;

        z-index: 2;

        display: flex;

        justify-content: space-between;

        align-items: center;
    }


    .stat-icon {

        width: 40px;

        height: 40px;

        border-radius: 12px;

        display: flex;

        align-items: center;

        justify-content: center;

        color:
            var(--primary-dark);

        background:
            var(--purple-soft);

        font-size: 18px;
    }


    .pending .stat-icon {

        color:
            var(--orange);

        background:
            var(--orange-soft);
    }


    .approved .stat-icon {

        color:
            var(--green);

        background:
            var(--green-soft);
    }


    .rejected .stat-icon {

        color:
            var(--red);

        background:
            var(--red-soft);
    }


    .stat-label {

        margin-top: 19px;

        color:
            var(--muted);

        font-size: 10px;

        font-weight: 800;

        letter-spacing: .9px;

        text-transform: uppercase;
    }


    .stat-number {

        margin-top: 4px;

        color: #111827;

        font-size: 32px;

        line-height: 1;

        font-weight: 850;

        letter-spacing: -1px;
    }


    /* =========================================================
   QUICK ACTIONS
========================================================= */

    .quick-actions {

        display: grid;

        grid-template-columns:
            repeat(4, 1fr);

        gap: 14px;

        margin-bottom: 24px;
    }


    .quick-action {

        display: flex;

        align-items: center;

        gap: 14px;

        padding:
            17px 18px;

        border-radius: 16px;

        background: white;

        border:
            1px solid var(--border);

        color: var(--text);

        box-shadow:
            var(--shadow);

        transition:
            var(--transition);
    }


    .quick-action:hover {

        transform:
            translateY(-3px);

        border-color:
            rgba(139, 92, 246, .22);

        box-shadow:
            var(--shadow-hover);

        color:
            var(--primary-dark);
    }


    .quick-icon {

        width: 42px;

        height: 42px;

        flex-shrink: 0;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 12px;

        background:
            var(--purple-soft);

        color:
            var(--primary-dark);

        font-size: 18px;
    }


    .quick-action strong {

        display: block;

        font-size: 13px;
    }


    .quick-action span {

        display: block;

        margin-top: 3px;

        color:
            var(--muted);

        font-size: 10px;
    }


    /* =========================================================
   SEARCH
========================================================= */

    .search-card {

        background: white;

        border:
            1px solid var(--border);

        border-radius: 18px;

        padding: 20px;

        margin-bottom: 25px;

        box-shadow:
            var(--shadow);
    }


    .search-header {

        display: flex;

        align-items: center;

        gap: 10px;

        margin-bottom: 14px;
    }


    .search-header-icon {

        width: 34px;

        height: 34px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 10px;

        color:
            var(--primary-dark);

        background:
            var(--purple-soft);
    }


    .search-header strong {

        font-size: 14px;
    }


    .search-header span {

        display: block;

        color:
            var(--muted);

        font-size: 10px;

        margin-top: 2px;
    }


    .search-form {

        display: flex;

        gap: 9px;
    }


    .search-input-wrap {

        position: relative;

        flex: 1;
    }


    .search-input-wrap i {

        position: absolute;

        left: 14px;

        top: 50%;

        transform:
            translateY(-50%);

        color:
            #9CA3AF;
    }


    .search-input {

        width: 100%;

        height: 45px;

        border:
            1px solid var(--border);

        border-radius: 11px;

        background:
            #FAFAFB;

        padding:
            0 14px 0 40px;

        outline: none;

        font-size: 12px;

        transition:
            var(--transition);
    }


    .search-input:focus {

        background: white;

        border-color:
            var(--primary);

        box-shadow:
            0 0 0 4px rgba(139, 92, 246, .10);
    }


    .search-button {

        height: 45px;

        padding:
            0 21px;

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


    .search-button:hover {

        transform:
            translateY(-1px);

        box-shadow:
            0 8px 20px rgba(109, 40, 217, .18);
    }


    .search-result {

        display: flex;

        align-items: center;

        gap: 9px;

        margin-top: 12px;

        color:
            var(--muted);

        font-size: 11px;
    }


    .search-result strong {

        color:
            var(--primary-dark);
    }


    .clear-search {

        padding:
            5px 9px;

        border-radius: 8px;

        color:
            var(--text-soft);

        background:
            #F3F4F6;

        font-weight: 700;

        transition:
            var(--transition);
    }


    .clear-search:hover {

        color:
            var(--primary-dark);

        background:
            var(--purple-soft);
    }


    /* =========================================================
   MEMBERS HEADER
========================================================= */

    .members-heading {

        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-bottom: 13px;
    }


    .members-title {

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .members-title h2 {

        margin: 0;

        font-size: 20px;

        font-weight: 850;

        letter-spacing: -.4px;
    }


    .members-title span {

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


    .members-info {

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

        min-width: 1500px;

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
    }


    thead th:first-child {

        border-top-left-radius: 16px;
    }


    thead th:last-child {

        border-top-right-radius: 16px;
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


    tbody td:nth-child(2) {

        color:
            var(--primary-dark);

        font-weight: 800;
    }


    tbody td:nth-child(3) {

        color:
            #111827;

        font-weight: 750;
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


    .status-pending {

        color:
            #92400E;

        background:
            var(--orange-soft);
    }


    .status-approved {

        color:
            #166534;

        background:
            var(--green-soft);
    }


    .status-rejected {

        color:
            #991B1B;

        background:
            var(--red-soft);
    }


    /* =========================================================
   TABLE BUTTONS
========================================================= */

    .table-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 5px;

        border: none;

        border-radius: 8px;

        padding:
            6px 9px;

        font-size: 9px;

        font-weight: 800;

        transition:
            var(--transition);
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


    .approve-btn {

        background:
            var(--green-soft);

        color:
            #166534;
    }


    .approve-btn:hover {

        color: white;

        background:
            var(--green);
    }


    .reject-btn {

        background:
            var(--red-soft);

        color:
            #991B1B;
    }


    .reject-btn:hover {

        color: white;

        background:
            var(--red);
    }


    .approval-badge {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding:
            6px 10px;

        border-radius: 999px;

        font-size: 9px;

        font-weight: 800;
    }


    .approval-approved {

        color:
            #166534;

        background:
            var(--green-soft);
    }


    .approval-rejected {

        color:
            #991B1B;

        background:
            var(--red-soft);
    }


    /* =========================================================
   EMPTY
========================================================= */

    .empty-state {

        padding:
            65px 20px !important;

        color:
            var(--muted) !important;

        text-align:
            center !important;
    }


    .empty-icon {

        width: 55px;

        height: 55px;

        margin:
            0 auto 12px;

        border-radius: 16px;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            var(--purple-soft);

        color:
            var(--primary);

        font-size: 23px;
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
   MOBILE OVERLAY
========================================================= */

    .sidebar-overlay {

        display: none;

        position: fixed;

        inset: 0;

        background:
            rgba(15, 23, 42, .45);

        backdrop-filter:
            blur(3px);

        z-index: 999;
    }


    .sidebar-overlay.show {

        display: block;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1200px) {

        .stats {

            grid-template-columns:
                repeat(2, 1fr);
        }


        .quick-actions {

            grid-template-columns:
                repeat(2, 1fr);
        }

    }


    @media (max-width: 1050px) {

        .sidebar {

            transform:
                translateX(-100%);

            transition:
                transform .3s ease;

            /*
         * Keep scrolling enabled
         * on mobile.
         */

            overflow-y: auto;
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

            flex-shrink: 0;
        }


        .topbar {

            gap: 12px;
        }


        .top-actions {

            flex-wrap: wrap;

            justify-content: flex-end;
        }

    }


    @media (max-width: 768px) {

        .main {

            padding:
                16px 14px 35px;
        }


        .topbar {

            align-items: flex-start;

            flex-direction: column;
        }


        .page-title {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .page-title h1 {

            font-size: 24px;
        }


        .top-actions {

            width: 100%;

            justify-content: flex-start;
        }


        .top-button {

            flex: 1;

            justify-content: center;
        }


        .welcome {

            padding: 21px;
        }


        .welcome h2 {

            font-size: 20px;
        }


        .stats {

            grid-template-columns:
                1fr 1fr;

            gap: 10px;
        }


        .stat-card {

            min-height: 130px;

            padding: 17px;
        }


        .stat-number {

            font-size: 27px;
        }


        .quick-actions {

            grid-template-columns:
                1fr;
        }


        .search-form {

            flex-direction: column;
        }


        .search-button {

            width: 100%;
        }


        .members-heading {

            align-items: flex-start;

            gap: 10px;

            flex-direction: column;
        }


        .members-info {

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


        .members-title {

            flex-wrap: wrap;
        }


        .welcome {

            border-radius: 16px;
        }


        .search-card,
        .table-card {

            border-radius: 15px;
        }

    }


    /* =========================================================
   ACCESSIBILITY
========================================================= */

    button:focus-visible,
    a:focus-visible,
    input:focus-visible {

        outline:
            3px solid rgba(139, 92, 246, .25);

        outline-offset: 2px;
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
     MOBILE OVERLAY
========================================================= -->

    <div class="sidebar-overlay" id="sidebarOverlay"></div>


    <!-- =========================================================
     SIDEBAR
========================================================= -->

    <aside class="sidebar" id="sidebar">


        <!-- BRAND -->

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


        <!-- MAIN MENU -->

        <div class="nav-section">
            Main Menu
        </div>


        <nav class="side-nav">


            <a href="dashboard.php" class="side-link active">

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

                Add Matches

            </a>


        </nav>


        <!-- SYSTEM -->

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


        <!-- SIDEBAR BOTTOM -->

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


                    <button class="mobile-toggle" id="mobileToggle" type="button" aria-label="Open navigation"
                        aria-controls="sidebar" aria-expanded="false">

                        <i class="bi bi-list"></i>

                    </button>


                    <div>

                        <h1>
                            Dashboard
                        </h1>

                        <p>
                            Manage your MTU Badminton Club
                            administration.
                        </p>

                    </div>


                </div>


                <div class="top-actions">


                    <a href="../public/index.php" target="_blank" class="top-button">

                        <i class="bi bi-box-arrow-up-right"></i>

                        Website

                    </a>


                    <a href="add_news.php" class="top-button primary">

                        <i class="bi bi-plus-lg"></i>

                        Post News

                    </a>


                    <a href="add_match.php" class="top-button primary">

                        <i class="bi bi-trophy-fill"></i>

                        Add Match

                    </a>


                    <a href="reset_system.php" class="top-button danger">

                        <i class="bi bi-shield-exclamation"></i>

                        Reset

                    </a>


                </div>


            </div>


            <!-- =====================================================
             WELCOME
        ====================================================== -->

            <section class="welcome">


                <div class="welcome-content">


                    <small>
                        MTU Badminton Club Administration
                    </small>


                    <h2>

                        Welcome back,
                        <?= e($adminName) ?> 👋

                    </h2>


                    <p>

                        Here's what's happening with your
                        club members today.

                    </p>


                </div>


            </section>


            <!-- =====================================================
             STATISTICS
        ====================================================== -->

            <section class="stats">


                <!-- TOTAL -->

                <div class="stat-card">

                    <div class="stat-top">

                        <div class="stat-icon">

                            <i class="bi bi-people-fill"></i>

                        </div>

                    </div>


                    <div class="stat-label">
                        Total Members
                    </div>


                    <div class="stat-number">

                        <?= (int)$total['total'] ?>

                    </div>

                </div>


                <!-- PENDING -->

                <div class="stat-card pending">

                    <div class="stat-top">

                        <div class="stat-icon">

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                    </div>


                    <div class="stat-label">
                        Pending
                    </div>


                    <div class="stat-number">

                        <?= (int)$pending['total'] ?>

                    </div>

                </div>


                <!-- APPROVED -->

                <div class="stat-card approved">

                    <div class="stat-top">

                        <div class="stat-icon">

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                    </div>


                    <div class="stat-label">
                        Approved
                    </div>


                    <div class="stat-number">

                        <?= (int)$approved['total'] ?>

                    </div>

                </div>


                <!-- REJECTED -->

                <div class="stat-card rejected">

                    <div class="stat-top">

                        <div class="stat-icon">

                            <i class="bi bi-x-circle-fill"></i>

                        </div>

                    </div>


                    <div class="stat-label">
                        Rejected
                    </div>


                    <div class="stat-number">

                        <?= (int)$rejected['total'] ?>

                    </div>

                </div>


            </section>


            <!-- =====================================================
             QUICK ACTIONS
        ====================================================== -->

            <section class="quick-actions">


                <!-- MEMBERS -->

                <a href="members.php" class="quick-action">

                    <div class="quick-icon">

                        <i class="bi bi-people"></i>

                    </div>

                    <div>

                        <strong>
                            View Members
                        </strong>

                        <span>
                            Manage registered students
                        </span>

                    </div>

                </a>


                <!-- NEWS -->

                <a href="add_news.php" class="quick-action">

                    <div class="quick-icon">

                        <i class="bi bi-megaphone-fill"></i>

                    </div>

                    <div>

                        <strong>
                            Post News
                        </strong>

                        <span>
                            Publish a new announcement
                        </span>

                    </div>

                </a>


                <!-- ADD MATCH -->

                <a href="add_match.php" class="quick-action">

                    <div class="quick-icon">

                        <i class="bi bi-trophy-fill"></i>

                    </div>

                    <div>

                        <strong>
                            Add Match
                        </strong>

                        <span>
                            Create a new competition or match
                        </span>

                    </div>

                </a>


                <!-- MATCHES -->

                <a href="matches.php" class="quick-action">

                    <div class="quick-icon">

                        <i class="bi bi-calendar2-event-fill"></i>

                    </div>

                    <div>

                        <strong>
                            Matches
                        </strong>

                        <span>
                            View, edit and delete matches
                        </span>

                    </div>

                </a>


            </section>


            <!-- =====================================================
             SEARCH
        ====================================================== -->

            <section class="search-card">


                <div class="search-header">


                    <div class="search-header-icon">

                        <i class="bi bi-search"></i>

                    </div>


                    <div>

                        <strong>
                            Search Members
                        </strong>

                        <span>
                            Search by Student ID, name or email
                        </span>

                    </div>


                </div>


                <form method="GET" class="search-form">


                    <div class="search-input-wrap">


                        <i class="bi bi-search"></i>


                        <input type="text" name="search" class="search-input"
                            placeholder="Search Student ID, Name or Email..." value="<?= e($search) ?>">


                    </div>


                    <button type="submit" class="search-button">

                        <i class="bi bi-search me-1"></i>

                        Search

                    </button>


                </form>


                <?php if ($search !== ''): ?>


                <div class="search-result">


                    <span>
                        Searching for:
                    </span>


                    <strong>
                        <?= e($search) ?>
                    </strong>


                    <a href="dashboard.php" class="clear-search">

                        <i class="bi bi-x"></i>

                        Clear

                    </a>


                </div>


                <?php endif; ?>


            </section>


            <!-- =====================================================
             MEMBERS HEADING
        ====================================================== -->

            <div class="members-heading">


                <div class="members-title">


                    <h2>
                        Registered Students
                    </h2>


                    <span>

                        <?= (int)$total['total'] ?>

                        Members

                    </span>


                </div>


                <div class="members-info">

                    <i class="bi bi-info-circle me-1"></i>

                    Latest registrations appear first

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

                                <th>ID</th>

                                <th>Student ID</th>

                                <th>Name</th>

                                <th>Roll Number</th>

                                <th>Department</th>

                                <th>Academic Year</th>

                                <th>Gender</th>

                                <th>Phone</th>

                                <th>Email</th>

                                <th>Register Date</th>

                                <th>Updated Date</th>

                                <th>Edit / Delete</th>

                                <th>Status</th>

                                <th>Approval</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (count($result) > 0): ?>


                            <?php foreach ($result as $row): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= (int)$row['id'] ?>

                                </td>


                                <!-- STUDENT ID -->

                                <td>

                                    <?= e($row['student_id']) ?>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <?= e($row['username']) ?>

                                </td>


                                <!-- ROLL -->

                                <td>

                                    <?= e($row['roll_number']) ?>

                                </td>


                                <!-- DEPARTMENT -->

                                <td>

                                    <?= e($row['department']) ?>

                                </td>


                                <!-- ACADEMIC YEAR -->

                                <td>

                                    <?= e($row['academic_year']) ?>

                                </td>


                                <!-- GENDER -->

                                <td>

                                    <?= e($row['gender']) ?>

                                </td>


                                <!-- PHONE -->

                                <td>

                                    <?= e($row['phone']) ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?= e($row['email']) ?>

                                </td>


                                <!-- CREATED -->

                                <td>

                                    <?= e($row['created_at']) ?>

                                </td>


                                <!-- UPDATED -->

                                <td>

                                    <?= e($row['updated_at']) ?>

                                </td>


                                <!-- EDIT / DELETE -->

                                <td>


                                    <a href="edit_student.php?id=<?= (int)$row['id'] ?>" class="table-btn edit-btn">

                                        <i class="bi bi-pencil-fill"></i>

                                        Edit

                                    </a>


                                    <form method="POST" action="delete.php" style="display:inline;">


                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">


                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                                        <button type="submit" class="table-btn delete-btn"
                                            onclick="return confirm('Are you sure you want to permanently delete this member?');">

                                            <i class="bi bi-trash3-fill"></i>

                                            Delete

                                        </button>


                                    </form>


                                </td>


                                <!-- STATUS -->

                                <td>


                                    <?php if ($row['status'] === 'Pending'): ?>


                                    <span class="status-badge status-pending">
                                        Pending
                                    </span>


                                    <?php elseif ($row['status'] === 'Approved'): ?>


                                    <span class="status-badge status-approved">
                                        Approved
                                    </span>


                                    <?php else: ?>


                                    <span class="status-badge status-rejected">
                                        Rejected
                                    </span>


                                    <?php endif; ?>


                                </td>


                                <!-- APPROVAL -->

                                <td>


                                    <?php if ($row['status'] === 'Pending'): ?>


                                    <!-- APPROVE -->

                                    <form method="POST" action="approve.php" style="display:inline;">


                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">


                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                                        <button type="submit" class="table-btn approve-btn"
                                            onclick="return confirm('Are you sure you want to approve this member?');">

                                            <i class="bi bi-check-lg"></i>

                                            Approve

                                        </button>


                                    </form>


                                    <!-- REJECT -->

                                    <form method="POST" action="reject.php" style="display:inline;">


                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">


                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                                        <button type="submit" class="table-btn reject-btn"
                                            onclick="return confirm('Are you sure you want to reject this member?');">

                                            <i class="bi bi-x-lg"></i>

                                            Reject

                                        </button>


                                    </form>


                                    <?php elseif ($row['status'] === 'Approved'): ?>


                                    <span class="approval-badge approval-approved">

                                        <i class="bi bi-check-circle-fill"></i>

                                        Approved

                                    </span>


                                    <?php else: ?>


                                    <span class="approval-badge approval-rejected">

                                        <i class="bi bi-x-circle-fill"></i>

                                        Rejected

                                    </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                            <?php endforeach; ?>


                            <?php else: ?>


                            <tr>


                                <td colspan="14" class="empty-state">


                                    <div class="empty-icon">

                                        <i class="bi bi-person-x"></i>

                                    </div>


                                    <strong>
                                        No members found
                                    </strong>


                                    <div>
                                        Try changing your search.
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

    const sidebarOverlay =
        document.getElementById('sidebarOverlay');


    function openSidebar() {

        if (!sidebar) return;

        sidebar.classList.add('open');

        if (sidebarOverlay) {

            sidebarOverlay.classList.add('show');

        }

        if (mobileToggle) {

            mobileToggle.setAttribute(
                'aria-expanded',
                'true'
            );

        }

    }


    function closeSidebar() {

        if (!sidebar) return;

        sidebar.classList.remove('open');

        if (sidebarOverlay) {

            sidebarOverlay.classList.remove('show');

        }

        if (mobileToggle) {

            mobileToggle.setAttribute(
                'aria-expanded',
                'false'
            );

        }

    }


    if (mobileToggle && sidebar) {

        mobileToggle.addEventListener(
            'click',
            function(event) {

                event.stopPropagation();

                if (sidebar.classList.contains('open')) {

                    closeSidebar();

                } else {

                    openSidebar();

                }

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

                    closeSidebar();

                }

            }
        );


        if (sidebarOverlay) {

            sidebarOverlay.addEventListener(
                'click',
                function() {

                    closeSidebar();

                }
            );

        }


        window.addEventListener(
            'resize',
            function() {

                if (window.innerWidth > 1050) {

                    closeSidebar();

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