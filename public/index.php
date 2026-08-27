<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

/*
|--------------------------------------------------------------------------
| GET PUBLISHED NEWS
|--------------------------------------------------------------------------
*/

$newsList = [];

$sql = "
    SELECT id, title, content, image, created_at
    FROM news
    WHERE status = 'published'
    ORDER BY created_at DESC
    LIMIT 6
";

$result = $pdo->query($sql);

if ($result) {

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

        $newsList[] = $row;

    }

}

require_once __DIR__ . "/../includes/header.php";

?>

<style>
/* =========================================================
   HERO
========================================================= */

.hero {

    height: 100vh;
    min-height: 700px;

    position: relative;

    display: flex;
    align-items: center;

    background:
        linear-gradient(90deg,
            rgba(240, 249, 255, 0.96),
            rgba(240, 249, 255, 0.80),
            rgba(240, 249, 255, 0.40)),
        url("../assets/images/hero.jpg");

    background-size: cover;
    background-position: center;

}

.hero-content {

    position: relative;

    width: 90%;
    max-width: 1200px;

    margin: auto;

    z-index: 2;

}

.hero-small {

    font-size: 11px;

    letter-spacing: 5px;

    color: var(--accent-orange);

    margin-bottom: 25px;

    font-weight: 700;

}

.hero h1 {

    font-size: clamp(55px, 9vw, 125px);

    line-height: 0.88;

    font-weight: 800;

    letter-spacing: -5px;

    color: var(--text-main);

}

.hero h1 span {

    display: block;

    font-family: 'Playfair Display', serif;

    font-style: italic;

    font-weight: 500;

    color: var(--accent-orange);

}

.hero-description {

    max-width: 520px;

    color: var(--text-muted);

    line-height: 1.8;

    margin: 35px 0;

    font-weight: 500;

}


/* =========================================================
   BUTTONS
========================================================= */

.btn {

    display: inline-flex;

    align-items: center;

    gap: 12px;

    padding: 15px 28px;

    font-size: 10px;

    letter-spacing: 2px;

    transition: 0.3s;

    cursor: pointer;

    font-weight: 700;

    border-radius: 4px;

}

.hero-buttons {

    display: flex;

    gap: 15px;

}

.btn-gradient {

    background: var(--gradient-warm);

    color: white;

    box-shadow:
        0 4px 20px rgba(234, 88, 12, 0.35);

}

.btn-gradient:hover {

    transform: translateY(-2px);

    box-shadow:
        0 6px 25px rgba(234, 88, 12, 0.45);

}

.btn-outline {

    border: 2px solid var(--accent-sky);

    color: var(--text-main);

    background: white;

}

.btn-outline:hover {

    background: var(--accent-sky);

    color: white;

    border-color: var(--accent-sky);

}


/* =========================================================
   SCROLL
========================================================= */

.scroll {

    position: absolute;

    bottom: 30px;

    left: 50%;

    transform: translateX(-50%);

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 10px;

    color: var(--text-muted);

    font-size: 8px;

    letter-spacing: 3px;

    font-weight: 600;

}

.scroll i {

    color: var(--accent-orange);

}


/* =========================================================
   ABOUT
========================================================= */

.about {

    padding: 110px 10%;

    background: var(--bg-secondary);

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 80px;

    align-items: center;

}

.section-tag {

    color: var(--accent-orange);

    font-size: 10px;

    letter-spacing: 4px;

    margin-bottom: 20px;

    font-weight: 700;

    display: inline-block;

    background: rgba(234, 88, 12, 0.1);

    padding: 6px 14px;

    border-radius: 20px;

}

.about h2 {

    font-size: clamp(38px, 5vw, 65px);

    line-height: 1;

    color: var(--text-main);

}

.about h2 span {

    display: block;

    font-family: 'Playfair Display', serif;

    font-style: italic;

    color: var(--accent-orange);

}

.about-right p {

    color: var(--text-muted);

    line-height: 1.9;

    margin-bottom: 20px;

}

.learn {

    display: inline-flex;

    gap: 15px;

    font-size: 10px;

    letter-spacing: 2px;

    margin-top: 15px;

    padding-bottom: 10px;

    border-bottom: 2px solid var(--accent-orange);

    color: var(--accent-orange);

    font-weight: 700;

    transition: 0.3s;

}

.learn:hover {

    color: var(--accent-sky);

    border-color: var(--accent-sky);

}


/* =========================================================
   ANNOUNCEMENTS
========================================================= */

.announcements {

    padding: 110px 8%;

    background: #ffffff;

}

.section-title {

    text-align: center;

    margin-bottom: 60px;

}

.section-title h2 {

    font-size: 38px;

    color: var(--text-main);

    margin-top: 10px;

}

.section-title h2 span {

    color: var(--accent-orange);

    font-family: 'Playfair Display', serif;

    font-style: italic;

}

.news-grid {

    max-width: 1200px;

    margin: auto;

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 25px;

}

.news-card {

    background: white;

    border-radius: 14px;

    overflow: hidden;

    border:
        1px solid rgba(2, 132, 199, 0.12);

    box-shadow:
        0 10px 30px rgba(2, 132, 199, 0.08);

    transition: 0.35s;

}

