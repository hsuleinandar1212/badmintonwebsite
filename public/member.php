<?php

/**
 * =========================================================
 * MTU BADMINTON CLUB
 * PROTECTED MEMBERS PAGE
 * =========================================================
 */

require_once __DIR__ . "/../includes/auth.php";

/**
 * Only authenticated members can view this page.
 */
require_member();

/**
 * Database connection.
 */
require_once __DIR__ . "/../config/db.php";

/**
 * Website header.
 */
require_once __DIR__ . "/../includes/header.php";


/**
 * =========================================================
 * PAGE TITLE
 * =========================================================
 */

$pageTitle = "MTU Badminton Club | Members";
/*
|--------------------------------------------------------------------------
| GET APPROVED MEMBERS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        username,
        student_id,
        roll_number,
        department,
        academic_year,
        gender,
        phone,
        profile_picture
    FROM members
    WHERE status = 'Approved'
    ORDER BY id DESC
";

$result = $pdo->query($sql);

if (!$result) {
    $error = $pdo->errorInfo();
    die("Database error: " . ($error[2] ?? 'Unknown database error'));
}


/*
|--------------------------------------------------------------------------
| PROFILE IMAGE HELPER
|--------------------------------------------------------------------------
|
| This function checks several possible upload locations.
|
*/

