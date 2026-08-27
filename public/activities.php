<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/*
|--------------------------------------------------------------------------
| LOAD MATCHES FROM DATABASE
|--------------------------------------------------------------------------
| Uses the EXACT structure of the matches table:
|
| id
| competition_name
| match_type
| team_one
| team_two
| match_date
| match_time
| venue
| status
| team_one_score
| team_two_score
| description
| created_at
| updated_at
|--------------------------------------------------------------------------
*/

$matches = [];

try {

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
            description
        FROM matches
        ORDER BY

            CASE status

                WHEN 'Live' THEN 1
                WHEN 'Upcoming' THEN 2
                WHEN 'Completed' THEN 3
                WHEN 'Cancelled' THEN 4

                ELSE 5

            END,

            CASE

                WHEN status IN ('Live', 'Upcoming')
                THEN match_date

                ELSE NULL

            END ASC,

            CASE

                WHEN status IN ('Completed', 'Cancelled')
                THEN match_date

                ELSE NULL

            END DESC,

            match_time ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute();

    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Never expose database errors to public users
    |--------------------------------------------------------------------------
    */

    $matches = [];

}


/*
|--------------------------------------------------------------------------
| PREPARE MATCH DATA FOR JAVASCRIPT
|--------------------------------------------------------------------------
*/

$matchData = [];

