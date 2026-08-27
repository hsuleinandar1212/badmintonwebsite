<?php

/*
|--------------------------------------------------------------------------
| Announcement Page
| MTU Badminton Club
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";


/*
|--------------------------------------------------------------------------
| GET PUBLISHED NEWS
|--------------------------------------------------------------------------
*/

$news = [];

$sql = "
    SELECT
        id,
        title,
        content,
        image,
        created_at
    FROM news
    WHERE status = 'Published'
    ORDER BY created_at DESC
";

$result = false;

if (isset($pdo)) {
    $result = $pdo->query($sql);
}

if ($result) {

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

        $news[] = $row;

    }

}

/*
|--------------------------------------------------------------------------
| COMMON HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../includes/header.php";

?>

<style>
/* =========================================================
   RESET
========================================================= */

.announcement-page,
.announcement-page * {
    box-sizing: border-box;
}


/* =========================================================
   ROOT COLORS
========================================================= */

.announcement-page {

    --orange: #fb8500;
    --yellow: #ffb703;
    --skyblue: #87ceeb;

    --white: #ffffff;

    --text-dark: #1a252c;
    --text-muted: #546e7a;

    --light-blue: #eef9ff;

    min-height: 100vh;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: var(--text-dark);

    position: relative;

    overflow: hidden;

    padding-bottom: 60px;
}


/* =========================================================
   BACKGROUND
========================================================= */

