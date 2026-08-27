<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";


/**
 * =========================================================
 * ESCAPE OUTPUT
 * =========================================================
 */

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/**
 * =========================================================
 * GET MEMBERS
 * =========================================================
 */

$sql = "SELECT
            id,
            username,
            student_id,
            roll_number,
            department,
            academic_year,
            gender,
            phone,
            email,
            profile_picture,
            status,
            created_at
        FROM members
        ORDER BY created_at DESC";

$result = $pdo->query($sql);

$members = $result->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Members | MTU Badminton Club
    </title>


    <!-- =====================================================
         TAILWIND CSS
    ====================================================== -->

    <script src="https://cdn.tailwindcss.com"></script>


    <!-- =====================================================
         GOOGLE FONTS
    ====================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap"
        rel="stylesheet">


    <!-- =====================================================
         MATERIAL SYMBOLS
    ====================================================== -->

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
        rel="stylesheet">


    <style>
    /* =====================================================
           GLOBAL
        ===================================================== */

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background:
            radial-gradient(circle at 10% 10%,
                rgba(124, 58, 237, 0.12),
                transparent 30%),
            radial-gradient(circle at 90% 90%,
                rgba(0, 0, 0, 0.06),
                transparent 30%),
            #f8fafc;
    }


    h1,
    h2,
    h3 {
        font-family: 'Montserrat', sans-serif;
    }


    .material-symbols-outlined {
        font-variation-settings:
            'FILL'0,
            'wght'400,
            'GRAD'0,
            'opsz'24;
    }


    /* =====================================================
           PAGE CONTAINER
        ====================================================== */

    .page-container {
        max-width: 1280px;
        margin: auto;
    }


    /* =====================================================
           DASHBOARD BUTTON
        ====================================================== */

    .dashboard-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        padding: 12px 20px;

        border-radius: 12px;

        background:
            linear-gradient(135deg,
                #7c3aed,
                #5b21b6);

        color: white;

        font-size: 14px;

        font-weight: 700;

        text-decoration: none;

        box-shadow:
            0 8px 20px rgba(124, 58, 237, 0.25);

        transition:
            all 0.25s ease;
    }


    .dashboard-btn:hover {

        transform:
            translateY(-2px);

        background:
            linear-gradient(135deg,
                #6d28d9,
                #4c1d95);

        box-shadow:
            0 12px 25px rgba(124, 58, 237, 0.35);
    }


    .dashboard-btn:active {

        transform:
            translateY(0);
    }


    /* =====================================================
           HEADER
        ====================================================== */

    .page-header {

        background:
            rgba(255, 255, 255, 0.95);

        border:
            1px solid #e5e7eb;

        border-left:
            5px solid #7c3aed;

        border-radius:
            20px;

        padding:
            26px;

        box-shadow:
            0 10px 30px rgba(0, 0, 0, 0.05);

        transition:
            all 0.3s ease;
    }


    .page-header:hover {

        box-shadow:
            0 15px 40px rgba(124, 58, 237, 0.10);
    }


    .club-label {

        color:
            #7c3aed;

        font-size:
            12px;

        font-weight:
            800;

        letter-spacing:
            0.12em;

        text-transform:
            uppercase;
    }


    .page-title {

        color:
            #111827;

        font-size:
            34px;

        font-weight:
            800;
    }


    .page-description {

        color:
            #6b7280;

        font-size:
            14px;

        margin-top:
            6px;
    }


    /* =====================================================
           MEMBER COUNT
        ====================================================== */

    .member-count {

        min-width:
            150px;

        background:
            #111827;

        border:
            1px solid #111827;

        border-radius:
            16px;

        padding:
            16px 20px;

        color:
            white;

        box-shadow:
            0 10px 25px rgba(0, 0, 0, 0.12);
    }


    .member-count-label {

        color:
            #d1d5db;

        font-size:
            10px;

        font-weight:
            700;

        text-transform:
            uppercase;

        letter-spacing:
            0.1em;
    }


    .member-count-number {

        color:
            #c4b5fd;

        font-size:
            30px;

        font-weight:
            800;

        line-height:
            1.1;

        margin-top:
            3px;
    }


    /* =====================================================
           SEARCH
        ====================================================== */

    .search-wrapper {

        position:
            relative;

        max-width:
            600px;
    }


    .search-input {

        width:
            100%;

        background:
            white;

        border:
            1px solid #e5e7eb;

        border-radius:
            14px;

        padding:
            14px 18px 14px 50px;

        outline:
            none;

        color:
            #111827;

        font-size:
            14px;

        box-shadow:
            0 5px 18px rgba(0, 0, 0, 0.04);

        transition:
            all 0.25s ease;
    }


    .search-input:focus {

        border-color:
            #7c3aed;

        box-shadow:
            0 0 0 4px rgba(124, 58, 237, 0.10);
    }


    .search-icon {

        position:
            absolute;

        left:
            17px;

        top:
            50%;

        transform:
            translateY(-50%);

        color:
            #7c3aed;

        pointer-events:
            none;
    }


    /* =====================================================
           MEMBER CARD
        ====================================================== */

    .member-card {

        background:
            white;

        border:
            1px solid #e5e7eb;

        border-radius:
            20px;

        overflow:
            hidden;

        box-shadow:
            0 6px 20px rgba(0, 0, 0, 0.05);

        transition:
            all 0.28s ease;
    }


    .member-card:hover {

        transform:
            translateY(-6px);

        border-color:
            #c4b5fd;

        box-shadow:
            0 18px 40px rgba(124, 58, 237, 0.14);
    }


    /* =====================================================
           CARD TOP
        ====================================================== */

    .card-top {

        height:
            90px;

        background:
            linear-gradient(135deg,
                #111827,
                #4c1d95,
                #7c3aed);

        position:
            relative;
    }


    /* =====================================================
           STATUS
        ====================================================== */

    .status-approved {

        background:
            #dcfce7;

        color:
            #166534;
    }


    .status-rejected {

        background:
            #fee2e2;

        color:
            #991b1b;
    }


    .status-pending {

        background:
            #fef3c7;

        color:
            #92400e;
    }


    /* =====================================================
           PROFILE IMAGE
        ====================================================== */

    .profile-image {

        width:
            112px;

        height:
            112px;

        border-radius:
            50%;

        object-fit:
            cover;

        border:
            5px solid white;

        box-shadow:
            0 8px 20px rgba(0, 0, 0, 0.16);
    }


    .profile-placeholder {

        width:
            112px;

        height:
            112px;

        border-radius:
            50%;

        border:
            5px solid white;

        background:
            #ede9fe;

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        box-shadow:
            0 8px 20px rgba(0, 0, 0, 0.16);
    }


    .profile-placeholder span {

        color:
            #7c3aed;

        font-size:
            48px;
    }


    /* =====================================================
           MEMBER NAME
        ====================================================== */

    .member-name {

        color:
            #111827;

        font-size:
            18px;

        font-weight:
            800;
    }


    .student-id {

        color:
            #7c3aed;

        font-size:
            13px;

        font-weight:
            700;

        margin-top:
            3px;
    }


    /* =====================================================
           INFORMATION
        ====================================================== */

    .info-icon {

        color:
            #7c3aed;

        font-size:
            20px;
    }


    .info-label {

        color:
            #9ca3af;

        font-size:
            10px;

        font-weight:
            700;

        text-transform:
            uppercase;

        letter-spacing:
            0.06em;
    }


    .info-value {

        color:
            #374151;

        font-size:
            13px;

        font-weight:
            600;

        margin-top:
            2px;
    }


    /* =====================================================
           DIVIDER
        ====================================================== */

    .divider {

        border-top:
            1px solid #f3f4f6;
    }


    /* =====================================================
           NO RESULTS
        ====================================================== */

    .no-results-icon {

        color:
            #c4b5fd;

        font-size:
            64px;
    }


    /* =====================================================
           RESPONSIVE
        ====================================================== */

    @media (max-width: 768px) {

        .page-title {
            font-size:
                28px;
        }


        .page-header {
            padding:
                20px;
        }


        .member-count {
            width:
                100%;
        }


        .dashboard-btn {
            width:
                100%;
        }

    }


    @media (max-width: 480px) {

        .page-title {
            font-size:
                24px;
        }


        .page-description {
            font-size:
                13px;
        }

    }
    </style>

</head>


<body class="min-h-screen">


    <main class="page-container px-5 md:px-8 py-8 md:py-10">


        <!-- =====================================================
         HEADER
    ====================================================== -->

        <div class="page-header mb-8">

            <div class="flex flex-col lg:flex-row
                    lg:items-center
                    lg:justify-between
                    gap-6">


                <!-- LEFT -->

                <div>

                    <p class="club-label mb-2">

                        MTU BADMINTON CLUB

                    </p>


                    <h1 class="page-title">

                        Our Members

                    </h1>


                    <p class="page-description">

                        Meet the registered members of our badminton community.

                    </p>

                </div>


                <!-- RIGHT -->

                <div class="flex flex-col sm:flex-row
                        items-stretch
                        sm:items-center
                        gap-3">


                    <!-- MEMBER COUNT -->

                    <div class="member-count">

                        <p class="member-count-label">

                            Total Members

                        </p>


                        <p class="member-count-number">

                            <?= count($members) ?>

                        </p>

                    </div>


                    <!-- DASHBOARD BUTTON -->

                    <a href="../member/dashboard.php" class="dashboard-btn">

                        <span class="material-symbols-outlined">
                            dashboard
                        </span>

                        Dashboard

                    </a>

                </div>

            </div>

        </div>



        <!-- =====================================================
         SEARCH
    ====================================================== -->

        <div class="mb-8">

            <div class="search-wrapper">

                <span class="material-symbols-outlined search-icon">

                    search

                </span>


                <input type="text" id="searchInput" class="search-input"
                    placeholder="Search by name, student ID or department..." autocomplete="off">

            </div>

        </div>



        <?php if (count($members) > 0): ?>


        <!-- =====================================================
         MEMBER GRID
    ====================================================== -->

        <div id="memberGrid" class="grid
               grid-cols-1
               sm:grid-cols-2
               lg:grid-cols-3
               xl:grid-cols-4
               gap-6">


            <?php foreach ($members as $member): ?>


            <?php

            /**
             * PROFILE IMAGE
             */

            if (!empty($member['profile_picture'])) {

                $profileImage =
                    "../" . $member['profile_picture'];

            } else {

                $profileImage = null;

            }


            /**
             * STATUS
             */

            if ($member['status'] === 'Approved') {

                $statusClass =
                    "status-approved";

            } elseif ($member['status'] === 'Rejected') {

                $statusClass =
                    "status-rejected";

            } else {

                $statusClass =
                    "status-pending";

            }

        ?>


            <!-- =================================================
             MEMBER CARD
        ================================================== -->

            <div class="member-card member-item" data-search="<?= e(
                $member['username'] . ' ' .
                $member['student_id'] . ' ' .
                $member['department'] . ' ' .
                $member['roll_number']
            ) ?>">


                <!-- CARD TOP -->

                <div class="card-top">


                    <!-- STATUS -->

                    <span class="absolute
                           top-4
                           right-4
                           px-3
                           py-1
                           rounded-full
                           text-xs
                           font-bold
                           <?= $statusClass ?>">

                        <?= e($member['status']) ?>

                    </span>

                </div>



                <!-- CARD CONTENT -->

                <div class="px-5 pb-5">


                    <!-- PROFILE -->

                    <div class="-mt-14 mb-4 flex justify-center">


                        <?php if ($profileImage): ?>


                        <img src="<?= e($profileImage) ?>" alt="<?= e($member['username']) ?>" class="profile-image">


                        <?php else: ?>


                        <div class="profile-placeholder">

                            <span class="material-symbols-outlined">

                                person

                            </span>

                        </div>


                        <?php endif; ?>


                    </div>



                    <!-- NAME -->

                    <div class="text-center">

                        <h3 class="member-name">

                            <?= e($member['username']) ?>

                        </h3>


                        <p class="student-id">

                            <?= e($member['student_id']) ?>

                        </p>

                    </div>



                    <!-- DIVIDER -->

                    <div class="divider my-5"></div>



                    <!-- =================================================
                     MEMBER INFORMATION
                ================================================== -->

                    <div class="space-y-3">


                        <!-- DEPARTMENT -->

                        <div class="flex items-start gap-3">

                            <span class="material-symbols-outlined info-icon">

                                school

                            </span>


                            <div class="min-w-0">

                                <p class="info-label">

                                    Department

                                </p>


                                <p class="info-value">

                                    <?= e($member['department']) ?>

                                </p>

                            </div>

                        </div>



                        <!-- ACADEMIC YEAR -->

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined info-icon">

                                calendar_month

                            </span>


                            <div>

                                <p class="info-label">

                                    Academic Year

                                </p>


                                <p class="info-value">

                                    <?= e($member['academic_year']) ?>

                                </p>

                            </div>

                        </div>



                        <!-- ROLL NUMBER -->

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined info-icon">

                                badge

                            </span>


                            <div>

                                <p class="info-label">

                                    Roll Number

                                </p>


                                <p class="info-value">

                                    <?= e($member['roll_number']) ?>

                                </p>

                            </div>

                        </div>



                        <!-- EMAIL -->

                        <div class="flex items-start gap-3">

                            <span class="material-symbols-outlined info-icon">

                                mail

                            </span>


                            <div class="min-w-0">

                                <p class="info-label">

                                    Email

                                </p>


                                <p class="info-value truncate" title="<?= e($member['email']) ?>">

                                    <?= e($member['email']) ?>

                                </p>

                            </div>

                        </div>


                    </div>



                    <!-- =================================================
                     FOOTER
                ================================================== -->

                    <div class="mt-5
                           pt-4
                           divider
                           flex
                           items-center
                           justify-between">

                        <span class="text-xs text-gray-400">

                            Joined

                        </span>


                        <span class="text-xs
                                 font-bold
                                 text-gray-700">

                            <?= date(
                            "d M Y",
                            strtotime($member['created_at'])
                        ) ?>

                        </span>

                    </div>


                </div>

            </div>


            <?php endforeach; ?>


        </div>



        <!-- =====================================================
         NO SEARCH RESULTS
    ====================================================== -->

        <div id="noResults" class="hidden text-center py-16">

            <span class="material-symbols-outlined no-results-icon">

                search_off

            </span>


            <h3 class="text-xl
                   font-bold
                   text-gray-700
                   mt-4">

                No members found

            </h3>


            <p class="text-gray-500 mt-1">

                Try another name, student ID or department.

            </p>

        </div>


        <?php else: ?>


        <!-- =====================================================
         NO MEMBERS
    ====================================================== -->

        <div class="bg-white
               rounded-2xl
               border
               border-gray-200
               text-center
               py-20
               shadow-sm">

            <span class="material-symbols-outlined
                   text-6xl
                   text-purple-300">

                groups

            </span>


            <h3 class="text-xl
                   font-bold
                   text-gray-700
                   mt-4">

                No Members Yet

            </h3>


            <p class="text-gray-500 mt-2">

                Registered members will appear here.

            </p>


            <!-- DASHBOARD BUTTON -->

            <a href="../member/dashboard.php" class="dashboard-btn mt-6 inline-flex">

                <span class="material-symbols-outlined">

                    dashboard

                </span>

                Back to Dashboard

            </a>

        </div>


        <?php endif; ?>


    </main>



    <!-- =========================================================
     SEARCH JAVASCRIPT
========================================================== -->

    <script>
    const searchInput =
        document.getElementById("searchInput");

    const memberItems =
        document.querySelectorAll(".member-item");

    const noResults =
        document.getElementById("noResults");


    if (searchInput) {

        searchInput.addEventListener(
            "input",
            function() {

                const search =
                    this.value
                    .toLowerCase()
                    .trim();

                let visibleCount = 0;


                memberItems.forEach(
                    function(card) {

                        const text =
                            card.dataset.search
                            .toLowerCase();


                        if (text.includes(search)) {

                            card.style.display =
                                "";

                            visibleCount++;

                        } else {

                            card.style.display =
                                "none";

                        }

                    }
                );


                if (noResults) {

                    if (visibleCount === 0) {

                        noResults.classList.remove(
                            "hidden"
                        );

                    } else {

                        noResults.classList.add(
                            "hidden"
                        );

                    }

                }

            }
        );

    }
    </script>


</body>

</html>