function getProfileImage($profilePicture)
{
    $profilePicture = trim((string)$profilePicture);

    /*
    |--------------------------------------------------------------------------
    | DEFAULT IMAGE
    |--------------------------------------------------------------------------
    */

    $defaultImage = "../assets/images/default-profile.png";

    if ($profilePicture === "") {
        return $defaultImage;
    }


    /*
    |--------------------------------------------------------------------------
    | Remove unwanted leading slashes
    |--------------------------------------------------------------------------
    */

    $cleanName = ltrim($profilePicture, "/\\");


    /*
    |--------------------------------------------------------------------------
    | Possible locations
    |--------------------------------------------------------------------------
    */

    $possibleFiles = [

        /*
        | uploads/profile/
        */
        [
            "file" => __DIR__ . "/../uploads/profile/" . basename($cleanName),
            "url"  => "../uploads/profile/" . rawurlencode(basename($cleanName))
        ],

        /*
        | uploads/
        */
        [
            "file" => __DIR__ . "/../uploads/" . basename($cleanName),
            "url"  => "../uploads/" . rawurlencode(basename($cleanName))
        ],

        /*
        | assets/uploads/
        */
        [
            "file" => __DIR__ . "/../assets/uploads/" . basename($cleanName),
            "url"  => "../assets/uploads/" . rawurlencode(basename($cleanName))
        ]
    ];


    /*
    |--------------------------------------------------------------------------
    | If database already contains uploads/profile/filename.jpg
    |--------------------------------------------------------------------------
    */

    if (
        strpos($cleanName, "uploads/profile/") === 0 ||
        strpos($cleanName, "uploads\\profile\\") === 0
    ) {

        $relative = str_replace("\\", "/", $cleanName);

        $file = __DIR__ . "/../" . $relative;

        if (file_exists($file)) {

            return "../" . implode(
                "/",
                array_map(
                    "rawurlencode",
                    explode("/", $relative)
                )
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | If database already contains uploads/filename.jpg
    |--------------------------------------------------------------------------
    */

    if (
        strpos($cleanName, "uploads/") === 0 ||
        strpos($cleanName, "uploads\\") === 0
    ) {

        $relative = str_replace("\\", "/", $cleanName);

        $file = __DIR__ . "/../" . $relative;

        if (file_exists($file)) {

            return "../" . implode(
                "/",
                array_map(
                    "rawurlencode",
                    explode("/", $relative)
                )
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Check normal filename
    |--------------------------------------------------------------------------
    */

    foreach ($possibleFiles as $image) {

        if (file_exists($image["file"])) {

            return $image["url"];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | No image found
    |--------------------------------------------------------------------------
    */

    return $defaultImage;
}

?>

<style>
/* =========================================================
   MEMBER PAGE VARIABLES
========================================================= */

:root {

    --skyblue: #87ceeb;
    --deep-skyblue: #00bfff;
    --light-blue: #e0f7fa;

    --pure-white: #ffffff;

    --card-bg: rgba(255, 255, 255, 0.90);

    --text-dark: #1a252c;
    --text-muted: #546e7a;

    --accent-yellow: #ffb703;
    --accent-orange: #fb8500;

    --soft-orange: rgba(251, 133, 0, 0.15);
    --soft-yellow: rgba(255, 183, 3, 0.20);
}


/* =========================================================
   PAGE RESET
========================================================= */

.member-page,
.member-page * {
    box-sizing: border-box;
}

.member-page {

    min-height: 100vh;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: var(--text-dark);

    overflow: hidden;

    background:
        radial-gradient(circle at 20% 15%,
            rgba(135, 206, 235, 0.45),
            transparent 40%),

        radial-gradient(circle at 80% 25%,
            rgba(255, 183, 3, 0.25),
            transparent 40%),

        radial-gradient(circle at 50% 100%,
            rgba(251, 133, 0, 0.15),
            transparent 50%),

        linear-gradient(135deg,
            #e0f2fe 0%,
            #ffffff 50%,
            #fff7ed 100%);

    position: relative;

    padding-bottom: 50px;
}


/* =========================================================
   BACKGROUND
========================================================= */

.member-background {

    position: fixed;

    inset: 0;

    overflow: hidden;

    pointer-events: none;

    z-index: 0;
}


.member-glow {

    position: absolute;

    border-radius: 50%;

    filter: blur(80px);

    opacity: 0.4;

    animation:
        memberGlowMove 10s infinite alternate ease-in-out;
}


.member-glow.one {

    width: 350px;
    height: 350px;

    background: var(--skyblue);

    top: -100px;
    left: -100px;
}


.member-glow.two {

    width: 400px;
    height: 400px;

    background: var(--accent-orange);

    right: -150px;
    bottom: -100px;

    animation-delay: 2s;
}


.member-glow.three {

    width: 250px;
    height: 250px;

    background: var(--accent-yellow);

    left: 45%;
    top: 40%;

    animation-delay: 4s;
}


@keyframes memberGlowMove {

    0% {
        transform: translate(0, 0) scale(1);
    }

    50% {
        transform: translate(50px, -30px) scale(1.2);
    }

    100% {
        transform: translate(-30px, 40px) scale(.9);
    }
}


/* =========================================================
   PARTICLES
========================================================= */

.member-particle {

    position: absolute;

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: var(--accent-orange);

    box-shadow:
        0 0 12px var(--accent-yellow);

    animation:
        memberParticleMove 8s infinite linear;
}


.member-particle.p1 {
    left: 10%;
    bottom: -20px;
}


.member-particle.p2 {

    left: 30%;
    bottom: -20px;

    animation-delay: 2s;

    background: white;

    box-shadow:
        0 0 12px var(--skyblue);
}


.member-particle.p3 {

    left: 60%;
    bottom: -20px;

    animation-delay: 4s;

    background: var(--skyblue);

    box-shadow:
        0 0 12px var(--skyblue);
}


.member-particle.p4 {

    left: 85%;
    bottom: -20px;

    animation-delay: 6s;

    background: var(--accent-yellow);

    box-shadow:
        0 0 12px var(--accent-orange);
}


@keyframes memberParticleMove {

    0% {

        transform: translateY(0);

        opacity: 0;
    }

    20% {
        opacity: 1;
    }

    100% {

        transform: translateY(-100vh);

        opacity: 0;
    }
}


/* =========================================================
   CONTENT
========================================================= */

.member-content {

    position: relative;

    z-index: 2;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.member-header {

    text-align: center;

    padding: 10px 20px 0;
}


.member-header h1 {

    margin: 80px 0 10px;

    font-size:
        clamp(40px, 6vw, 70px);

    line-height: 1.1;

    font-weight: 900;

    letter-spacing: 4px;

    color: var(--text-dark);

    animation:
        memberTitleAppear 1s ease .2s both;
}


/*
|--------------------------------------------------------------------------
| FIXED: .header -> .member-header
|--------------------------------------------------------------------------
*/

.member-header h1 .members {

    background:
        linear-gradient(90deg,
            var(--accent-orange),
            var(--accent-yellow),
            var(--skyblue));

    background-clip: text;

    -webkit-background-clip: text;

    color: transparent;

    -webkit-text-fill-color: transparent;

    background-size: 200% auto;

    animation:
        memberGradientMove 4s linear infinite;
}


@keyframes memberGradientMove {

    to {

        background-position:
            200% center;
    }
}


@keyframes memberTitleAppear {

    from {

        opacity: 0;

        transform:
            translateY(-35px);
    }

    to {

        opacity: 1;

        transform:
            translateY(0);
    }
}


.member-red-line {

    width: 110px;

    height: 4px;

    margin: 18px auto;

    border-radius: 10px;

    background:
        linear-gradient(90deg,
            var(--accent-yellow),
            var(--accent-orange));

    box-shadow:
        0 0 15px rgba(251, 133, 0, .5);
}


.member-header p {

    margin: 10px 0 0;

    color: var(--text-muted);

    letter-spacing: 3px;

    font-size: 14px;

    font-weight: 600;
}


/* =========================================================
   SLIDER
========================================================= */

.member-slider {

    position: relative;

    width: 100%;

    max-width: 1200px;

    height: 490px;

    margin: 5px auto 0;

    perspective: 1600px;
}


/* =========================================================
   MEMBER CARD
========================================================= */

.member-card {

    position: absolute;

    width: 290px;

    height: 420px;

    left: 50%;

    top: 50%;

    transform-style: preserve-3d;

    transform:
        translate(-50%, -50%) translateX(var(--x)) scale(var(--scale)) rotateY(var(--rotate));

    transition:
        transform .85s cubic-bezier(.22,
            .61,
            .36,
            1),

        opacity .85s ease;

    opacity: var(--opacity);

    z-index: var(--z);

    cursor: pointer;
}


/* =========================================================
   CARD INNER
========================================================= */

.member-card-inner {

    width: 100%;

    height: 100%;

    position: relative;

    overflow: hidden;

    border-radius: 26px;

    background:
        var(--card-bg);

    border:
        2px solid var(--accent-yellow);

    box-shadow:
        0 20px 40px rgba(251, 133, 0, .12),

        0 0 20px rgba(255, 183, 3, .25);

    backdrop-filter: blur(12px);

    transition: .5s;
}


.member-card-inner::before {

    content: "";

    position: absolute;

    top: 0;

    left: -100%;

    width: 100%;

    height: 4px;

    background:
        linear-gradient(90deg,
            var(--accent-yellow),
            var(--accent-orange),
            white);

    z-index: 10;

    transition: .7s;
}


.member-card:hover .member-card-inner::before {

    left: 100%;
}


.member-card:hover .member-card-inner {

    border-color:
        var(--accent-orange);

    box-shadow:
        0 30px 60px rgba(251, 133, 0, .25),

        0 0 30px rgba(255, 183, 3, .5);

    transform:
        translateY(-10px);
}


/* =========================================================
   MEMBER PHOTO
========================================================= */

.member-photo {

    width: 100%;

    height: 245px;

    overflow: hidden;

    position: relative;

    background:
        linear-gradient(135deg,
            #dff6ff,
            #fff4df);
}


.member-photo::after {

    content: "";

    position: absolute;

    inset: 0;

    background:
        linear-gradient(to top,
            rgba(255, 255, 255, 1) 0%,
            rgba(255, 255, 255, .25) 35%,
            transparent 65%);

    pointer-events: none;
}


.member-photo img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    transition:
        .8s cubic-bezier(.22,
            .61,
            .36,
            1);
}


.member-card:hover .member-photo img {

    transform:
        scale(1.1);

    filter:
        saturate(1.15) brightness(1.05);
}


/* =========================================================
   CARD NUMBER
========================================================= */

.member-card-number {

    position: absolute;

    top: 14px;

    right: 17px;

    width: 40px;

    height: 40px;

    border-radius: 50%;

    background:
        linear-gradient(135deg,
            var(--accent-yellow),
            var(--accent-orange));

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 900;

    font-size: 13px;

    z-index: 20;

    box-shadow:
        0 4px 15px rgba(251, 133, 0, .4);
}


/* =========================================================
   MEMBER INFO
========================================================= */

.member-info {

    position: absolute;

    left: 0;

    right: 0;

    bottom: 0;

    padding:
        18px 22px 20px;

    z-index: 5;
}


.member-info h2 {

    font-size: 22px;

    font-weight: 800;

    margin:
        0 0 7px;

    color:
        var(--text-dark);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.member-info h2::after {

    content: "";

    display: block;

    width: 45px;

    height: 3px;

    margin-top: 7px;

    background:
        linear-gradient(90deg,
            var(--accent-yellow),
            var(--accent-orange));

    border-radius: 10px;
}


.member-info p {

    font-size: 12.5px;

    color:
        var(--text-muted);

    margin:
        5px 0 0;

    font-weight: 600;

    line-height: 1.3;
}


.member-info strong {

    color:
        var(--accent-orange);
}


/* =========================================================
   CONTROLS
========================================================= */

.member-controls {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 20px;

    position: relative;

    z-index: 5;
}


.member-control-btn {

    width: 55px;

    height: 55px;

    border-radius: 50%;

    border:
        2px solid var(--accent-yellow);

    background:
        rgba(255, 255, 255, .95);

    color:
        var(--accent-orange);

    font-size: 28px;

    line-height: 1;

    cursor: pointer;

    transition: .35s;

    box-shadow:
        0 4px 15px rgba(251, 133, 0, .15);
}


.member-control-btn:hover {

    transform:
        scale(1.12);

    background:
        linear-gradient(135deg,
            var(--accent-yellow),
            var(--accent-orange));

    color: white;

    border-color:
        var(--accent-orange);

    box-shadow:
        0 0 25px rgba(251, 133, 0, .5);
}


/* =========================================================
   DOTS
========================================================= */

.member-dots {

    display: flex;

    justify-content: center;

    align-items: center;

    gap: 8px;

    margin-top: 20px;

    position: relative;

    z-index: 5;
}


.member-dot {

    width: 10px;

    height: 10px;

    border-radius: 50%;

    background:
        #b0bec5;

    cursor: pointer;

    transition: .35s;
}


.member-dot:hover {

    transform:
        scale(1.2);
}


.member-dot.active {

    width: 30px;

    background:
        linear-gradient(90deg,
            var(--accent-yellow),
            var(--accent-orange));

    box-shadow:
        0 0 12px rgba(251, 133, 0, .5);

    border-radius: 10px;
}


/* =========================================================
   COUNTER
========================================================= */

.member-count {

    text-align: center;

    margin-top: 15px;

    color:
        var(--text-muted);

    font-size: 13px;

    font-weight: 700;

    letter-spacing: 2px;

    position: relative;

    z-index: 5;
}


.member-count span {

    color:
        var(--accent-orange);

    font-weight: 900;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-members {

    width:
        min(90%, 600px);

    margin:
        50px auto;

    padding:
        60px 25px;

    text-align: center;

    background:
        rgba(255, 255, 255, .78);

    border:
        2px solid rgba(255, 183, 3, .6);

    border-radius: 25px;

    backdrop-filter:
        blur(15px);

    box-shadow:
        0 20px 50px rgba(251, 133, 0, .12);
}


.empty-members i {

    display: block;

    font-size: 55px;

    color:
        var(--accent-orange);

    margin-bottom: 20px;
}


.empty-members h2 {

    margin:
        0 0 10px;

    color:
        var(--text-dark);

    font-size: 25px;
}


.empty-members p {

    margin: 0;

    color:
        var(--text-muted);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 800px) {

    .member-page {
        overflow-x: hidden;
    }

    .member-header h1 {

        font-size:
            clamp(34px, 11vw, 52px);

        letter-spacing: 2px;
    }

    .member-header p {

        font-size: 11px;

        letter-spacing: 1.5px;

        padding: 0 10px;
    }

    .member-slider {

        height: 455px;

        margin-top: 5px;
    }

    .member-card {

        width: 260px;

        height: 400px;
    }

    .member-photo {

        height: 225px;
    }

    .member-info {

        padding:
            16px 19px 18px;
    }

    .member-info h2 {

        font-size: 20px;
    }

    .member-info p {

        font-size: 12px;
    }

    .member-controls {

        margin-top: 5px;
    }

    .member-control-btn {

        width: 50px;

        height: 50px;

        font-size: 25px;
    }

    .empty-members {

        padding:
            45px 20px;
    }
}


@media(max-width: 480px) {

    .member-slider {

        height: 430px;
    }

    .member-card {

        width: 245px;

        height: 385px;
    }

    .member-photo {

        height: 215px;
    }

    .member-info {

        padding:
            14px 17px 16px;
    }

    .member-info h2 {

        font-size: 19px;
    }

    .member-info p {

        font-size: 11.5px;
    }

    .member-social-btn {

        height: 34px;

        font-size: 10px;
    }
}
</style>


<div class="member-page">

    <!-- BACKGROUND -->

    <div class="member-background">

        <div class="member-glow one"></div>

        <div class="member-glow two"></div>

        <div class="member-glow three"></div>

        <div class="member-particle p1"></div>

        <div class="member-particle p2"></div>

        <div class="member-particle p3"></div>

        <div class="member-particle p4"></div>

    </div>


    <div class="member-content">


        <!-- =================================================
             HEADER
        ================================================== -->

        <section class="member-header">

            <h1>

                OUR

                <span class="members">
                    MEMBERS
                </span>

            </h1>

            <div class="member-red-line"></div>

            <p>
                MEET THE TEAM BEHIND MTU BADMINTON CLUB
            </p>

        </section>


        <!-- =================================================
             MEMBER SLIDER
        ================================================== -->

        <div class="member-slider" id="memberSlider">

            <?php

            $memberNumber = 1;

            $members = $result->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($members)):

                foreach ($members as $member):

                    /*
                    |--------------------------------------------------------------------------
                    | GET PROFILE IMAGE
                    |--------------------------------------------------------------------------
                    */

                    $profileImage =
                        getProfileImage(
                            $member['profile_picture'] ?? ''
                        );

            ?>

            <div class="member-card">

                <div class="member-card-inner">


                    <!-- CARD NUMBER -->

                    <div class="member-card-number">

                        <?= sprintf(
                            "%02d",
                            $memberNumber
                        ) ?>

                    </div>


                    <!-- PHOTO -->

                    <div class="member-photo">

                        <img src="<?= htmlspecialchars(
                                $profileImage,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" alt="<?= htmlspecialchars(
                                $member['username'] ?? 'Member',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" loading="lazy"
                            onerror="this.onerror=null;this.src='../assets/images/default-profile.png';">

                    </div>


                    <!-- MEMBER INFORMATION -->

                    <div class="member-info">

                        <h2>

                            <?= htmlspecialchars(
                                $member['username'] ?? 'Member',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </h2>



                        <p>

                            <strong>
                                Major:
                            </strong>

                            <?= htmlspecialchars(
                                $member['department'] ?? '-',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Batch:
                            </strong>

                            <?= htmlspecialchars(
                                $member['academic_year'] ?? '-',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>


                    </div>

                </div>

            </div>


            <?php

                    $memberNumber++;

                endforeach;

            else:

            ?>


            <div class="empty-members">

                <i class="fa-solid fa-users"></i>

                <h2>
                    No Approved Members Yet
                </h2>

                <p>
                    Approved badminton club members
                    will appear here.
                </p>

            </div>


            <?php endif; ?>

        </div>


        <?php if (!empty($members)): ?>


        <!-- CONTROLS -->

        <div class="member-controls">

            <button class="member-control-btn" id="memberPrev" type="button" aria-label="Previous member">
                ‹
            </button>


            <button class="member-control-btn" id="memberNext" type="button" aria-label="Next member">
                ›
            </button>

        </div>


        <!-- DOTS -->

        <div class="member-dots" id="memberDots"></div>


        <!-- COUNTER -->

        <div class="member-count">

            MEMBER

            <span id="memberCurrent">
                01
            </span>

            /

            <span id="memberTotal">
                00
            </span>

        </div>


        <?php endif; ?>


    </div>

</div>


<script>
/* =========================================================
   MEMBER SLIDER
========================================================= */

const memberCards =
    document.querySelectorAll(
        ".member-card"
    );

const memberDotsContainer =
    document.getElementById(
        "memberDots"
    );

const memberCurrent =
    document.getElementById(
        "memberCurrent"
    );

const memberTotal =
    document.getElementById(
        "memberTotal"
    );

const memberNext =
    document.getElementById(
        "memberNext"
    );

const memberPrev =
    document.getElementById(
        "memberPrev"
    );

const memberSlider =
    document.getElementById(
        "memberSlider"
    );


let memberIndex = 0;

const memberTotalCards =
    memberCards.length;


/* =========================================================
   TOTAL
========================================================= */

if (memberTotal) {

    memberTotal.textContent =
        String(
            memberTotalCards
        ).padStart(2, "0");

}


/* =========================================================
   CREATE DOTS
========================================================= */

if (memberDotsContainer) {

    memberCards.forEach(
        (card, i) => {

            const dot =
                document.createElement(
                    "div"
                );

            dot.classList.add(
                "member-dot"
            );

            dot.setAttribute(
                "aria-label",
                "Go to member " +
                (i + 1)
            );

            dot.addEventListener(
                "click",
                () => {

                    memberIndex = i;

                    updateMemberSlider();

                    resetMemberAutoSlide();

                }
            );

            memberDotsContainer.appendChild(
                dot
            );

        }
    );

}


const memberDots =
    document.querySelectorAll(
        ".member-dot"
    );


/* =========================================================
   UPDATE SLIDER
========================================================= */

function updateMemberSlider() {

    if (
        memberTotalCards === 0
    ) {
        return;
    }


    memberCards.forEach(
        (card, i) => {

            let position =
                i - memberIndex;


            if (
                position >
                memberTotalCards / 2
            ) {

                position -=
                    memberTotalCards;

            }


            if (
                position <
                -memberTotalCards / 2
            ) {

                position +=
                    memberTotalCards;

            }


            let x =
                position * 330;

            let scale =
                position === 0 ?
                1 :
                .78;

            let opacity =
                Math.abs(position) > 2 ?
                0 :
                position === 0 ?
                1 :
                .55;

            let rotate =
                position * -18;

            let z =
                10 -
                Math.abs(position);


            if (
                position === 0
            ) {

                x = 0;

                scale = 1;

                rotate = 0;

                opacity = 1;

            }


            card.style.setProperty(
                "--x",
                `${x}px`
            );

            card.style.setProperty(
                "--scale",
                scale
            );

            card.style.setProperty(
                "--rotate",
                `${rotate}deg`
            );

            card.style.setProperty(
                "--opacity",
                opacity
            );

            card.style.setProperty(
                "--z",
                z
            );

        }
    );


    if (memberCurrent) {

        memberCurrent.textContent =
            String(
                memberIndex + 1
            ).padStart(2, "0");

    }


    memberDots.forEach(
        (dot, i) => {

            dot.classList.toggle(
                "active",
                i === memberIndex
            );

        }
    );

}


/* =========================================================
   NEXT
========================================================= */

function nextMember() {

    if (
        memberTotalCards === 0
    ) {
        return;
    }

    memberIndex++;

    if (
        memberIndex >=
        memberTotalCards
    ) {

        memberIndex = 0;

    }

    updateMemberSlider();

}


/* =========================================================
   PREVIOUS
========================================================= */

function previousMember() {

    if (
        memberTotalCards === 0
    ) {
        return;
    }

    memberIndex--;

    if (
        memberIndex < 0
    ) {

        memberIndex =
            memberTotalCards - 1;

    }

    updateMemberSlider();

}


/* =========================================================
   BUTTONS
========================================================= */

if (memberNext) {

    memberNext.addEventListener(
        "click",
        () => {

            nextMember();

            resetMemberAutoSlide();

        }
    );

}


if (memberPrev) {

    memberPrev.addEventListener(
        "click",
        () => {

            previousMember();

            resetMemberAutoSlide();

        }
    );

}


/* =========================================================
   AUTO SLIDE
========================================================= */

let memberAutoSlide = null;


function startMemberAutoSlide() {

    if (
        memberTotalCards <= 1
    ) {
        return;
    }

    memberAutoSlide =
        setInterval(
            nextMember,
            4000
        );

}


function resetMemberAutoSlide() {

    if (memberAutoSlide) {

        clearInterval(
            memberAutoSlide
        );

    }

    startMemberAutoSlide();

}


/* =========================================================
   KEYBOARD
========================================================= */

document.addEventListener(
    "keydown",
    (event) => {

        if (
            event.key ===
            "ArrowRight"
        ) {

            nextMember();

            resetMemberAutoSlide();

        }

        if (
            event.key ===
            "ArrowLeft"
        ) {

            previousMember();

            resetMemberAutoSlide();

        }

    }
);


/* =========================================================
   PAUSE ON HOVER
========================================================= */

if (memberSlider) {

    memberSlider.addEventListener(
        "mouseenter",
        () => {

            if (memberAutoSlide) {

                clearInterval(
                    memberAutoSlide
                );

            }

        }
    );


    memberSlider.addEventListener(
        "mouseleave",
        () => {

            resetMemberAutoSlide();

        }
    );

}


/* =========================================================
   INITIALIZE
========================================================= */

if (
    memberTotalCards > 0
) {

    updateMemberSlider();

    startMemberAutoSlide();

}
</script>