foreach ($matches as $match) {

    /*
    |--------------------------------------------------------------------------
    | DATE
    |--------------------------------------------------------------------------
    */

    $dateObject = DateTime::createFromFormat(
        'Y-m-d',
        (string)$match['match_date']
    );

    $formattedDate = $dateObject
        ? $dateObject->format('d M Y')
        : (string)$match['match_date'];


    /*
    |--------------------------------------------------------------------------
    | TIME
    |--------------------------------------------------------------------------
    */

    $formattedTime = '';

    if (!empty($match['match_time'])) {

        $timeObject = DateTime::createFromFormat(
            'H:i:s',
            (string)$match['match_time']
        );

        if ($timeObject) {

            $formattedTime =
                $timeObject->format('h:i A');

        } else {

            $formattedTime =
                (string)$match['match_time'];

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SCORES
    |--------------------------------------------------------------------------
    */

    $teamOneScore = null;
    $teamTwoScore = null;

    if (
        $match['team_one_score'] !== null &&
        $match['team_one_score'] !== ''
    ) {

        $teamOneScore =
            (int)$match['team_one_score'];

    }

    if (
        $match['team_two_score'] !== null &&
        $match['team_two_score'] !== ''
    ) {

        $teamTwoScore =
            (int)$match['team_two_score'];

    }


    /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */

    $result = '';

    if (
        $teamOneScore !== null &&
        $teamTwoScore !== null
    ) {

        $result =
            $teamOneScore . ' - ' . $teamTwoScore;

    }


    /*
    |--------------------------------------------------------------------------
    | ADD MATCH
    |--------------------------------------------------------------------------
    */

    $matchData[] = [

        'id' =>
            (int)$match['id'],

        'date' =>
            $formattedDate,

        'raw_date' =>
            (string)$match['match_date'],

        'time' =>
            $formattedTime,

        'competition_name' =>
            (string)$match['competition_name'],

        'match_type' =>
            (string)$match['match_type'],

        'team_one' =>
            (string)$match['team_one'],

        'team_two' =>
            (string)$match['team_two'],

        'venue' =>
            (string)($match['venue'] ?? ''),

        'status' =>
            (string)($match['status'] ?? 'Upcoming'),

        'team_one_score' =>
            $teamOneScore,

        'team_two_score' =>
            $teamTwoScore,

        'result' =>
            $result,

        'description' =>
            (string)($match['description'] ?? '')

    ];

}

?>

<!DOCTYPE html>

<html lang="en" spellcheck="false">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="MTU Badminton Club activities, training, matches and announcements.">

    <title>
        MTU Badminton Club | Activities
    </title>


    <style>
    /* =========================================================
   RESET
========================================================= */

    * {

        margin: 0;

        padding: 0;

        box-sizing: border-box;

    }


    /* =========================================================
   VARIABLES
========================================================= */

    :root {

        --orange: #fb8500;

        --yellow: #ffb703;

        --skyblue: #87ceeb;

        --pure-white: #ffffff;

        --text-dark: #1a252c;

        --text-muted: #546e7a;

        --green: #16a34a;

        --green-bg: #dcfce7;

        --red: #dc2626;

        --red-bg: #fee2e2;

        --blue: #2563eb;

        --blue-bg: #dbeafe;

    }


    /* =========================================================
   HTML / BODY
========================================================= */

    html {

        scroll-behavior: smooth;

    }


    body {

        font-family:
            "Segoe UI",
            Arial,
            sans-serif;

        background: #eef9ff;

        color: var(--text-dark);

        min-height: 100vh;

        overflow-x: hidden;

    }


    /* =========================================================
   BACKGROUND
========================================================= */

    .bg {

        position: fixed;

        inset: 0;

        z-index: -5;

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


    .glow {

        position: absolute;

        border-radius: 50%;

        filter: blur(85px);

        opacity: .4;

        animation:
            float 10s ease-in-out infinite alternate;

    }


    .g1 {

        width: 420px;

        height: 420px;

        background: var(--skyblue);

        left: -170px;

        top: -180px;

    }


    .g2 {

        width: 450px;

        height: 450px;

        background: var(--orange);

        right: -180px;

        bottom: -180px;

        animation-delay: 3s;

    }


    .g3 {

        width: 240px;

        height: 240px;

        background: var(--yellow);

        left: 45%;

        top: 35%;

        animation-delay: 5s;

    }


    @keyframes float {

        to {

            transform:
                translate(60px, -40px) scale(1.18);

        }

    }


    /* =========================================================
   NAVIGATION
========================================================= */

    nav {

        height: 80px;

        padding: 0 6%;

        display: flex;

        align-items: center;

        justify-content: space-between;

        background:
            rgba(255, 255, 255, 0.88);

        backdrop-filter: blur(18px);

        border-bottom:
            2px solid var(--yellow);

        position: sticky;

        top: 0;

        z-index: 100;

        box-shadow:
            0 4px 20px rgba(251, 133, 0, 0.1);

    }


    .logo {

        display: flex;

        align-items: center;

        gap: 12px;

    }


    .logo img {

        width: 50px;

        height: 50px;

        object-fit: contain;

        animation:
            logo 3s ease-in-out infinite;

    }


    @keyframes logo {

        50% {

            transform:
                translateY(-5px) rotate(3deg);

            filter:
                drop-shadow(0 0 12px rgba(251, 133, 0, .7));

        }

    }


    .logo b {

        color: var(--orange);

        font-size: 18px;

    }


    .logo span {

        color: var(--yellow);

        text-shadow:
            0 1px 2px rgba(0, 0, 0, 0.1);

    }


    nav ul {

        display: flex;

        gap: 24px;

        list-style: none;

        align-items: center;

    }


    nav a {

        color: var(--text-dark);

        text-decoration: none;

        font-size: 12px;

        font-weight: 800;

        letter-spacing: 1px;

        transition: .3s;

        position: relative;

    }


    nav a:hover {

        color: var(--orange);

    }


    nav .reg {

        background:
            linear-gradient(135deg,
                var(--yellow),
                var(--orange));

        color: #fff !important;

        padding: 10px 17px;

        border-radius: 20px;

        box-shadow:
            0 4px 15px rgba(251, 133, 0, 0.3);

    }


    .nav-toggle {

        display: none;

        background: none;

        border: none;

        font-size: 28px;

        color: var(--orange);

        cursor: pointer;

        z-index: 101;

    }


    /* =========================================================
   HERO
========================================================= */

    .hero {

        min-height:
            calc(100vh - 80px);

        padding:
            55px 7%;

        display: flex;

        align-items: center;

        gap: 5%;

    }


    .left {

        width: 42%;

        animation:
            enter 1s ease both;

    }


    .left small {

        color: var(--orange);

        letter-spacing: 4px;

        font-weight: 800;

    }


    .left h1 {

        font-size:
            clamp(48px, 6vw, 82px);

        line-height: .95;

        margin-top: 18px;

    }


    .orange {

        color: var(--orange);

    }


    .gradient {

        background:
            linear-gradient(90deg,
                var(--orange),
                var(--yellow),
                var(--skyblue));

        background-clip: text;

        -webkit-text-fill-color: transparent;

        background-size: 200% auto;

        animation:
            gradientMove 4s linear infinite;

    }


    @keyframes gradientMove {

        to {

            background-position:
                200% center;

        }

    }


    .left p {

        color: var(--text-muted);

        line-height: 1.8;

        margin-top: 25px;

        max-width: 520px;

        font-weight: 500;

    }


    .line {

        width: 110px;

        height: 4px;

        border-radius: 10px;

        background:
            linear-gradient(90deg,
                var(--yellow),
                var(--orange));

        margin-top: 25px;

        box-shadow:
            0 0 15px rgba(251, 133, 0, 0.5);

    }


    @keyframes enter {

        from {

            opacity: 0;

            transform:
                translateX(-70px);

        }

        to {

            opacity: 1;

            transform: none;

        }

    }


    /* =========================================================
   MENU
========================================================= */

    .menu {

        width: 58%;

        max-width: 700px;

        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 18px;

        animation:
            enterRight 1s .25s ease both;

    }


    @keyframes enterRight {

        from {

            opacity: 0;

            transform:
                translateX(80px) scale(.9);

        }

        to {

            opacity: 1;

            transform: none;

        }

    }


    .button {

        min-height: 125px;

        border:
            2px solid var(--yellow);

        border-radius: 23px;

        background:
            rgba(255, 255, 255, 0.88);

        backdrop-filter:
            blur(15px);

        padding: 20px;

        display: flex;

        align-items: center;

        gap: 16px;

        color: var(--text-dark);

        cursor: pointer;

        position: relative;

        overflow: hidden;

        transition: .45s;

        box-shadow:

            0 10px 30px rgba(251, 133, 0, 0.08),

            0 0 20px rgba(255, 183, 3, 0.15);

        animation:
            breathe 4s ease-in-out infinite;

    }


    .button:nth-child(2) {

        animation-delay: .4s;

    }


    .button:nth-child(3) {

        animation-delay: .8s;

    }


    .button:nth-child(4) {

        animation-delay: 1.2s;

    }


    .button:nth-child(5) {

        animation-delay: 1.6s;

    }


    .button:nth-child(6) {

        animation-delay: 2s;

    }


    @keyframes breathe {

        50% {

            transform:
                translateY(-7px);

        }

    }


    .button:hover {

        transform:
            translateY(-12px) scale(1.035) !important;

        border-color:
            var(--orange);

        box-shadow:

            0 20px 40px rgba(251, 133, 0, 0.25),

            0 0 30px rgba(255, 183, 3, 0.4);

    }


    .icon {

        width: 60px;

        height: 60px;

        min-width: 60px;

        border-radius: 18px;

        display: grid;

        place-items: center;

        font-size: 28px;

        background:
            linear-gradient(135deg,
                rgba(255, 183, 3, 0.2),
                rgba(251, 133, 0, 0.15));

        border:
            1px solid var(--yellow);

        transition: .45s;

    }


    .button:hover .icon {

        background:
            linear-gradient(135deg,
                var(--yellow),
                var(--orange));

        color: #fff;

        transform:
            rotate(-8deg) scale(1.12);

    }


    .button h3 {

        font-size: 15px;

        margin-bottom: 7px;

        color: var(--text-dark);

    }


    .button p {

        font-size: 11px;

        color: var(--text-muted);

        line-height: 1.45;

    }


    /* =========================================================
   COACHES
========================================================= */

    .coaches-section {

        padding:
            80px 7% 90px;

        text-align: center;

        position: relative;

    }


    .coaches-heading small {

        color: var(--orange);

        font-size: 12px;

        font-weight: 800;

        letter-spacing: 4px;

    }


    .coaches-heading h2 {

        margin-top: 10px;

        font-size:
            clamp(34px, 4vw, 52px);

        color: var(--text-dark);

    }


    .coaches-heading h2 span {

        color: var(--orange);

    }


    .coaches-heading p {

        max-width: 650px;

        margin:
            15px auto 45px;

        color: var(--text-muted);

        line-height: 1.7;

        font-size: 14px;

    }


    .coaches-container {

        max-width: 1000px;

        margin: 0 auto;

        display: grid;

        grid-template-columns:
            repeat(2, 1fr);

        gap: 30px;

    }


    .coach-card {

        position: relative;

        display: flex;

        align-items: center;

        text-align: left;

        gap: 25px;

        padding: 28px;

        min-height: 190px;

        border-radius: 26px;

        background:
            rgba(255, 255, 255, 0.90);

        backdrop-filter:
            blur(15px);

        border:
            2px solid rgba(255, 183, 3, 0.45);

        box-shadow:

            0 12px 35px rgba(251, 133, 0, 0.10),

            0 0 25px rgba(135, 206, 235, 0.15);

        overflow: hidden;

        transition: .4s ease;

    }


    .coach-card::before {

        content: "";

        position: absolute;

        width: 160px;

        height: 160px;

        border-radius: 50%;

        background:
            rgba(135, 206, 235, 0.16);

        right: -65px;

        top: -65px;

        transition: .4s;

    }


    .coach-card::after {

        content: "";

        position: absolute;

        width: 100px;

        height: 100px;

        border-radius: 50%;

        background:
            rgba(255, 183, 3, 0.10);

        left: -50px;

        bottom: -50px;

    }


    .coach-card:hover {

        transform:
            translateY(-10px);

        border-color:
            var(--orange);

        box-shadow:

            0 20px 45px rgba(251, 133, 0, 0.18),

            0 0 30px rgba(255, 183, 3, 0.20);

    }


    .coach-card:hover::before {

        transform:
            scale(1.25);

    }


    .coach-image {

        position: relative;

        width: 135px;

        height: 135px;

        min-width: 135px;

        border-radius: 50%;

        overflow: hidden;

        border: 5px solid #fff;

        box-shadow:

            0 0 0 3px var(--yellow),

            0 8px 25px rgba(251, 133, 0, 0.25);

        z-index: 2;

    }


    .coach-image img {

        width: 100%;

        height: 100%;

        object-fit: cover;

        display: block;

        transition: .5s ease;

    }


    .coach-card:hover .coach-image img {

        transform:
            scale(1.08);

    }


    .coach-info {

        position: relative;

        z-index: 2;

        flex: 1;

    }


    .coach-role {

        display: inline-block;

        padding: 6px 12px;

        border-radius: 20px;

        background:
            rgba(255, 183, 3, 0.15);

        color: var(--orange);

        font-size: 10px;

        font-weight: 800;

        letter-spacing: 1px;

        margin-bottom: 9px;

    }


    .coach-info h3 {

        font-size: 23px;

        color: var(--text-dark);

        margin-bottom: 13px;

    }


    .coach-detail {

        display: flex;

        align-items: flex-start;

        gap: 9px;

        margin: 7px 0;

        color: var(--text-muted);

        font-size: 13px;

        line-height: 1.5;

    }


    .coach-detail-icon {

        width: 22px;

        min-width: 22px;

        height: 22px;

        border-radius: 7px;

        display: grid;

        place-items: center;

        background:
            rgba(255, 183, 3, 0.13);

        font-size: 12px;

    }


    .coach-detail strong {

        color: var(--text-dark);

    }


    /* =========================================================
   MODAL
========================================================= */

    .overlay {

        position: fixed;

        inset: 0;

        background:
            rgba(26, 37, 44, .5);

        backdrop-filter:
            blur(12px);

        display: none;

        align-items: center;

        justify-content: center;

        padding: 25px;

        z-index: 1000;

    }


    .overlay.show {

        display: flex;

        animation:
            fade .35s ease;

    }


    @keyframes fade {

        from {

            opacity: 0;

        }

        to {

            opacity: 1;

        }

    }


    .card {

        width:
            min(1100px, 96vw);

        max-height: 88vh;

        overflow: auto;

        border-radius: 26px;

        position: relative;

        padding: 38px;

        background:
            rgba(255, 255, 255, 0.96);

        border:
            2px solid var(--yellow);

        box-shadow:

            0 30px 90px rgba(251, 133, 0, 0.2),

            0 0 30px rgba(255, 183, 3, 0.3);

        animation:
            cardIn .6s cubic-bezier(.17, .67, .3, 1.3);

    }


    @keyframes cardIn {

        from {

            opacity: 0;

            transform:
                translateY(80px) scale(.75) rotateX(15deg);

        }

        to {

            opacity: 1;

            transform: none;

        }

    }


    .close {

        position: absolute;

        right: 20px;

        top: 18px;

        width: 42px;

        height: 42px;

        border:
            1px solid var(--yellow);

        border-radius: 50%;

        background: #fff;

        color: var(--text-dark);

        font-size: 25px;

        cursor: pointer;

        transition: .3s;

        z-index: 5;

    }


    .close:hover {

        background:
            var(--orange);

        color: #fff;

        transform:
            rotate(90deg);

    }


    .card-top {

        display: flex;

        align-items: center;

        gap: 18px;

        padding-bottom: 22px;

        border-bottom:
            1px solid rgba(255, 183, 3, 0.3);

    }


    .big-icon {

        width: 75px;

        height: 75px;

        border-radius: 22px;

        display: grid;

        place-items: center;

        font-size: 36px;

        background:
            linear-gradient(135deg,
                var(--yellow),
                var(--orange));

        color: #fff;

        box-shadow:
            0 8px 25px rgba(251, 133, 0, 0.3);

    }


    .card h2 {

        font-size: 30px;

        color: var(--text-dark);

    }


    .card .subtitle {

        color: var(--orange);

        font-size: 12px;

        letter-spacing: 2px;

        margin-top: 5px;

        font-weight: 700;

    }


    .content {

        padding-top: 25px;

        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 16px;

    }


    .info {

        padding: 22px;

        border-radius: 18px;

        background:
            rgba(255, 255, 255, 0.9);

        border:
            1px solid rgba(255, 183, 3, 0.3);

        box-shadow:
            0 4px 15px rgba(0, 0, 0, .03);

        transition: .35s;

    }


    .info:hover {

        transform:
            translateY(-5px);

        border-color:
            var(--orange);

    }


    .info h3 {

        color: var(--orange);

        font-size: 15px;

        margin-bottom: 10px;

    }


    .info p,
    .info li {

        color: var(--text-muted);

        font-size: 13px;

        line-height: 1.7;

    }


    .info ul {

        padding-left: 18px;

    }


    .full {

        grid-column:
            1 / -1;

    }


    /* =========================================================
   MATCH TABLE
========================================================= */

    .match-table-wrapper {

        width: 100%;

        overflow-x: auto;

        border-radius: 14px;

    }


    .schedule {

        width: 100%;

        min-width: 900px;

        border-collapse:
            collapse;

        margin-top: 8px;

    }


    .schedule th,
    .schedule td {

        padding: 13px 11px;

        text-align: left;

        border-bottom:
            1px solid rgba(255, 183, 3, 0.2);

        font-size: 12px;

    }


    .schedule th {

        color: var(--orange);

        font-weight: 800;

        background:
            rgba(255, 183, 3, 0.08);

    }


    .schedule td {

        color: var(--text-muted);

    }


    .schedule tr {

        transition: .25s;

    }


    .schedule tbody tr:hover {

        background:
            rgba(255, 183, 3, 0.07);

    }


    /* =========================================================
   TEAM DISPLAY
========================================================= */

    .team-one {

        color: var(--text-dark);

        font-weight: 800;

    }


    .team-two {

        color: var(--text-dark);

        font-weight: 800;

    }


    .vs {

        color: var(--orange);

        font-size: 10px;

        font-weight: 900;

        margin: 3px 0;

    }


    /* =========================================================
   SCORE
========================================================= */

    .score {

        font-size: 14px;

        font-weight: 900;

        color: var(--text-dark);

    }


    .no-score {

        color: #94a3b8;

    }


    /* =========================================================
   STATUS BADGES
========================================================= */

    .status {

        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding:
            5px 10px;

        border-radius: 20px;

        font-size: 10px;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .5px;

        white-space: nowrap;

    }


    .status-upcoming {

        color: var(--blue);

        background:
            var(--blue-bg);

    }


    .status-live {

        color: var(--red);

        background:
            var(--red-bg);

        animation:
            livePulse 1.5s ease-in-out infinite;

    }


    .status-completed {

        color: var(--green);

        background:
            var(--green-bg);

    }


    .status-cancelled {

        color: var(--red);

        background:
            var(--red-bg);

    }


    @keyframes livePulse {

        50% {

            transform:
                scale(1.05);

        }

    }


    /* =========================================================
   MATCH EMPTY
========================================================= */

    .match-empty {

        text-align: center;

        padding: 45px 20px;

        border-radius: 18px;

        background:
            linear-gradient(135deg,
                rgba(255, 183, 3, .08),
                rgba(135, 206, 235, .08));

        border:
            1px dashed rgba(251, 133, 0, .35);

    }


    .match-empty-icon {

        font-size: 45px;

        margin-bottom: 12px;

    }


    .match-empty h3 {

        color:
            var(--text-dark);

        margin-bottom: 7px;

    }


    .match-empty p {

        color:
            var(--text-muted);

        font-size: 13px;

    }


    /* =========================================================
   MATCH DETAILS
========================================================= */

    .match-description {

        margin-top: 8px;

        color: var(--text-muted);

        font-size: 12px;

        line-height: 1.6;

    }


    /* =========================================================
   ACTION BUTTON
========================================================= */

    .action {

        display: inline-block;

        margin-top: 18px;

        padding:
            12px 20px;

        border-radius: 25px;

        background:
            linear-gradient(135deg,
                var(--yellow),
                var(--orange));

        color: #fff;

        text-decoration: none;

        font-weight: 900;

        font-size: 12px;

        transition: .3s;

        box-shadow:
            0 4px 15px rgba(251, 133, 0, 0.3);

    }


    .action:hover {

        transform:
            scale(1.05);

        box-shadow:
            0 8px 25px rgba(251, 133, 0, 0.35);

    }


    /* =========================================================
   FOOTER
========================================================= */

    footer {

        text-align: center;

        padding: 25px;

        color:
            var(--text-muted);

        font-size: 11px;

        font-weight: 600;

        border-top:
            1px solid rgba(255, 183, 3, 0.2);

        background:
            rgba(255, 255, 255, 0.55);

    }


    /* =========================================================
   MOBILE NAVIGATION
========================================================= */

    @media (max-width: 850px) {

        .nav-toggle {

            display: block;

        }


        nav ul {

            position: absolute;

            top: 80px;

            left: 0;

            right: 0;

            background:
                rgba(255, 255, 255, 0.96);

            backdrop-filter:
                blur(20px);

            border-bottom:
                2px solid var(--yellow);

            flex-direction:
                column;

            padding: 25px 0;

            gap: 20px;

            box-shadow:
                0 15px 30px rgba(251, 133, 0, 0.15);

            display: none;

        }


        nav ul.active {

            display: flex;

            animation:
                slideDown .3s ease forwards;

        }


        @keyframes slideDown {

            from {

                opacity: 0;

                transform:
                    translateY(-10px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        nav .reg {

            width: 80%;

            text-align: center;

        }


        .hero {

            flex-direction:
                column;

            padding-top: 45px;

            padding-bottom: 70px;

        }


        .left,
        .menu {

            width: 100%;

            max-width: 700px;

        }


        .left {

            text-align: center;

        }


        .left p {

            margin-left: auto;

            margin-right: auto;

        }


        .line {

            margin-left: auto;

            margin-right: auto;

        }


        .menu {

            margin-top: 20px;

        }


        .coaches-container {

            grid-template-columns:
                1fr;

            max-width: 650px;

        }

    }


    /* =========================================================
   SMALL MOBILE
========================================================= */

    @media (max-width: 600px) {

        .logo {

            gap: 8px;

        }


        .logo img {

            width: 42px;

            height: 42px;

        }


        .logo b {

            font-size: 15px;

        }


        .logo span {

            font-size: 12px;

        }


        .menu {

            grid-template-columns:
                1fr;

        }


        .content {

            grid-template-columns:
                1fr;

        }


        .full {

            grid-column:
                auto;

        }


        .card {

            padding: 25px;

        }


        .card h2 {

            font-size: 24px;

        }


        .hero h1 {

            font-size: 50px;

        }


        .coaches-section {

            padding:
                55px 6% 65px;

        }


        .coaches-heading h2 {

            font-size: 36px;

        }


        .coach-card {

            flex-direction:
                column;

            text-align: center;

            padding:
                30px 20px;

        }


        .coach-image {

            width: 140px;

            height: 140px;

            min-width: 140px;

        }


        .coach-detail {

            justify-content:
                center;

            text-align: left;

        }


        .card-top {

            padding-right:
                35px;

        }

    }
    </style>

</head>


<body>


    <!-- =====================================================
     BACKGROUND
===================================================== -->

    <div class="bg">

        <div class="glow g1"></div>

        <div class="glow g2"></div>

        <div class="glow g3"></div>

    </div>


    <!-- =====================================================
     NAVIGATION
===================================================== -->

    <nav>

        <div class="logo">

            <img src="../assets/images/mtubd.jpg" alt="MTU Badminton Club Logo">

            <div>

                <b>MTU</b>

                <span>BADMINTON</span>

                CLUB

            </div>

        </div>


        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" type="button">
            ☰
        </button>


        <ul id="navMenu">

            <li>

                <a href="index.php">
                    HOME
                </a>

            </li>


            <li>

                <a href="member.php">
                    MEMBERS
                </a>

            </li>


            <li>

                <a href="announcement.php">
                    ANNOUNCEMENT
                </a>

            </li>


            <li>

                <a href="register.php" class="reg">
                    REGISTRATION
                </a>

            </li>


            <li>

                <a href="index.php#contact">
                    CONTACT
                </a>

            </li>

        </ul>

    </nav>


    <!-- =====================================================
     HERO
===================================================== -->

    <main class="hero">


        <!-- =================================================
         LEFT
    ================================================== -->

        <section class="left">

            <small>
                MANDALAY TECHNOLOGICAL UNIVERSITY
            </small>


            <h1>

                <span class="orange">
                    MTU
                </span>

                <br>

                <span class="gradient">
                    BADMINTON
                </span>

                <br>

                CLUB

            </h1>


            <p>

                Step into the world of smashing dreams.
                Click any card and discover our training,
                rules, activities, matches and everything
                happening inside MTU Badminton Club.

            </p>


            <div class="line"></div>

        </section>


        <!-- =================================================
         MENU
    ================================================== -->

        <section class="menu">


            <button class="button" data-key="training" type="button">

                <div class="icon">
                    🏸
                </div>

                <div>

                    <h3>
                        WEEKLY TRAINING
                    </h3>

                    <p>
                        Training schedules, courts & practice sessions.
                    </p>

                </div>

            </button>


            <button class="button" data-key="rules" type="button">

                <div class="icon">
                    📖
                </div>

                <div>

                    <h3>
                        RULES & REGULATIONS
                    </h3>

                    <p>
                        Club rules and badminton guidelines.
                    </p>

                </div>

            </button>


            <button class="button" data-key="activities" type="button">

                <div class="icon">
                    ⚡️
                </div>

                <div>

                    <h3>
                        CLUB ACTIVITIES
                    </h3>

                    <p>
                        Events, challenges and memorable moments.
                    </p>

                </div>

            </button>


            <button class="button" data-key="matches" type="button">

                <div class="icon">
                    🏆
                </div>

                <div>

                    <h3>
                        MATCHES
                    </h3>

                    <p>
                        Upcoming matches, tournaments & results.
                    </p>

                </div>

            </button>


            <button class="button" data-key="announcement" type="button">

                <div class="icon">
                    📢
                </div>

                <div>

                    <h3>
                        ANNOUNCEMENTS
                    </h3>

                    <p>
                        Latest news and important notices.
                    </p>

                </div>

            </button>


            <button class="button" data-key="join" type="button">

                <div class="icon">
                    📝
                </div>

                <div>

                    <h3>
                        JOIN THE CLUB
                    </h3>

                    <p>
                        Become part of our badminton family.
                    </p>

                </div>

            </button>


        </section>

    </main>


    <!-- =====================================================
     COACHES
===================================================== -->

    <section class="coaches-section" id="coaches">

        <div class="coaches-heading">

            <small>
                MEET THE TEAM
            </small>


            <h2>

                OUR
                <span>COACHES</span>

            </h2>


            <p>

                Meet the coaches who guide, train and motivate
                our players to become better athletes and
                stronger teammates.

            </p>

        </div>


        <div class="coaches-container">


            <!-- COACH 1 -->

            <div class="coach-card">

                <div class="coach-image">

                    <img src="../assets/images/c1.jpg" alt="Coach Name 1">

                </div>


                <div class="coach-info">

                    <span class="coach-role">
                        BADMINTON COACH
                    </span>


                    <h3>
                        Ye Min Khant
                    </h3>


                    <div class="coach-detail">

                        <span class="coach-detail-icon">
                            🎓
                        </span>

                        <span>

                            <strong>
                                Year:
                            </strong>

                            2nd Year

                        </span>

                    </div>


                    <div class="coach-detail">

                        <span class="coach-detail-icon">
                            👷
                        </span>

                        <span>

                            <strong>
                                Major:
                            </strong>

                            Civil Engineering

                        </span>

                    </div>

                </div>

            </div>


            <!-- COACH 2 -->

            <div class="coach-card">

                <div class="coach-image">

                    <img src="../assets/images/coach2.jpg" alt="Coach Name 2">

                </div>


                <div class="coach-info">

                    <span class="coach-role">
                        BADMINTON COACH
                    </span>


                    <h3>
                        Hein Htet Soe
                    </h3>


                    <div class="coach-detail">

                        <span class="coach-detail-icon">
                            🎓
                        </span>

                        <span>

                            <strong>
                                Year:
                            </strong>

                            1st Year

                        </span>

                    </div>


                    <div class="coach-detail">

                        <span class="coach-detail-icon">
                            ⚡
                        </span>

                        <span>

                            <strong>
                                Major:
                            </strong>

                            Electrical Power Engineering

                        </span>

                    </div>

                </div>

            </div>


        </div>

    </section>


    <!-- =====================================================
     FOOTER
===================================================== -->

    <footer>

        © 2026 MTU Badminton Club
        • Mandalay Technological University

    </footer>


    <!-- =====================================================
     MODAL
===================================================== -->

    <div class="overlay" id="overlay">

        <div class="card">


            <button class="close" id="close" type="button" aria-label="Close">
                ×
            </button>


            <div class="card-top">

                <div class="big-icon" id="bigIcon">
                    🏸
                </div>


                <div>

                    <h2 id="title">
                        Weekly Training
                    </h2>


                    <div class="subtitle" id="subtitle">
                        TRAIN • IMPROVE • REPEAT
                    </div>

                </div>

            </div>


            <div class="content" id="content"></div>


        </div>

    </div>


    <!-- =====================================================
     JAVASCRIPT
===================================================== -->

    <script>
    /* =========================================================
   DATABASE MATCH DATA
========================================================= */

    const matches = <?= json_encode(
    $matchData,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
) ?>;


    /* =========================================================
       MOBILE NAVIGATION
    ========================================================= */

    const navToggle =
        document.getElementById("navToggle");

    const navMenu =
        document.getElementById("navMenu");


    navToggle.addEventListener(
        "click",
        () => {

            navMenu.classList.toggle("active");

            navToggle.textContent =
                navMenu.classList.contains("active") ?
                "✕" :
                "☰";

        }
    );


    /* =========================================================
       ESCAPE HTML
    ========================================================= */

    function escapeHtml(value) {

        return String(value ?? "")

            .replace(
                /&/g,
                "&amp;"
            )

            .replace(
                /</g,
                "&lt;"
            )

            .replace(
                />/g,
                "&gt;"
            )

            .replace(
                /"/g,
                "&quot;"
            )

            .replace(
                /'/g,
                "&#039;"
            );

    }


    /* =========================================================
       STATUS BADGE
    ========================================================= */

    function statusBadge(status) {

        const safeStatus =
            String(
                status || "Upcoming"
            ).toLowerCase();


        let label =
            "Upcoming";

        let className =
            "status-upcoming";

        let icon =
            "📅";


        if (
            safeStatus === "live"
        ) {

            label =
                "Live";

            className =
                "status-live";

            icon =
                "🔴";

        } else if (
            safeStatus === "completed"
        ) {

            label =
                "Completed";

            className =
                "status-completed";

            icon =
                "✅";

        } else if (
            safeStatus === "cancelled"
        ) {

            label =
                "Cancelled";

            className =
                "status-cancelled";

            icon =
                "❌";

        }


        return `

        <span class="status ${className}">

            ${icon}

            ${escapeHtml(label)}

        </span>

    `;

    }


    /* =========================================================
       CREATE MATCH ROW
    ========================================================= */

    function createMatchRow(match) {

        const date =
            escapeHtml(
                match.date
            );


        const time =
            escapeHtml(
                match.time || ""
            );


        const competition =
            escapeHtml(
                match.competition_name
            );


        const type =
            escapeHtml(
                match.match_type
            );


        const teamOne =
            escapeHtml(
                match.team_one
            );


        const teamTwo =
            escapeHtml(
                match.team_two
            );


        const venue =
            escapeHtml(
                match.venue || "TBA"
            );


        const description =
            escapeHtml(
                match.description || ""
            );


        let scoreHtml = `
        <span class="no-score">
            —
        </span>
    `;


        if (
            match.team_one_score !== null &&
            match.team_two_score !== null
        ) {

            scoreHtml = `

            <span class="score">

                ${escapeHtml(
                    match.team_one_score
                )}

                -

                ${escapeHtml(
                    match.team_two_score
                )}

            </span>

        `;

        }


        return `

        <tr>

            <!-- DATE -->

            <td>

                <strong>
                    ${date}
                </strong>

                ${
                    time
                    ? `
                        <br>

                        <small>
                            🕐 ${time}
                        </small>
                      `
                    : ""
                }

            </td>


            <!-- COMPETITION -->

            <td>

                <strong>
                    ${competition}
                </strong>

                ${
                    description
                    ? `
                        <div class="match-description">
                            ${description}
                        </div>
                      `
                    : ""
                }

            </td>


            <!-- TYPE -->

            <td>

                ${type}

            </td>


            <!-- TEAMS -->

            <td>

                <div class="team-one">

                    ${teamOne}

                </div>


                <div class="vs">

                    VS

                </div>


                <div class="team-two">

                    ${teamTwo}

                </div>

            </td>


            <!-- VENUE -->

            <td>

                ${venue}

            </td>


            <!-- STATUS -->

            <td>

                ${statusBadge(
                    match.status
                )}

            </td>


            <!-- RESULT -->

            <td>

                ${scoreHtml}

            </td>

        </tr>

    `;

    }


    /* =========================================================
       CREATE MATCH CONTENT
    ========================================================= */

    function createMatchesContent() {


        /*
        |--------------------------------------------------------------------------
        | NO MATCHES
        |--------------------------------------------------------------------------
        */

        if (
            !matches.length
        ) {

            return `

            <div class="info full">

                <div class="match-empty">

                    <div class="match-empty-icon">
                        🏆
                    </div>


                    <h3>
                        No Matches Available
                    </h3>


                    <p>

                        Match schedules and tournament
                        information will appear here
                        when they are published by the club.

                    </p>

                </div>

            </div>

        `;

        }


        /*
        |--------------------------------------------------------------------------
        | MATCH ROWS
        |--------------------------------------------------------------------------
        */

        const rows =
            matches
            .map(
                createMatchRow
            )
            .join("");


        /*
        |--------------------------------------------------------------------------
        | COUNTS
        |--------------------------------------------------------------------------
        */

        const upcoming =
            matches.filter(
                match =>
                String(
                    match.status
                ).toLowerCase() ===
                "upcoming"
            ).length;


        const live =
            matches.filter(
                match =>
                String(
                    match.status
                ).toLowerCase() ===
                "live"
            ).length;


        const completed =
            matches.filter(
                match =>
                String(
                    match.status
                ).toLowerCase() ===
                "completed"
            ).length;


        const cancelled =
            matches.filter(
                match =>
                String(
                    match.status
                ).toLowerCase() ===
                "cancelled"
            ).length;


        /*
        |--------------------------------------------------------------------------
        | HTML
        |--------------------------------------------------------------------------
        */

        return `

        <!-- =================================================
             MATCH TABLE
        ================================================== -->

        <div class="info full">

            <h3>
                🏆 MATCH SCHEDULE & RESULTS
            </h3>


            <div class="match-table-wrapper">

                <table class="schedule">

                    <thead>

                        <tr>

                            <th>
                                DATE
                            </th>

                            <th>
                                COMPETITION
                            </th>

                            <th>
                                TYPE
                            </th>

                            <th>
                                TEAMS
                            </th>

                            <th>
                                VENUE
                            </th>

                            <th>
                                STATUS
                            </th>

                            <th>
                                SCORE
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        ${rows}

                    </tbody>

                </table>

            </div>

        </div>


        <!-- =================================================
             UPCOMING
        ================================================== -->

        <div class="info">

            <h3>
                📅 UPCOMING
            </h3>

            <p>

                <strong>
                    ${upcoming}
                </strong>

                upcoming
                ${
                    upcoming === 1
                        ? "match"
                        : "matches"
                }.

            </p>

        </div>


        <!-- =================================================
             LIVE
        ================================================== -->

        <div class="info">

            <h3>
                🔴 LIVE
            </h3>

            <p>

                <strong>
                    ${live}
                </strong>

                live
                ${
                    live === 1
                        ? "match"
                        : "matches"
                }.

            </p>

        </div>


        <!-- =================================================
             COMPLETED
        ================================================== -->

        <div class="info">

            <h3>
                🥇 COMPLETED
            </h3>

            <p>

                <strong>
                    ${completed}
                </strong>

                completed
                ${
                    completed === 1
                        ? "match"
                        : "matches"
                }.

            </p>

        </div>


        <!-- =================================================
             CANCELLED
        ================================================== -->

        <div class="info">

            <h3>
                ❌ CANCELLED
            </h3>

            <p>

                <strong>
                    ${cancelled}
                </strong>

                cancelled
                ${
                    cancelled === 1
                        ? "match"
                        : "matches"
                }.

            </p>

        </div>


        <!-- =================================================
             MATCH DAY
        ================================================== -->

        <div class="info full">

            <h3>
                🎯 MATCH DAY
            </h3>

            <p>

                Bring your racket, water, team spirit
                and your best game. Check the match
                schedule above for the latest information.

            </p>

        </div>

    `;

    }


    /* =========================================================
       MODAL DATA
    ========================================================= */

    const data = {


        /* =====================================================
           TRAINING
        ===================================================== */

        training: {

            icon: "🏸",

            title: "Weekly Training",

            subtitle: "TRAIN • IMPROVE • REPEAT",

            html: `

            <div class="info full">

                <h3>
                    🏸 TRAINING SCHEDULE
                </h3>


                <div class="match-table-wrapper">

                    <table class="schedule">

                        <thead>

                            <tr>

                                <th>
                                    DAY
                                </th>

                                <th>
                                    TIME
                                </th>

                                <th>
                                    ACTIVITY
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td>
                                    Monday
                                </td>

                                <td>
                                    4:00 PM – 6:00 PM
                                </td>

                                <td>
                                    Physical Conditioning & Agility
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Tuesday
                                </td>

                                <td>
                                    4:00 PM – 6:00 PM
                                </td>

                                <td>
                                    Shadow Training & Tactics
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Wednesday
                                </td>

                                <td>
                                    4:00 PM – 6:00 PM
                                </td>

                                <td>
                                    Multi-Shuttle Technical Drills
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Thursday
                                </td>

                                <td>
                                    4:00 PM – 6:00 PM
                                </td>

                                <td>
                                    Multi-Shuttle Technical Drills
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Friday
                                </td>

                                <td>
                                    4:00 PM – 6:00 PM
                                </td>

                                <td>
                                    Smash & Defense Training
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    Saturday
                                </td>

                                <td>
                                    4:00 PM – 6:00 PM
                                </td>

                                <td>
                                    Match Play & Strategy
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <div class="info">

                <h3>
                    🔥 TRAINING FOCUS
                </h3>

                <p>

                    Footwork, serving, clears, drops,
                    drives, smash, defense and doubles rotation.

                </p>

            </div>


            <div class="info">

                <h3>
                    🎯 GOAL
                </h3>

                <p>

                    Build stronger skills, better teamwork
                    and confidence before competitions.

                </p>

            </div>

        `

        },


        /* =====================================================
           RULES
        ===================================================== */

        rules: {

            icon: "📖",

            title: "Rules & Regulations",

            subtitle: "PLAY FAIR • PLAY SMART",

            html: `

            <div class="info">

                <h3>
                    🏸 COURT RULES
                </h3>

                <ul>

                    <li>
                        Arrive before training begins.
                    </li>

                    <li>
                        Wear suitable sports shoes.
                    </li>

                    <li>
                        Keep the court clean.
                    </li>

                    <li>
                        Respect other players.
                    </li>

                </ul>

            </div>


            <div class="info">

                <h3>
                    🤝 CLUB RULES
                </h3>

                <ul>

                    <li>
                        Be respectful to every member.
                    </li>

                    <li>
                        Follow coach instructions.
                    </li>

                    <li>
                        Take care of club equipment.
                    </li>

                    <li>
                        Support your teammates.
                    </li>

                </ul>

            </div>


            <div class="info full">

                <h3>
                    ⭐ OUR CLUB PROMISE
                </h3>

                <p>

                    We compete with passion, respect our
                    opponents and always play with good sportsmanship.

                </p>

            </div>

        `

        },


        /* =====================================================
           ACTIVITIES
        ===================================================== */

        activities: {

            icon: "⚡️",

            title: "Club Activities",

            subtitle: "MORE THAN JUST BADMINTON",

            html: `

            <div class="info">

                <h3>
                    🏸 TRAINING CAMP
                </h3>

                <p>

                    Special training sessions for improving
                    technique, stamina and match confidence.

                </p>

            </div>


            <div class="info">

                <h3>
                    🎉 CAMPUS EVENTS
                </h3>

                <p>

                    Join MTU events, club booths, games
                    and fun activities with fellow students.

                </p>

            </div>


            <div class="info">

                <h3>
                    📸 CLUB MOMENTS
                </h3>

                <p>

                    Photo sessions, team memories,
                    celebrations and behind-the-scenes moments.

                </p>

            </div>


            <div class="info">

                <h3>
                    💪 FITNESS CHALLENGE
                </h3>

                <p>

                    Friendly fitness challenges designed
                    to make training more energetic and enjoyable.

                </p>

            </div>

        `

        },


        /* =====================================================
           MATCHES
        ===================================================== */

        matches: {

            icon: "🏆",

            title: "Matches & Tournaments",

            subtitle: "COMPETE • ACHIEVE • CELEBRATE",

            html: createMatchesContent()

        },


        /* =====================================================
           ANNOUNCEMENTS
        ===================================================== */

        announcement: {

            icon: "📢",

            title: "Announcements",

            subtitle: "STAY UPDATED • NEVER MISS OUT",

            html: `

            <div class="info full">

                <h3>
                    📢 LATEST NEWS
                </h3>

                <p>

                    <b style="color:var(--orange)">
                        NEW MEMBER REGISTRATION
                    </b>

                    <br>

                    Registration for new MTU Badminton Club
                    members is now open. Check the Registration
                    page for details.

                </p>

            </div>


            <div class="info">

                <h3>
                    🏸 TRAINING NOTICE
                </h3>

                <p>

                    Weekly training schedules may change
                    depending on university events and
                    court availability.

                </p>

            </div>


            <div class="info">

                <h3>
                    📅 EVENT NOTICE
                </h3>

                <p>

                    Follow the club announcements for
                    upcoming matches, activities and special events.

                </p>

            </div>


            <a
                class="action"
                href="announcement.php"
            >
                VIEW ALL ANNOUNCEMENTS →
            </a>

        `

        },


        /* =====================================================
           JOIN
        ===================================================== */

        join: {

            icon: "📝",

            title: "Join MTU Badminton Club",

            subtitle: "YOUR GAME • YOUR TEAM • YOUR STORY",

            html: `

            <div class="info full">

                <h3>
                    🏸 READY TO JOIN?
                </h3>

                <p>

                    Whether you are a beginner or an
                    experienced player, everyone who loves
                    badminton is welcome to become part
                    of the MTU Badminton Club.

                </p>


                <a
                    class="action"
                    href="register.php"
                >
                    REGISTER NOW →
                </a>

            </div>


            <div class="info">

                <h3>
                    ✨ WHAT YOU GET
                </h3>

                <ul>

                    <li>
                        Weekly training
                    </li>

                    <li>
                        Friendly matches
                    </li>

                    <li>
                        Club activities
                    </li>

                    <li>
                        Team experience
                    </li>

                </ul>

            </div>


            <div class="info">

                <h3>
                    💙 WHAT WE EXPECT
                </h3>

                <ul>

                    <li>
                        Teamwork
                    </li>

                    <li>
                        Respect
                    </li>

                    <li>
                        Commitment
                    </li>

                    <li>
                        Sportsmanship
                    </li>

                </ul>

            </div>

        `

        }

    };


    /* =========================================================
       MODAL ELEMENTS
    ========================================================= */

    const overlay =
        document.getElementById(
            "overlay"
        );


    const content =
        document.getElementById(
            "content"
        );


    const title =
        document.getElementById(
            "title"
        );


    const subtitle =
        document.getElementById(
            "subtitle"
        );


    const bigIcon =
        document.getElementById(
            "bigIcon"
        );


    /* =========================================================
       OPEN MODAL
    ========================================================= */

    document
        .querySelectorAll(".button")
        .forEach(
            btn => {

                btn.addEventListener(
                    "click",
                    () => {

                        const d =
                            data[
                                btn.dataset.key
                            ];


                        if (!d) {

                            return;

                        }


                        bigIcon.textContent =
                            d.icon;


                        title.textContent =
                            d.title;


                        subtitle.textContent =
                            d.subtitle;


                        content.innerHTML =
                            d.html;


                        overlay.classList.add(
                            "show"
                        );


                        document.body.style.overflow =
                            "hidden";

                    }
                );

            }
        );


    /* =========================================================
       CLOSE MODAL
    ========================================================= */

    function closeCard() {

        overlay.classList.remove(
            "show"
        );

        document.body.style.overflow =
            "";

    }


    document
        .getElementById("close")
        .onclick =
        closeCard;


    /* =========================================================
       CLICK OUTSIDE
    ========================================================= */

    overlay.addEventListener(
        "click",
        e => {

            if (
                e.target === overlay
            ) {

                closeCard();

            }

        }
    );


    /* =========================================================
       ESC KEY
    ========================================================= */

    document.addEventListener(
        "keydown",
        e => {

            if (
                e.key === "Escape"
            ) {

                closeCard();

            }

        }
    );


    /* =========================================================
       CLOSE MOBILE MENU
    ========================================================= */

    document
        .querySelectorAll(
            "#navMenu a"
        )
        .forEach(
            link => {

                link.addEventListener(
                    "click",
                    () => {

                        navMenu.classList.remove(
                            "active"
                        );

                        navToggle.textContent =
                            "☰";

                    }
                );

            }
        );
    </script>

</body>

</html>