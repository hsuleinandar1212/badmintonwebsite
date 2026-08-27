<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";


function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


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

    <title>Members | MTU Badminton Club</title>


    <!-- Tailwind CSS -->

    <script src="https://cdn.tailwindcss.com"></script>


    <!-- Google Fonts -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap"
        rel="stylesheet">


    <!-- Material Symbols -->

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
        rel="stylesheet">


    <style>
    body {
        font-family: 'Inter', sans-serif;
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

    .member-card {
        transition: all 0.25s ease;
    }

    .member-card:hover {
        transform: translateY(-5px);
    }
    </style>

</head>


<body class="bg-slate-50 min-h-screen">

    <main class="max-w-7xl mx-auto px-6 py-10">

        <div class="flex flex-col md:flex-row
                md:items-end
                md:justify-between
                gap-5
                mb-8">


            <div>

                <p class="text-blue-700
                      font-semibold
                      text-sm
                      uppercase
                      tracking-wider
                      mb-2">

                    MTU BADMINTON CLUB

                </p>

                <h2 class="text-3xl md:text-4xl
                       font-bold
                       text-slate-900">

                    Our Members

                </h2>

                <p class="text-slate-500 mt-2">

                    Meet the registered members of our badminton community.

                </p>

            </div>


            <!-- MEMBER COUNT -->

            <div class="bg-white
                    border
                    border-slate-200
                    rounded-2xl
                    px-6 py-4
                    shadow-sm">

                <p class="text-xs
                      text-slate-500
                      uppercase
                      tracking-wider
                      font-semibold">

                    Total Members

                </p>

                <p class="text-3xl
                      font-bold
                      text-blue-700">

                    <?= count($members) ?>

                </p>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- SEARCH -->
        <!-- ================================================= -->

        <div class="mb-8">

            <div class="relative max-w-lg">

                <span class="material-symbols-outlined
                         absolute
                         left-4
                         top-1/2
                         -translate-y-1/2
                         text-slate-400">

                    search

                </span>

                <input type="text" id="searchInput" placeholder="Search by name, student ID or department..." class="w-full
                       bg-white
                       border
                       border-slate-200
                       rounded-xl
                       pl-12
                       pr-4
                       py-3.5
                       outline-none
                       focus:ring-2
                       focus:ring-blue-600
                       shadow-sm">

            </div>

        </div>



        <?php if (count($members) > 0): ?>

        <div id="memberGrid" class="grid
                    grid-cols-1
                    sm:grid-cols-2
                    lg:grid-cols-3
                    xl:grid-cols-4
                    gap-6">


            <?php foreach ($members as $member): ?>


            <?php

               

                if (!empty($member['profile_picture'])) {

                    $profileImage =
                        "../" . $member['profile_picture'];

                } else {

                    $profileImage = null;

                }

                if ($member['status'] === 'Approved') {

                    $statusClass =
                        "bg-green-100 text-green-700";

                } elseif ($member['status'] === 'Rejected') {

                    $statusClass =
                        "bg-red-100 text-red-700";

                } else {

                    $statusClass =
                        "bg-yellow-100 text-yellow-700";

                }

                ?>


            <div class="member-card
                           member-item
                           bg-white
                           rounded-2xl
                           border
                           border-slate-200
                           shadow-sm
                           overflow-hidden" data-search="
                        <?= e(
                            $member['username'] . ' ' .
                            $member['student_id'] . ' ' .
                            $member['department'] . ' ' .
                            $member['roll_number']
                        ) ?>">


                <!-- CARD TOP -->

                <div class="h-24
                                bg-gradient-to-br
                                from-blue-700
                                to-blue-500
                                relative">


                    <!-- STATUS -->

                    <span class="absolute
                                   top-4
                                   right-4
                                   px-3 py-1
                                   rounded-full
                                   text-xs
                                   font-semibold
                                   <?= $statusClass ?>">

                        <?= e($member['status']) ?>

                    </span>

                </div>


                <!-- PROFILE -->

                <div class="px-5 pb-5">


                    <div class="-mt-14 mb-4">


                        <?php if ($profileImage): ?>

                        <img src="<?= e($profileImage) ?>" alt="<?= e($member['username']) ?>" class="w-28 h-28
                                           rounded-full
                                           object-cover
                                           border-4
                                           border-white
                                           shadow-lg
                                           mx-auto">

                        <?php else: ?>

                        <div class="w-28 h-28
                                           rounded-full
                                           border-4
                                           border-white
                                           shadow-lg
                                           mx-auto
                                           bg-blue-100
                                           flex items-center
                                           justify-center">

                            <span class="material-symbols-outlined
                                               text-blue-700
                                               text-5xl">

                                person

                            </span>

                        </div>

                        <?php endif; ?>

                    </div>


                    <!-- NAME -->

                    <div class="text-center">

                        <h3 class="text-lg
                                       font-bold
                                       text-slate-900">

                            <?= e($member['username']) ?>

                        </h3>

                        <p class="text-sm
                                      text-blue-700
                                      font-medium
                                      mt-1">

                            <?= e($member['student_id']) ?>

                        </p>

                    </div>


                    <!-- DIVIDER -->

                    <div class="border-t
                                    border-slate-100
                                    my-5">
                    </div>


                    <!-- MEMBER INFORMATION -->

                    <div class="space-y-3">


                        <!-- DEPARTMENT -->

                        <div class="flex items-start gap-3">

                            <span class="material-symbols-outlined
                                           text-blue-600
                                           text-[20px]">

                                school

                            </span>

                            <div>

                                <p class="text-[11px]
                                              text-slate-400
                                              uppercase
                                              font-semibold">

                                    Department

                                </p>

                                <p class="text-sm
                                              text-slate-700
                                              font-medium">

                                    <?= e($member['department']) ?>

                                </p>

                            </div>

                        </div>


                        <!-- ACADEMIC YEAR -->

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined
                                           text-blue-600
                                           text-[20px]">

                                calendar_month

                            </span>

                            <div>

                                <p class="text-[11px]
                                              text-slate-400
                                              uppercase
                                              font-semibold">

                                    Academic Year

                                </p>

                                <p class="text-sm
                                              text-slate-700
                                              font-medium">

                                    <?= e($member['academic_year']) ?>

                                </p>

                            </div>

                        </div>


                        <!-- ROLL NUMBER -->

                        <div class="flex items-center gap-3">

                            <span class="material-symbols-outlined
                                           text-blue-600
                                           text-[20px]">

                                badge

                            </span>

                            <div>

                                <p class="text-[11px]
                                              text-slate-400
                                              uppercase
                                              font-semibold">

                                    Roll Number

                                </p>

                                <p class="text-sm
                                              text-slate-700
                                              font-medium">

                                    <?= e($member['roll_number']) ?>

                                </p>

                            </div>

                        </div>


                        <!-- EMAIL -->

                        <div class="flex items-start gap-3">

                            <span class="material-symbols-outlined
                                           text-blue-600
                                           text-[20px]">

                                mail

                            </span>

                            <div class="min-w-0">

                                <p class="text-[11px]
                                              text-slate-400
                                              uppercase
                                              font-semibold">

                                    Email

                                </p>

                                <p class="text-sm
                                              text-slate-700
                                              truncate">

                                    <?= e($member['email']) ?>

                                </p>

                            </div>

                        </div>


                    </div>


                    <!-- FOOTER -->

                    <div class="mt-5
                                    pt-4
                                    border-t
                                    border-slate-100
                                    flex
                                    items-center
                                    justify-between">

                        <span class="text-xs
                                         text-slate-400">

                            Joined

                        </span>

                        <span class="text-xs
                                         font-semibold
                                         text-slate-600">

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


        <!-- NO SEARCH RESULT -->

        <div id="noResults" class="hidden
                    text-center
                    py-16">

            <span class="material-symbols-outlined
                         text-6xl
                         text-slate-300">

                search_off

            </span>

            <h3 class="text-xl
                       font-bold
                       text-slate-700
                       mt-4">

                No members found

            </h3>

            <p class="text-slate-500 mt-1">

                Try another name, student ID or department.

            </p>

        </div>


        <?php else: ?>


        <!-- NO MEMBERS -->

        <div class="bg-white
                    rounded-2xl
                    border
                    border-slate-200
                    text-center
                    py-20">

            <span class="material-symbols-outlined
                         text-6xl
                         text-slate-300">

                groups

            </span>

            <h3 class="text-xl
                       font-bold
                       text-slate-700
                       mt-4">

                No Members Yet

            </h3>

            <p class="text-slate-500 mt-2">

                Registered members will appear here.

            </p>

        </div>


        <?php endif; ?>


    </main>


    <!-- ===================================================== -->
    <!-- SEARCH -->
    <!-- ===================================================== -->

    <script>
    const searchInput =
        document.getElementById("searchInput");

    const memberItems =
        document.querySelectorAll(".member-item");

    const noResults =
        document.getElementById("noResults");


    searchInput.addEventListener("input", function() {

        const search =
            this.value.toLowerCase().trim();

        let visibleCount = 0;


        memberItems.forEach(function(card) {

            const text =
                card.dataset.search.toLowerCase();

            if (text.includes(search)) {

                card.style.display = "";

                visibleCount++;

            } else {

                card.style.display = "none";

            }

        });


        if (visibleCount === 0) {

            noResults.classList.remove("hidden");

        } else {

            noResults.classList.add("hidden");

        }

    });
    </script>


</body>

</html>