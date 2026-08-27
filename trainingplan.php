<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTU Badminton Club - Training Plan</title>

    <!-- FontAwesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
    :root {
        /* Logo Brand Colors */
        --club-blue: #2FB8EC;
        --club-orange: #F58220;
        --club-yellow: #FFD242;
        --club-dark: #1E293B;

        --bg-body: #F4F7FC;
        --card-bg: #FFFFFF;
        --text-main: #2D3748;
        --text-muted: #718096;
        --border-color: #E2E8F0;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-main);
        padding: 40px 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Top Navigation & Back Button */
    .top-bar {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: #FFFFFF;
        color: var(--club-dark);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-back:hover {
        background: var(--club-blue);
        color: #FFFFFF;
        border-color: var(--club-blue);
        transform: translateX(-3px);
        box-shadow: 0 4px 12px rgba(47, 184, 236, 0.25);
    }

    /* Minimalist & Clean Header Section (No Background Box) */
    .club-banner {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 10px 0 30px;
        text-align: center;
        margin-bottom: 20px;
    }

    .logo-wrapper {
        display: inline-block;
        position: relative;
        margin-bottom: 12px;
    }

    .logo-img {
        width: 120px;
        height: 120px;
        object-fit: contain;
        border-radius: 50%;
        padding: 8px;
        background: #FFFFFF;
        /* Soft Glow Border Ring matching Logo colors */
        box-shadow: 0 0 0 3px rgba(47, 184, 236, 0.3), 0 8px 24px rgba(245, 130, 32, 0.18);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .logo-img:hover {
        transform: scale(1.05);
        box-shadow: 0 0 0 4px var(--club-orange), 0 12px 28px rgba(47, 184, 236, 0.3);
    }

    .club-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--club-orange);
        font-style: italic;
        letter-spacing: 1px;
        line-height: 1.1;
    }

    .club-sub {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--club-blue);
        margin-top: 4px;
        letter-spacing: 0.5px;
    }

    /* Section Title Header */
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .section-header h2 {
        color: var(--club-dark);
        font-size: 1.5rem;
        font-weight: 700;
    }

    .section-header i {
        color: var(--club-orange);
        font-size: 1.4rem;
    }

    /* Training Plan Cards Section */
    .plan-list {
        display: grid;
        gap: 16px;
        margin-bottom: 50px;
    }

    .plan-card {
        background: var(--card-bg);
        border-radius: 14px;
        padding: 20px 24px;
        border-left: 6px solid var(--club-blue);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .plan-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(47, 184, 236, 0.18);
        border-left-color: var(--club-orange);
    }

    .plan-info {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .plan-icon {
        width: 50px;
        height: 50px;
        background: rgba(47, 184, 236, 0.1);
        color: var(--club-blue);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .plan-card:hover .plan-icon {
        background: var(--club-orange);
        color: #FFFFFF;
    }

    .plan-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--club-dark);
    }

    .module-badge {
        background: rgba(255, 210, 66, 0.25);
        color: #B78103;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    /* Coaches Section */
    .coaches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .coach-card {
        background: var(--card-bg);
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .coach-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(245, 130, 32, 0.15);
    }

    .coach-avatar {
        width: 85px;
        height: 85px;
        background: rgba(47, 184, 236, 0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 2.2rem;
        color: var(--club-blue);
        border: 3px solid var(--club-yellow);
    }

    .coach-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--club-dark);
    }

    .coach-major {
        display: inline-block;
        margin-top: 8px;
        padding: 5px 14px;
        background: rgba(245, 130, 32, 0.1);
        color: var(--club-orange);
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
    }

    .coach-role {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 10px;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 600px) {
        .plan-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .module-badge {
            align-self: flex-end;
        }
    }
    </style>
</head>

<body>

    <div class="container">

        <!-- Top Navigation with Back Button -->
        <div class="top-bar">
            <a href="javascript:history.back()" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- Clean Header (Logo with Soft Glow) -->
        <div class="club-banner">
            <div class="logo-wrapper">
                <img src="logo.jpg" alt="MTU Badminton Club Logo" class="logo-img"
                    onerror="this.onerror=null; this.style.display='none';">
            </div>
            <div class="club-title">MTU</div>
            <div class="club-sub">Badminton Club</div>
        </div>

        <!-- Training Plan Section -->
        <div class="section-header">
            <i class="fa-solid fa-person-running"></i>
            <h2>Training Plan</h2>
        </div>

        <div class="plan-list">

            <!-- Module 1 -->
            <div class="plan-card" onclick="selectModule('Physical Conditioning & Agility')">
                <div class="plan-info">
                    <div class="plan-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="plan-title">Physical Conditioning & Agility</div>
                </div>
                <span class="module-badge">Module 1</span>
            </div>

            <!-- Module 2 -->
            <div class="plan-card" onclick="selectModule('Shadow Training & Tactics')">
                <div class="plan-info">
                    <div class="plan-icon"><i class="fa-solid fa-arrows-spin"></i></div>
                    <div class="plan-title">Shadow Training & Tactics</div>
                </div>
                <span class="module-badge">Module 2</span>
            </div>

            <!-- Module 3 -->
            <div class="plan-card" onclick="selectModule('Multi-Shuttle Technical Drills (Part 1)')">
                <div class="plan-info">
                    <div class="plan-icon"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="plan-title">Multi-Shuttle Technical Drills (Part 1)</div>
                </div>
                <span class="module-badge">Module 3</span>
            </div>

            <!-- Module 4 -->
            <div class="plan-card" onclick="selectModule('Multi-Shuttle Technical Drills (Part 2)')">
                <div class="plan-info">
                    <div class="plan-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="plan-title">Multi-Shuttle Technical Drills (Part 2)</div>
                </div>
                <span class="module-badge">Module 4</span>
            </div>

            <!-- Module 5 -->
            <div class="plan-card" onclick="selectModule('Match Play & Strategy')">
                <div class="plan-info">
                    <div class="plan-icon"><i class="fa-solid fa-trophy"></i></div>
                    <div class="plan-title">Match Play & Strategy</div>
                </div>
                <span class="module-badge">Module 5</span>
            </div>

        </div>

        <!-- Coaches Section -->
        <div class="section-header">
            <i class="fa-solid fa-user-group"></i>
            <h2>Club Coaches</h2>
        </div>

        <div class="coaches-grid">

            <!-- Coach 1 -->
            <div class="coach-card">
                <div class="coach-avatar">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
                <div class="coach-name">U Kyaw Kyaw</div>
                <div class="coach-major">Information Technology (IT)</div>
                <div class="coach-role">Head Coach</div>
            </div>

            <!-- Coach 2 -->
            <div class="coach-card">
                <div class="coach-avatar">
                    <i class="fa-solid fa-user-astronaut"></i>
                </div>
                <div class="coach-name">U Aung Aung</div>
                <div class="coach-major">Electronic Engineering (EC)</div>
                <div class="coach-role">Assistant Coach</div>
            </div>

        </div>

    </div>

    <script>
    function selectModule(title) {
        console.log("Selected Module: " + title);
    }
    </script>

</body>

</html>