.announcement-page .bg {

    position: fixed;

    inset: 0;

    z-index: -10;

    pointer-events: none;

    background:

        radial-gradient(circle at 15% 20%,
            rgba(135, 206, 235, .45),
            transparent 45%),

        radial-gradient(circle at 85% 75%,
            rgba(255, 183, 3, .25),
            transparent 45%),

        linear-gradient(135deg,
            #e0f2fe 0%,
            #ffffff 50%,
            #fff7ed 100%);

    background-attachment: fixed;
}


.announcement-page .glow {

    position: absolute;

    border-radius: 50%;

    filter: blur(85px);

    opacity: .4;

    animation:
        announcementFloat 10s ease-in-out infinite alternate;
}


.announcement-page .g1 {

    width: 420px;

    height: 420px;

    background:
        var(--skyblue);

    left: -170px;

    top: -180px;
}


.announcement-page .g2 {

    width: 450px;

    height: 450px;

    background:
        var(--orange);

    right: -180px;

    bottom: -180px;

    animation-delay: 3s;
}


.announcement-page .g3 {

    width: 240px;

    height: 240px;

    background:
        var(--yellow);

    left: 45%;

    top: 35%;

    animation-delay: 5s;
}


@keyframes announcementFloat {

    0% {

        transform:
            translate(0, 0) scale(1);

    }

    50% {

        transform:
            translate(50px, -30px) scale(1.12);

    }

    100% {

        transform:
            translate(-30px, 40px) scale(.9);

    }

}


/* =========================================================
   PAGE HEADER
========================================================= */

.announcement-page .page-header {

    text-align: center;

    padding:
        90px 20px 35px;

    position: relative;

    z-index: 2;
}


.announcement-page .page-header small {

    display: block;

    color:
        var(--orange);

    letter-spacing: 4px;

    font-weight: 800;

    font-size: 12px;
}


.announcement-page .page-header h1 {

    font-size:
        clamp(42px, 6vw, 72px);

    line-height: 1;

    margin-top: 15px;

    font-weight: 900;

    letter-spacing: 2px;
}


/*
|--------------------------------------------------------------------------
| Gradient Text
|--------------------------------------------------------------------------
*/

.announcement-page .page-header h1 span {

    background:
        linear-gradient(90deg,
            var(--orange),
            var(--yellow),
            var(--skyblue));

    background-size:
        200% auto;

    /*
    |----------------------------------------------------------
    | Standard property
    |----------------------------------------------------------
    */

    background-clip: text;

    color: transparent;

    /*
    |----------------------------------------------------------
    | Browser compatibility
    |----------------------------------------------------------
    */

    -webkit-background-clip: text;

    -webkit-text-fill-color: transparent;

    animation:
        gradientMove 4s linear infinite;
}


@keyframes gradientMove {

    0% {

        background-position:
            0% center;

    }

    100% {

        background-position:
            200% center;

    }

}


.announcement-page .page-header p {

    max-width: 680px;

    margin:
        20px auto 0;

    color:
        var(--text-muted);

    line-height: 1.8;

    font-size: 14px;
}


/* =========================================================
   DECORATIVE LINE
========================================================= */

.announcement-page .header-line {

    width: 110px;

    height: 4px;

    margin:
        20px auto;

    border-radius: 20px;

    background:
        linear-gradient(90deg,
            var(--yellow),
            var(--orange));

    box-shadow:
        0 0 15px rgba(251, 133, 0, .4);
}


/* =========================================================
   NEWS CONTAINER
========================================================= */

.announcement-page .news-container {

    width:
        min(1100px, 92%);

    margin:
        10px auto 70px;

    display:
        grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap:
        28px;

    position:
        relative;

    z-index:
        2;
}


/* =========================================================
   NEWS CARD
========================================================= */

.announcement-page .news-card {

    background:
        rgba(255, 255, 255, .90);

    backdrop-filter:
        blur(15px);

    -webkit-backdrop-filter:
        blur(15px);

    border:
        2px solid rgba(255, 183, 3, .35);

    border-radius:
        25px;

    overflow:
        hidden;

    box-shadow:
        0 10px 35px rgba(251, 133, 0, .08);

    transition:
        transform .4s ease,
        box-shadow .4s ease,
        border-color .4s ease;

    animation:
        newsEnter .7s ease both;
}


.announcement-page .news-card:hover {

    transform:
        translateY(-10px);

    border-color:
        var(--orange);

    box-shadow:
        0 20px 45px rgba(251, 133, 0, .18);
}


@keyframes newsEnter {

    from {

        opacity: 0;

        transform:
            translateY(35px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/* =========================================================
   NEWS IMAGE
========================================================= */

.announcement-page .news-image {

    width: 100%;

    height: 230px;

    background:
        linear-gradient(135deg,
            #e0f2fe,
            #fff7ed);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    overflow:
        hidden;

    position:
        relative;
}


.announcement-page .news-image::after {

    content: "";

    position:
        absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 80px;

    background:
        linear-gradient(to top,
            rgba(255, 255, 255, .35),
            transparent);

    pointer-events:
        none;
}


.announcement-page .news-image img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

    display:
        block;

    transition:
        transform .6s ease;
}


.announcement-page .news-card:hover .news-image img {

    transform:
        scale(1.07);
}


/* =========================================================
   PLACEHOLDER
========================================================= */

.announcement-page .news-placeholder {

    width:
        75px;

    height:
        75px;

    border-radius:
        50%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-size:
        34px;

    background:
        rgba(255, 255, 255, .8);

    box-shadow:
        0 8px 25px rgba(251, 133, 0, .12);
}


/* =========================================================
   NEWS BODY
========================================================= */

.announcement-page .news-body {

    padding:
        25px;
}


.announcement-page .news-date {

    display:
        inline-block;

    color:
        var(--orange);

    font-size:
        11px;

    font-weight:
        800;

    letter-spacing:
        1px;

    margin-bottom:
        10px;
}


.announcement-page .news-body h2 {

    font-size:
        22px;

    line-height:
        1.3;

    margin-bottom:
        12px;

    color:
        var(--text-dark);

    font-weight:
        800;
}


.announcement-page .news-body p {

    color:
        var(--text-muted);

    font-size:
        14px;

    line-height:
        1.8;

    overflow:
        hidden;

    /*
    |----------------------------------------------------------
    | Standard line clamp
    |----------------------------------------------------------
    */

    display:
        -webkit-box;

    line-clamp:
        4;

    -webkit-line-clamp:
        4;

    -webkit-box-orient:
        vertical;
}


/* =========================================================
   READ MORE
========================================================= */

.announcement-page .read-more {

    display:
        inline-block;

    margin-top:
        18px;

    padding:
        10px 18px;

    border-radius:
        22px;

    background:
        linear-gradient(135deg,
            var(--yellow),
            var(--orange));

    color:
        white;

    text-decoration:
        none;

    font-size:
        11px;

    font-weight:
        900;

    letter-spacing:
        .5px;

    transition:
        transform .3s ease,
        box-shadow .3s ease;
}


.announcement-page .read-more:hover {

    transform:
        translateY(-3px) scale(1.03);

    color:
        white;

    box-shadow:
        0 8px 22px rgba(251, 133, 0, .35);
}


/* =========================================================
   EMPTY NEWS
========================================================= */

.announcement-page .no-news {

    grid-column:
        1 / -1;

    text-align:
        center;

    background:
        rgba(255, 255, 255, .9);

    border:
        2px dashed var(--yellow);

    border-radius:
        25px;

    padding:
        60px 25px;

    box-shadow:
        0 10px 30px rgba(251, 133, 0, .08);
}


.announcement-page .empty-icon {

    font-size:
        55px;

    margin-bottom:
        15px;
}


.announcement-page .no-news h2 {

    color:
        var(--text-dark);

    margin-bottom:
        10px;
}


.announcement-page .no-news p {

    color:
        var(--text-muted);

    font-size:
        14px;
}


/* =========================================================
   FOOTER
========================================================= */

.announcement-page .announcement-footer {

    text-align:
        center;

    padding:
        28px 20px;

    color:
        var(--text-muted);

    font-size:
        11px;

    font-weight:
        600;

    border-top:
        1px solid rgba(255, 183, 3, .3);

    background:
        rgba(255, 255, 255, .65);

    backdrop-filter:
        blur(15px);

    -webkit-backdrop-filter:
        blur(15px);
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 850px) {

    .announcement-page .news-container {

        grid-template-columns:
            1fr;

        width:
            min(650px, 92%);
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .announcement-page .page-header {

        padding:
            50px 18px 30px;
    }


    .announcement-page .page-header h1 {

        font-size:
            40px;

        line-height:
            1.15;
    }


    .announcement-page .page-header p {

        font-size:
            13px;
    }


    .announcement-page .news-container {

        width:
            92%;

        gap:
            20px;

        margin-bottom:
            45px;
    }


    .announcement-page .news-image {

        height:
            200px;
    }


    .announcement-page .news-body {

        padding:
            20px;
    }


    .announcement-page .news-body h2 {

        font-size:
            20px;
    }


    .announcement-page .news-body p {

        font-size:
            13px;
    }

}


/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {

    .announcement-page *,
    .announcement-page *::before,
    .announcement-page *::after {

        animation-duration:
            0.01ms !important;

        animation-iteration-count:
            1 !important;

        transition-duration:
            0.01ms !important;
    }

}
</style>


<div class="announcement-page">


    <!-- =====================================================
         BACKGROUND
    ===================================================== -->

    <div class="bg">

        <div class="glow g1"></div>

        <div class="glow g2"></div>

        <div class="glow g3"></div>

    </div>


    <!-- =====================================================
         PAGE HEADER
    ===================================================== -->

    <section class="page-header">

        <small>
            MANDALAY TECHNOLOGICAL UNIVERSITY
        </small>

        <h1>

            CLUB

            <span>
                ANNOUNCEMENTS
            </span>

        </h1>

        <div class="header-line"></div>

        <p>

            Stay updated with the latest news,
            events, training information and important
            announcements from the MTU Badminton Club.

        </p>

    </section>


    <!-- =====================================================
         NEWS LIST
    ===================================================== -->

    <section class="news-container">


        <?php if (empty($news)): ?>


        <!-- EMPTY STATE -->

        <div class="no-news">

            <div class="empty-icon">
                📢
            </div>

            <h2>
                No Announcements Yet
            </h2>

            <p>
                There are currently no published announcements.
                Please check back later.
            </p>

        </div>


        <?php else: ?>


        <?php foreach ($news as $item): ?>


        <article class="news-card">


            <!-- =====================================
                         NEWS IMAGE
                    ====================================== -->

            <div class="news-image">

                <?php

                        /*
                        |--------------------------------------------------------------------------
                        | IMAGE FILENAME
                        |--------------------------------------------------------------------------
                        */

                        $image =
                            trim(
                                $item['image'] ?? ''
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | PHYSICAL FILE PATH
                        |--------------------------------------------------------------------------
                        |
                        | announcement.php is inside /public/
                        |
                        | ../uploads/news/
                        | points to:
                        |
                        | mut-badminton-system/uploads/news/
                        |
                        */

                        $imageFile =
                            __DIR__ .
                            "/../uploads/news/" .
                            $image;


                        /*
                        |--------------------------------------------------------------------------
                        | BROWSER IMAGE URL
                        |--------------------------------------------------------------------------
                        */

                        $imageUrl =
                            "../uploads/news/" .
                            rawurlencode($image);


                        /*
                        |--------------------------------------------------------------------------
                        | CHECK IMAGE
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $image !== '' &&
                            file_exists($imageFile)
                        ):

                        ?>

                <img src="<?= htmlspecialchars(
                                    $imageUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>" alt="<?= htmlspecialchars(
                                    $item['title'] ?? 'News Image',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>" loading="lazy">

                <?php else: ?>

                <div class="news-placeholder">
                    📢
                </div>

                <?php endif; ?>

            </div>


            <!-- =====================================
                         NEWS BODY
                    ====================================== -->

            <div class="news-body">


                <!-- DATE -->

                <span class="news-date">

                    <?= date(
                                "F d, Y",
                                strtotime(
                                    $item['created_at']
                                )
                            ) ?>

                </span>


                <!-- TITLE -->

                <h2>

                    <?= htmlspecialchars(
                                $item['title'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                </h2>


                <!-- CONTENT -->

                <p>

                    <?= nl2br(
                                htmlspecialchars(
                                    $item['content'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>

                </p>


            </div>


        </article>


        <?php endforeach; ?>


        <?php endif; ?>


    </section>
</div>


<script>
/*
|--------------------------------------------------------------------------
| MOBILE NAVIGATION
|--------------------------------------------------------------------------
*/

const navToggle =
    document.getElementById("navToggle");

const navMenu =
    document.getElementById("navMenu");


if (navToggle && navMenu) {

    navToggle.addEventListener(
        "click",
        function() {

            navMenu.classList.toggle(
                "active"
            );

            navToggle.textContent =
                navMenu.classList.contains("active") ?
                "✕" :
                "☰";

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLOSE MENU AFTER CLICKING LINK
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll("#navMenu a")
        .forEach(function(link) {

            link.addEventListener(
                "click",
                function() {

                    navMenu.classList.remove(
                        "active"
                    );

                    navToggle.textContent =
                        "☰";

                }
            );

        });

}
</script>

<?php

/*
|--------------------------------------------------------------------------
| CLOSE DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

if (isset($conn)) {

    $conn->close();

}

?>