.news-card:hover {

    transform: translateY(-8px);

    box-shadow:
        0 18px 40px rgba(2, 132, 199, 0.15);

}

.news-image {

    width: 100%;

    height: 190px;

    object-fit: cover;

    display: block;

}

.news-image-placeholder {

    height: 190px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(135deg,
            #e0f2fe,
            #bae6fd);

    color: var(--accent-sky);

    font-size: 45px;

}

.news-body {

    padding: 25px;

}

.news-date {

    color: var(--accent-orange);

    font-size: 9px;

    letter-spacing: 2px;

    font-weight: 700;

    margin-bottom: 12px;

}

.news-body h3 {

    font-size: 17px;

    line-height: 1.4;

    color: var(--text-main);

    margin-bottom: 12px;

}

.news-body p {

    color: var(--text-muted);

    font-size: 12px;

    line-height: 1.8;

    display: -webkit-box;

    -webkit-line-clamp: 3;

    line-clamp: 3;

    -webkit-box-orient: vertical;

    overflow: hidden;

}

.news-read {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 18px;

    color: var(--accent-orange);

    font-size: 10px;

    font-weight: 800;

    letter-spacing: 1px;

    transition: 0.3s;

}

.news-read:hover {

    color: var(--accent-sky);

    gap: 12px;

}

.no-news {

    max-width: 700px;

    margin: auto;

    text-align: center;

    padding: 50px 25px;

    background: #f8fafc;

    border-radius: 15px;

    border: 1px dashed #bae6fd;

}

.no-news i {

    font-size: 40px;

    color: var(--accent-sky);

    margin-bottom: 15px;

}

.no-news h3 {

    margin-bottom: 10px;

    color: var(--text-main);

}

.no-news p {

    color: var(--text-muted);

    font-size: 13px;

}

.view-all-news {

    text-align: center;

    margin-top: 45px;

}


/* =========================================================
   JOIN CTA
========================================================= */

.join {

    min-height: 470px;

    display: flex;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 80px 20px;

    background:
        linear-gradient(rgba(12, 74, 110, 0.88),
            rgba(12, 74, 110, 0.95)),
        url("../assets/images/court2.jpg");

    background-size: cover;

    background-position: center;

    color: white;

}

.join-content {

    max-width: 850px;

}

.join .section-tag {

    background:
        rgba(245, 158, 11, 0.2);

    color: var(--accent-yellow);

}

.join h2 {

    font-size: clamp(40px, 6vw, 75px);

    line-height: 1;

    color: white;

}

.join h2 span {

    display: block;

    color: var(--accent-yellow);

    font-family: 'Playfair Display', serif;

    font-style: italic;

}

.join p {

    color: #e0f2fe;

    margin: 25px 0;

}


/* =========================================================
   STEPS
========================================================= */

.steps-section {

    padding: 110px 8%;

    background: var(--bg-dark);

}

.steps {

    max-width: 1100px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: center;

}

.step {

    width: 250px;

    text-align: center;

    background:
        rgba(255, 255, 255, 0.7);

    padding: 35px 20px;

    border-radius: 12px;

    box-shadow:
        0 10px 30px rgba(2, 132, 199, 0.08);

    border:
        1px solid rgba(255, 255, 255, 0.9);

    position: relative;

    transition: 0.3s;

}

.step:hover {

    transform: translateY(-5px);

    background: white;

}

.step-number {

    font-family: 'Playfair Display', serif;

    font-size: 45px;

    font-style: italic;

    color: var(--accent-orange);

    margin-bottom: 10px;

}

.step-icon {

    width: 75px;

    height: 75px;

    background: var(--gradient-warm);

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: center;

    transform: rotate(45deg);

    margin-bottom: 35px;

    color: white;

    box-shadow:
        0 6px 15px rgba(234, 88, 12, 0.3);

    border-radius: 6px;

}

.step-icon i {

    transform: rotate(-45deg);

    font-size: 22px;

}

.step h3 {

    font-size: 12px;

    letter-spacing: 3px;

    margin-bottom: 15px;

    color: var(--text-main);

}

.step p {

    color: var(--text-muted);

    font-size: 11px;

    line-height: 1.8;

}

.arrow {

    color: var(--accent-orange);

    margin: 0 20px;

    font-size: 20px;

}


/* =========================================================
   FINAL CTA
========================================================= */

.final {

    min-height: 470px;

    display: flex;

    justify-content: center;

    align-items: center;

    text-align: center;

    background:
        linear-gradient(rgba(12, 74, 110, 0.85),
            rgba(12, 74, 110, 0.95)),
        url("../assets/images/player.jpg");

    background-size: cover;

    background-position: center;

    color: white;

}

.final p {

    font-size: 9px;

    letter-spacing: 5px;

    color: var(--accent-yellow);

    font-weight: 700;

}

.final h2 {

    font-size: clamp(45px, 7vw, 90px);

    margin: 15px 0 30px;

    color: white;

}

.final h2 span {

    color: var(--accent-yellow);

    font-family: 'Playfair Display', serif;

    font-style: italic;

}

.final .btn-outline {

    background: white;

    color: var(--text-main);

    border-color: white;

}


/* =========================================================
   FOOTER
========================================================= */

footer {

    background: #082f49;

    color: white;

    padding: 80px 8% 30px;

}

.footer-content {

    max-width: 1200px;

    margin: auto;

    display: grid;

    grid-template-columns: 2fr 1.5fr 2fr;

    gap: 50px;

    padding-bottom: 50px;

    border-bottom:
        1px solid rgba(255, 255, 255, 0.1);

}

.footer-brand h2 {

    font-size: 28px;

    letter-spacing: 3px;

    color: white;

}

.footer-brand h3 {

    font-size: 10px;

    letter-spacing: 3px;

    color: var(--accent-orange);

    margin-bottom: 15px;

}

.footer-brand p {

    color: #cbd5e1;

    font-size: 13px;

    line-height: 1.7;

}

.footer-column h3 {

    font-size: 12px;

    letter-spacing: 2px;

    color: var(--accent-yellow);

    margin-bottom: 20px;

}

.footer-column a,
.footer-column p {

    display: block;

    color: #cbd5e1;

    font-size: 13px;

    margin-bottom: 12px;

    transition: 0.3s;

}

.footer-column a:hover {

    color: var(--accent-orange);

    transform: translateX(4px);

}

.footer-column p i {

    margin-right: 8px;

}

.social {

    display: flex;

    gap: 12px;

    margin-top: 20px;

}

.social a {

    width: 38px;

    height: 38px;

    background:
        rgba(255, 255, 255, 0.08);

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    color: white;

    transition: 0.3s;

}

.social a:hover {

    background: white;

    color: var(--accent-orange);

    transform: translateY(-3px);

}

.copyright {

    text-align: center;

    padding-top: 30px;

    font-size: 11px;

    color: #94a3b8;

    letter-spacing: 1px;

}


/* =========================================================
   =========================================================
   MTU AI CHATBOT - FIXED POSITION
   =========================================================
========================================================= */


/*
|--------------------------------------------------------------------------
| MAIN CHATBOT CONTAINER
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| The chatbot is fixed to the browser viewport.
| It does NOT depend on footer, hero, section, etc.
|
*/

.ai-chatbot {

    position: fixed !important;

    right: 24px !important;

    bottom: 24px !important;

    width: auto;

    height: auto;

    z-index: 2147483000 !important;

    font-family: inherit;

    pointer-events: none;

}


/*
|--------------------------------------------------------------------------
| CHAT WINDOW
|--------------------------------------------------------------------------
*/

.ai-chat-window {

    position: absolute;

    right: 0;

    bottom: 82px;

    width: 390px;

    /*
    | Never allow the window to become taller
    | than the available browser screen.
    */

    height: min(560px,
            calc(100dvh - 125px));

    min-height: 400px;

    max-width: calc(100vw - 32px);

    background: #ffffff;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 24px 70px rgba(15, 23, 42, 0.28),
        0 8px 25px rgba(109, 40, 217, 0.12);

    border:
        1px solid rgba(109, 40, 217, 0.12);

    display: flex;

    flex-direction: column;

    opacity: 0;

    visibility: hidden;

    pointer-events: none;

    transform:
        translateY(12px) scale(0.96);

    transform-origin:
        bottom right;

    transition:
        opacity 0.22s ease,
        transform 0.22s ease,
        visibility 0.22s ease;

}


/*
|--------------------------------------------------------------------------
| OPEN CHAT WINDOW
|--------------------------------------------------------------------------
*/

.ai-chat-window.active {

    opacity: 1;

    visibility: visible;

    pointer-events: auto;

    transform:
        translateY(0) scale(1);

}


/*
|--------------------------------------------------------------------------
| CHAT HEADER
|--------------------------------------------------------------------------
*/

.ai-chat-header {

    flex-shrink: 0;

    min-height: 76px;

    padding: 15px 16px;

    background:
        linear-gradient(135deg,
            #8b5cf6 0%,
            #7c3aed 50%,
            #6d28d9 100%);

    color: white;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


/*
|--------------------------------------------------------------------------
| CHAT TITLE
|--------------------------------------------------------------------------
*/

.ai-chat-title {

    display: flex;

    align-items: center;

    gap: 11px;

    min-width: 0;

}

.ai-chat-avatar {

    width: 42px;

    height: 42px;

    min-width: 42px;

    border-radius: 14px;

    background:
        rgba(255, 255, 255, 0.18);

    border:
        1px solid rgba(255, 255, 255, 0.22);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    box-shadow:
        inset 0 0 15px rgba(255, 255, 255, 0.08);

}

.ai-chat-title h3 {

    margin: 0;

    font-size: 14px;

    font-weight: 700;

    line-height: 1.3;

}

.ai-chat-title span {

    display: block;

    margin-top: 4px;

    font-size: 10px;

    opacity: 0.82;

    white-space: nowrap;

}


/*
|--------------------------------------------------------------------------
| CLOSE BUTTON
|--------------------------------------------------------------------------
*/

.ai-chat-close {

    width: 34px;

    height: 34px;

    min-width: 34px;

    border: none;

    background:
        rgba(255, 255, 255, 0.13);

    color: white;

    border-radius: 50%;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    transition: 0.2s;

}

.ai-chat-close:hover {

    background:
        rgba(255, 255, 255, 0.25);

    transform:
        rotate(90deg);

}


/*
|--------------------------------------------------------------------------
| CHAT MESSAGES
|--------------------------------------------------------------------------
*/

.ai-chat-messages {

    flex: 1;

    min-height: 0;

    padding: 18px;

    overflow-y: auto;

    background:
        linear-gradient(180deg,
            #f8fafc 0%,
            #f5f3ff 100%);

    display: flex;

    flex-direction: column;

    gap: 12px;

    scroll-behavior: smooth;

}


/*
|--------------------------------------------------------------------------
| CUSTOM SCROLLBAR
|--------------------------------------------------------------------------
*/

.ai-chat-messages::-webkit-scrollbar {

    width: 6px;

}

.ai-chat-messages::-webkit-scrollbar-track {

    background: transparent;

}

.ai-chat-messages::-webkit-scrollbar-thumb {

    background:
        rgba(109, 40, 217, 0.25);

    border-radius: 20px;

}

.ai-chat-messages::-webkit-scrollbar-thumb:hover {

    background:
        rgba(109, 40, 217, 0.45);

}


/*
|--------------------------------------------------------------------------
| CHAT MESSAGE
|--------------------------------------------------------------------------
*/

.ai-message {

    max-width: 82%;

    padding: 11px 14px;

    border-radius: 14px;

    font-size: 12px;

    line-height: 1.65;

    word-wrap: break-word;

    overflow-wrap: anywhere;

    white-space: pre-wrap;

    animation:
        aiMessageIn 0.2s ease;

}

@keyframes aiMessageIn {

    from {

        opacity: 0;

        transform:
            translateY(5px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/*
|--------------------------------------------------------------------------
| BOT MESSAGE
|--------------------------------------------------------------------------
*/

.ai-message.bot {

    align-self: flex-start;

    background: #ffffff;

    color: #334155;

    border:
        1px solid #e2e8f0;

    border-bottom-left-radius: 4px;

    box-shadow:
        0 3px 10px rgba(15, 23, 42, 0.04);

}


/*
|--------------------------------------------------------------------------
| USER MESSAGE
|--------------------------------------------------------------------------
*/

.ai-message.user {

    align-self: flex-end;

    background:
        linear-gradient(135deg,
            #8b5cf6,
            #6d28d9);

    color: white;

    border-bottom-right-radius: 4px;

    box-shadow:
        0 5px 15px rgba(109, 40, 217, 0.18);

}


/*
|--------------------------------------------------------------------------
| TYPING INDICATOR
|--------------------------------------------------------------------------
*/

.ai-typing {

    display: flex;

    align-items: center;

    gap: 4px;

    width: fit-content;

    min-width: 48px;

    padding: 12px 14px;

}

.ai-typing span {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: #8b5cf6;

    animation:
        aiTyping 1.2s infinite;

}

.ai-typing span:nth-child(2) {

    animation-delay:
        0.15s;

}

.ai-typing span:nth-child(3) {

    animation-delay:
        0.3s;

}

@keyframes aiTyping {

    0%,
    60%,
    100% {

        transform:
            translateY(0);

        opacity: 0.35;

    }

    30% {

        transform:
            translateY(-5px);

        opacity: 1;

    }

}


/*
|--------------------------------------------------------------------------
| INPUT AREA
|--------------------------------------------------------------------------
*/

.ai-chat-input {

    flex-shrink: 0;

    padding: 12px;

    background: #ffffff;

    border-top:
        1px solid #e2e8f0;

    display: flex;

    align-items: center;

    gap: 8px;

}


/*
|--------------------------------------------------------------------------
| TEXTAREA
|--------------------------------------------------------------------------
*/

.ai-chat-input textarea {

    flex: 1;

    width: 100%;

    resize: none;

    height: 44px;

    max-height: 90px;

    padding: 12px 13px;

    border:
        1px solid #e2e8f0;

    border-radius: 13px;

    outline: none;

    font-family: inherit;

    font-size: 12px;

    line-height: 1.4;

    color: #334155;

    background: #f8fafc;

    transition: 0.2s;

}

.ai-chat-input textarea::placeholder {

    color: #94a3b8;

}

.ai-chat-input textarea:focus {

    background: #ffffff;

    border-color: #8b5cf6;

    box-shadow:
        0 0 0 3px rgba(139, 92, 246, 0.10);

}


/*
|--------------------------------------------------------------------------
| SEND BUTTON
|--------------------------------------------------------------------------
*/

.ai-send-button {

    width: 44px;

    height: 44px;

    min-width: 44px;

    border: none;

    border-radius: 13px;

    background:
        linear-gradient(135deg,
            #8b5cf6,
            #6d28d9);

    color: white;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        opacity 0.2s ease;

    box-shadow:
        0 5px 15px rgba(109, 40, 217, 0.25);

}

.ai-send-button:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 20px rgba(109, 40, 217, 0.32);

}

.ai-send-button:active {

    transform:
        translateY(0) scale(0.96);

}

.ai-send-button:disabled {

    opacity: 0.5;

    cursor: not-allowed;

    transform: none;

    box-shadow: none;

}


/*
|--------------------------------------------------------------------------
| FLOATING BUTTON
|--------------------------------------------------------------------------
*/

.ai-chat-button {

    position: relative;

    width: 60px;

    height: 60px;

    border: none;

    border-radius: 50%;

    background:
        linear-gradient(135deg,
            #8b5cf6 0%,
            #7c3aed 50%,
            #6d28d9 100%);

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;

    cursor: pointer;

    pointer-events: auto;

    box-shadow:
        0 10px 30px rgba(109, 40, 217, 0.35);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;

    z-index: 2;

}


/*
|--------------------------------------------------------------------------
| BUTTON HOVER
|--------------------------------------------------------------------------
*/

.ai-chat-button:hover {

    transform:
        translateY(-4px) scale(1.05);

    box-shadow:
        0 15px 35px rgba(109, 40, 217, 0.45);

}


/*
|--------------------------------------------------------------------------
| BUTTON ACTIVE
|--------------------------------------------------------------------------
*/

.ai-chat-button:active {

    transform:
        scale(0.95);

}


/*
|--------------------------------------------------------------------------
| BUTTON PULSE
|--------------------------------------------------------------------------
*/

.ai-chat-button::before {

    content: "";

    position: absolute;

    inset: 0;

    border-radius: 50%;

    background:
        rgba(139, 92, 246, 0.35);

    animation:
        aiPulse 2.2s infinite;

    z-index: -1;

}

@keyframes aiPulse {

    0% {

        transform:
            scale(1);

        opacity: 0.65;

    }

    70% {

        transform:
            scale(1.45);

        opacity: 0;

    }

    100% {

        transform:
            scale(1.45);

        opacity: 0;

    }

}


/*
|--------------------------------------------------------------------------
| BUTTON ICON
|--------------------------------------------------------------------------
*/

.ai-chat-button i {

    position: relative;

    z-index: 2;

}


/* =========================================================
   DESKTOP SCREEN FIX
========================================================= */

@media (min-width: 901px) {

    .ai-chatbot {

        right: 24px !important;

        bottom: 24px !important;

    }

    .ai-chat-window {

        right: 0;

        bottom: 78px;

        width: 390px;

        max-width: 390px;

    }

}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 900px) {

    nav {

        position: absolute;

        top: 85px;

        right: 5%;

        width: 250px;

        padding: 25px;

        background: white;

        display: none;

        flex-direction: column;

        align-items: flex-start;

        gap: 22px;

        box-shadow:
            0 10px 30px rgba(2, 132, 199, 0.15);

        border-radius: 8px;

    }

    nav.active {

        display: flex;

    }

    .menu-btn {

        display: block;

    }

    .about {

        grid-template-columns: 1fr;

        gap: 35px;

    }

    .news-grid {

        grid-template-columns:
            1fr 1fr;

    }

    .steps {

        flex-direction: column;

        gap: 45px;

    }

    .arrow {

        transform:
            rotate(90deg);

    }

    .footer-content {

        grid-template-columns:
            1fr 1fr;

    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .hero {

        min-height: 650px;

    }

    .hero h1 {

        letter-spacing: -2px;

    }

    .hero-buttons {

        flex-direction: column;

        align-items: flex-start;

    }

    .news-grid {

        grid-template-columns: 1fr;

    }

    .footer-content {

        grid-template-columns: 1fr;

        gap: 35px;

    }

    .announcements {

        padding: 80px 6%;

    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE CHATBOT
    |--------------------------------------------------------------------------
    */

    .ai-chatbot {

        right: 16px !important;

        bottom: 16px !important;

    }

    .ai-chat-window {

        right: 0;

        bottom: 70px;

        width:
            calc(100vw - 32px);

        max-width:
            calc(100vw - 32px);

        height:
            min(500px,
                calc(100dvh - 105px));

        min-height: 360px;

        border-radius: 18px;

    }

    .ai-chat-header {

        min-height: 70px;

        padding: 13px 14px;

    }

    .ai-chat-avatar {

        width: 38px;

        height: 38px;

        min-width: 38px;

        border-radius: 12px;

        font-size: 16px;

    }

    .ai-chat-title h3 {

        font-size: 13px;

    }

    .ai-chat-title span {

        font-size: 9px;

        max-width: 190px;

        overflow: hidden;

        text-overflow: ellipsis;

    }

    .ai-chat-messages {

        padding: 14px;

    }

    .ai-chat-input {

        padding: 10px;

    }

    .ai-chat-button {

        width: 56px;

        height: 56px;

        font-size: 21px;

    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media (max-width: 380px) {

    .ai-chatbot {

        right: 12px !important;

        bottom: 12px !important;

    }

    .ai-chat-window {

        width:
            calc(100vw - 24px);

        max-width:
            calc(100vw - 24px);

        right: 0;

    }

}


/* =========================================================
   REDUCE MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {

    .ai-chat-button::before,
    .ai-typing span {

        animation: none;

    }

    .ai-chat-window,
    .ai-message,
    .ai-chat-button {

        transition: none;

    }

}
</style>


</head>

<body>


    <!-- =========================================================
     HERO
========================================================= -->

    <section class="hero" id="home">

        <div class="hero-content">

            <p class="hero-small">
                MANDALAY TECHNOLOGICAL UNIVERSITY
            </p>

            <h1>

                MTU

                <span>
                    BADMINTON
                </span>

                CLUB

            </h1>

            <p class="hero-description">

                Smash your limits.
                Build your skills.
                Connect with the MTU badminton community
                in style.

            </p>

            <div class="hero-buttons">

                <a href="getstarted.php" class="btn btn-gradient">

                    JOIN THE CLUB

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

                <a href="announcement.php" class="btn btn-outline">

                    EXPLORE

                </a>

            </div>

        </div>


        <div class="scroll">

            <span>
                SCROLL TO DISCOVER
            </span>

            <i class="fa-solid fa-chevron-down"></i>

        </div>

    </section>


    <!-- =========================================================
     ABOUT
========================================================= -->

    <section class="about" id="about">

        <div class="about-left">

            <span class="section-tag">
                WELCOME TO MTU
            </span>

            <h2>

                WHERE

                <span>
                    PASSION
                </span>

                MEETS THE COURT

            </h2>

        </div>


        <div class="about-right">

            <p>

                MTU Badminton Club is a vibrant community
                for students who love badminton and want
                to improve their skills, fitness, and teamwork.

            </p>

            <p>

                Whether you are a beginner or an experienced
                player, everyone is welcome to join our
                energetic club environment.

            </p>

            <a href="getstarted.php" class="learn">

                BECOME A MEMBER

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>

    </section>


    <!-- =========================================================
     ANNOUNCEMENTS
========================================================= -->

    <section class="announcements" id="announcements">

        <div class="section-title">

            <span class="section-tag">
                STAY UPDATED
            </span>

            <h2>

                LATEST

                <span>
                    ANNOUNCEMENTS
                </span>

            </h2>

        </div>


        <?php if (count($newsList) > 0): ?>

        <div class="news-grid">

            <?php foreach ($newsList as $news): ?>

            <?php

                $newsId =
                    (int)$news['id'];

                $title =
                    htmlspecialchars(
                        $news['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    );

                $content =
                    htmlspecialchars(
                        $news['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    );

                $date =
                    date(
                        'd M Y',
                        strtotime(
                            $news['created_at']
                        )
                    );

                $image =
                    $news['image'];

                ?>

            <article class="news-card">

                <?php if (!empty($image)): ?>

                <img src="../uploads/news/<?= htmlspecialchars(
                                $image,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" alt="<?= $title ?>" class="news-image">

                <?php else: ?>

                <div class="news-image-placeholder">

                    <i class="fa-solid fa-bullhorn"></i>

                </div>

                <?php endif; ?>


                <div class="news-body">

                    <div class="news-date">

                        <i class="fa-regular fa-calendar"></i>

                        <?= $date ?>

                    </div>


                    <h3>
                        <?= $title ?>
                    </h3>


                    <p>
                        <?= $content ?>
                    </p>


                    <a href="announcement.php?id=<?= $newsId ?>" class="news-read">

                        READ MORE

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </div>

            </article>

            <?php endforeach; ?>

        </div>


        <div class="view-all-news">

            <a href="announcement.php" class="btn btn-gradient">

                VIEW ALL ANNOUNCEMENTS

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


        <?php else: ?>

        <div class="no-news">

            <i class="fa-solid fa-bullhorn"></i>

            <h3>
                No Announcements Yet
            </h3>

            <p>

                There are currently no published announcements.
                Please check again later.

            </p>

        </div>

        <?php endif; ?>

    </section>


    <!-- =========================================================
     JOIN CTA
========================================================= -->

    <section class="join">

        <div class="join-content">

            <span class="section-tag">
                YOUR GAME STARTS HERE
            </span>

            <h2>

                YOU WANT TO

                <span>
                    PLAY BADMINTON?
                </span>

            </h2>

            <p>

                Register now and become an active member
                of MTU Badminton Club.

            </p>

            <a href="register.php" class="btn btn-gradient">

                REGISTER NOW

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>

    </section>


    <!-- =========================================================
     3 STEPS
========================================================= -->

    <section class="steps-section" id="steps">

        <div class="section-title">

            <span class="section-tag">
                QUICK & EASY
            </span>

            <h2>

                3 STEPS TO

                <span>
                    JOIN MTU BADMINTON
                </span>

            </h2>

        </div>


        <div class="steps">

            <div class="step">

                <div class="step-number">
                    01
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-user-plus"></i>

                </div>

                <h3>
                    REGISTER
                </h3>

                <p>

                    Fill in your student information
                    and create your membership.

                </p>

            </div>


            <div class="arrow">

                <i class="fa-solid fa-chevron-right"></i>

            </div>


            <div class="step">

                <div class="step-number">
                    02
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-calendar-check"></i>

                </div>

                <h3>
                    READ
                </h3>

                <p>

                    Read the club guidelines
                    and training schedule.

                </p>

            </div>


            <div class="arrow">

                <i class="fa-solid fa-chevron-right"></i>

            </div>


            <div class="step">

                <div class="step-number">
                    03
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-bolt"></i>

                </div>

                <h3>
                    PLAY
                </h3>

                <p>

                    Join the club and start playing
                    with fellow MTU students.

                </p>

            </div>

        </div>

    </section>


    <!-- =========================================================
     FINAL CTA
========================================================= -->

    <section class="final">

        <div>

            <p>
                READY TO MAKE YOUR MOVE?
            </p>

            <h2>

                FIND YOUR

                <span>
                    GAME.
                </span>

            </h2>

            <a href="login.php" class="btn btn-outline">

                JOIN MTU BADMINTON

            </a>

        </div>

    </section>


    <!-- =========================================================
     FOOTER
========================================================= -->

    <?php

require_once __DIR__ . "/../includes/footer.php";

?>


    <!-- =========================================================
     AI CHATBOT
========================================================= -->

    <div class="ai-chatbot" id="aiChatbot">


        <!-- =====================================================
         CHAT WINDOW
    ====================================================== -->

        <div class="ai-chat-window" id="aiChatWindow">


            <!-- HEADER -->

            <div class="ai-chat-header">

                <div class="ai-chat-title">

                    <div class="ai-chat-avatar">

                        <i class="fa-solid fa-robot"></i>

                    </div>

                    <div>

                        <h3>
                            MTU AI Assistant
                        </h3>

                        <span>
                            Ask about MTU Badminton Club
                        </span>

                    </div>

                </div>


                <button type="button" class="ai-chat-close" id="aiChatClose" aria-label="Close AI chatbot">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>


            <!-- MESSAGES -->

            <div class="ai-chat-messages" id="aiChatMessages">
                <div class="ai-message bot">
                    🏸 Smash! Hello, I'm SmashBot, your MTU Badminton Club guide.
                    <br>
                    Serve me your questions about club membership, training, activities, or announcements, and I'll
                    clear them right up!
                </div>
            </div>


            <!-- INPUT -->

            <div class="ai-chat-input">

                <textarea id="aiChatInput" placeholder="Ask me something..." maxlength="2000"></textarea>


                <button type="button" class="ai-send-button" id="aiSendButton" aria-label="Send message">

                    <i class="fa-solid fa-paper-plane"></i>

                </button>

            </div>

        </div>


        <!-- =====================================================
         FLOATING AI BUTTON
    ====================================================== -->

        <button type="button" class="ai-chat-button" id="aiChatButton" aria-label="Open MTU AI Assistant">

            <i class="fa-solid fa-robot"></i>

        </button>

    </div>


    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

    <script>
    /* =========================================================
   NAVIGATION MENU
========================================================= */

    function toggleMenu() {

        const menu =
            document.getElementById("navMenu");

        if (menu) {

            menu.classList.toggle("active");

        }

    }


    /* =========================================================
       NAVBAR SCROLL
    ========================================================= */

    window.addEventListener(
        "scroll",
        function() {

            const navbar =
                document.getElementById("navbar");

            if (!navbar) {

                return;

            }

            if (window.scrollY > 50) {

                navbar.classList.add("scrolled");

            } else {

                navbar.classList.remove("scrolled");

            }

        }
    );


    /* =========================================================
       CLOSE MOBILE NAVIGATION
    ========================================================= */

    document
        .querySelectorAll("#navMenu a")
        .forEach(function(link) {

            link.addEventListener(
                "click",
                function() {

                    const menu =
                        document.getElementById("navMenu");

                    if (menu) {

                        menu.classList.remove("active");

                    }

                }
            );

        });


    /* =========================================================
       MTU AI CHATBOT
    ========================================================= */

    const aiChatButton =
        document.getElementById("aiChatButton");

    const aiChatWindow =
        document.getElementById("aiChatWindow");

    const aiChatClose =
        document.getElementById("aiChatClose");

    const aiChatInput =
        document.getElementById("aiChatInput");

    const aiSendButton =
        document.getElementById("aiSendButton");

    const aiChatMessages =
        document.getElementById("aiChatMessages");


    /* =========================================================
       CHECK CHATBOT ELEMENTS
    ========================================================= */

    if (
        aiChatButton &&
        aiChatWindow &&
        aiChatClose &&
        aiChatInput &&
        aiSendButton &&
        aiChatMessages
    ) {


        /* =====================================================
           OPEN CHAT
        ====================================================== */

        aiChatButton.addEventListener(
            "click",
            function() {

                aiChatWindow.classList.add("active");

                setTimeout(
                    function() {

                        aiChatInput.focus();

                    },
                    200
                );

            }
        );


        /* =====================================================
           CLOSE CHAT
        ====================================================== */

        aiChatClose.addEventListener(
            "click",
            function() {

                aiChatWindow.classList.remove(
                    "active"
                );

            }
        );


        /* =====================================================
           ADD MESSAGE
        ====================================================== */

        function addAIMessage(
            message,
            type
        ) {

            const messageElement =
                document.createElement("div");

            messageElement.className =
                "ai-message " + type;

            /*
            |------------------------------------------------------
            | textContent prevents AI/user messages from being
            | interpreted as HTML.
            |------------------------------------------------------
            */

            messageElement.textContent =
                message;

            aiChatMessages.appendChild(
                messageElement
            );

            aiChatMessages.scrollTop =
                aiChatMessages.scrollHeight;

        }


        /* =====================================================
           TYPING INDICATOR
        ====================================================== */

        function showAITyping() {

            const typing =
                document.createElement("div");

            typing.className =
                "ai-message bot ai-typing";

            typing.id =
                "aiTyping";

            typing.innerHTML = `
            <span></span>
            <span></span>
            <span></span>
        `;

            aiChatMessages.appendChild(
                typing
            );

            aiChatMessages.scrollTop =
                aiChatMessages.scrollHeight;

        }


        function removeAITyping() {

            const typing =
                document.getElementById(
                    "aiTyping"
                );

            if (typing) {

                typing.remove();

            }

        }


        /* =====================================================
           SEND AI MESSAGE
        ====================================================== */

        async function sendAIMessage() {

            const message =
                aiChatInput.value.trim();


            /*
            |------------------------------------------------------
            | Don't send empty messages
            |------------------------------------------------------
            */

            if (!message) {

                return;

            }


            /*
            |------------------------------------------------------
            | Show user's message
            |------------------------------------------------------
            */

            addAIMessage(
                message,
                "user"
            );


            /*
            |------------------------------------------------------
            | Clear input
            |------------------------------------------------------
            */

            aiChatInput.value = "";


            /*
            |------------------------------------------------------
            | Disable send button
            |------------------------------------------------------
            */

            aiSendButton.disabled =
                true;


            /*
            |------------------------------------------------------
            | Show typing
            |------------------------------------------------------
            */

            showAITyping();


            try {


                /* =================================================
                   SEND TO PHP BACKEND
                ================================================== */

                const response =
                    await fetch(
                        "../ai/chat.php", {

                            method: "POST",

                            headers: {

                                "Content-Type": "application/json"

                            },

                            body: JSON.stringify({

                                message: message

                            })

                        }
                    );


                /* =================================================
                   GET RAW RESPONSE
                ================================================== */

                const responseText =
                    await response.text();

                console.log(
                    "AI Server Response:",
                    responseText
                );


                /* =================================================
                   PARSE JSON
                ================================================== */

                let data;

                try {

                    data =
                        JSON.parse(
                            responseText
                        );

                } catch (jsonError) {

                    console.error(
                        "Invalid AI JSON:",
                        responseText
                    );

                    throw new Error(
                        "Invalid response from AI server."
                    );

                }


                /* =================================================
                   REMOVE TYPING
                ================================================== */

                removeAITyping();


                /* =================================================
                   CHECK BACKEND SUCCESS
                ================================================== */

                if (!data.success) {

                    throw new Error(
                        data.error ||
                        "AI service is unavailable."
                    );

                }


                /* =================================================
                   DISPLAY AI ANSWER
                ================================================== */

                addAIMessage(
                    data.answer,
                    "bot"
                );


            } catch (error) {

                removeAITyping();

                console.error(
                    "MTU AI Error:",
                    error
                );


                /*
                |--------------------------------------------------
                | Display friendly error
                |--------------------------------------------------
                */

                addAIMessage(
                    "Sorry, I'm unable to answer right now. Please try again later.",
                    "bot"
                );

            } finally {

                aiSendButton.disabled =
                    false;

                aiChatInput.focus();

            }

        }


        /* =====================================================
           SEND BUTTON
        ====================================================== */

        aiSendButton.addEventListener(
            "click",
            sendAIMessage
        );


        /* =====================================================
           ENTER TO SEND
        ====================================================== */

        aiChatInput.addEventListener(
            "keydown",
            function(event) {

                /*
                |--------------------------------------------------
                | Enter = send
                | Shift + Enter = new line
                |--------------------------------------------------
                */

                if (
                    event.key === "Enter" &&
                    !event.shiftKey
                ) {

                    event.preventDefault();

                    sendAIMessage();

                }

            }
        );


        /* =====================================================
           ESCAPE TO CLOSE
        ====================================================== */

        document.addEventListener(
            "keydown",
            function(event) {

                if (
                    event.key === "Escape" &&
                    aiChatWindow.classList.contains("active")
                ) {

                    aiChatWindow.classList.remove(
                        "active"
                    );

                }

            }
        );


        /* =====================================================
           CLICK OUTSIDE TO CLOSE
        ====================================================== */

        document.addEventListener(
            "click",
            function(event) {

                if (
                    !aiChatWindow.classList.contains("active")
                ) {

                    return;

                }

                const clickedInsideChat =
                    aiChatWindow.contains(event.target);

                const clickedButton =
                    aiChatButton.contains(event.target);

                if (
                    !clickedInsideChat &&
                    !clickedButton
                ) {

                    aiChatWindow.classList.remove(
                        "active"
                    );

                }

            }
        );

    }
    </script>


</body>

</html>