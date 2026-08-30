<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MTU Badminton Club</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;1,500&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
    /* =========================================================
       GLOBAL
    ========================================================= */

    :root {
        --bg-primary: #f0f9ff;
        --bg-secondary: #e0f2fe;
        --bg-dark: #bae6fd;

        --text-main: #0c4a6e;
        --text-muted: #334155;

        --accent-sky: #0284c7;
        --accent-sky-hover: #0369a1;

        --accent-yellow: #f59e0b;
        --accent-orange: #ea580c;

        --gradient-warm:
            linear-gradient(135deg,
                #f59e0b 0%,
                #ea580c 100%);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background: var(--bg-primary);
        color: var(--text-main);
        overflow-x: hidden;
    }

    a {
        text-decoration: none;
        color: inherit;
    }


    /* =========================================================
       NAVBAR
    ========================================================= */

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 85px;

        padding: 0 6%;

        display: flex;
        justify-content: space-between;
        align-items: center;

        z-index: 999;

        transition: 0.4s;

        background: rgba(240, 249, 255, 0.90);
        backdrop-filter: blur(12px);

        border-bottom:
            1px solid rgba(2, 132, 199, 0.15);
    }

    .navbar.scrolled {
        background: rgba(240, 249, 255, 0.98);

        box-shadow:
            0 4px 20px rgba(2, 132, 199, 0.12);
    }


    /* =========================================================
       LOGO
    ========================================================= */

    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo-img {
        width: 45px;
        height: 45px;

        object-fit: contain;

        border-radius: 50%;

        background: white;
        padding: 2px;

        box-shadow:
            0 4px 15px rgba(2, 132, 199, 0.2);
    }

    .logo-text h2 {
        font-size: 21px;
        letter-spacing: 3px;
        color: var(--text-main);
    }

    .logo-text span {
        font-size: 7px;
        color: var(--accent-orange);
        letter-spacing: 2px;
        font-weight: 700;
    }


    /* =========================================================
       NAVIGATION
    ========================================================= */

    nav {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    nav a {
        font-size: 10px;
        letter-spacing: 2px;

        color: var(--text-muted);

        font-weight: 600;

        transition: 0.3s;
    }

    nav a:hover {
        color: var(--accent-orange);
    }

    .register-nav {
        padding: 13px 22px;

        background: var(--gradient-warm);

        color: white !important;

        border-radius: 4px;

        box-shadow:
            0 4px 15px rgba(234, 88, 12, 0.3);

        transition: 0.3s;
    }

    .register-nav:hover {
        transform: translateY(-2px);

        box-shadow:
            0 6px 20px rgba(234, 88, 12, 0.4);
    }

    .menu-btn {
        display: none;

        background: none;
        border: none;

        color: var(--text-main);

        font-size: 25px;

        cursor: pointer;
    }
    </style>

    <header class="navbar" id="navbar">

        <div class="logo">
            <img src="../assets/images/mtubd.jpg" alt="MTU Logo" class="logo-img">

            <div class="logo-text">
                <h2>MTU</h2>
                <span>BADMINTON CLUB</span>
            </div>
        </div>

        <nav id="navMenu">

            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active-link' : '' ?>">
                HOME
            </a>

            <a href="index.php#about">
                ABOUT
            </a>

            <a href="../public/activities.php"
                class="<?= $currentPage === '../public/activities.php' ? 'active-link' : '' ?>">
                ACTIVITIES
            </a>

            <a href="index.php#steps">
                HOW TO JOIN
            </a>

            <a href="../public/member.php" class="<?= $currentPage === '../public/member.php' ? 'active-link' : '' ?>">
                MEMBERS
            </a>

            <a href="index.php#contact">
                CONTACT
            </a>

            <a href="../public/register.php" class="register-nav">
                REGISTER
            </a>

        </nav>

        <button class="menu-btn" onclick="toggleMenu()" aria-label="Open menu">
            <i class="fa-solid fa-bars"></i>
        </button>

    </header>
