<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MTU Badminton Club | Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
    :root {
        --orange: #fb8500;
        --yellow: #ffb703;
        --skyblue: #87ceeb;
        --pure-white: #ffffff;
        --text-dark: #1a252c;
        --text-muted: #546e7a;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    body {
        background: radial-gradient(circle at 15% 20%, rgba(135, 206, 235, .45), transparent 45%),
            radial-gradient(circle at 85% 75%, rgba(255, 183, 3, .25), transparent 45%),
            linear-gradient(135deg, #e0f2fe 0%, #ffffff 50%, #fff7ed 100%);
        background-attachment: fixed;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .card-container {
        width: 100%;
        max-width: 440px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(15px);
        border-radius: 26px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        padding: 40px 24px;
        box-shadow: 0 30px 90px rgba(251, 133, 0, 0.2), 0 0 30px rgba(255, 183, 3, 0.3);
        border: 2px solid var(--yellow);
    }

    .image-wrapper {
        position: relative;
        width: clamp(180px, 55vw, 240px);
        height: clamp(240px, 38vh, 320px);
        margin-bottom: 20px;
    }

    .capsule-image {
        width: 100%;
        height: 100%;
        border-radius: 999px;
        border: 3px solid var(--yellow);
        object-fit: cover;
        box-shadow: 0 10px 25px rgba(251, 133, 0, 0.2);
    }

    .welcome-content {
        text-align: center;
        color: var(--text-dark);
    }

    .welcome-title {
        font-size: clamp(20px, 5vw, 26px);
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1.2;
        margin-bottom: 10px;
        color: var(--orange);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .welcome-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 24px;
        line-height: 1.7;
        font-weight: 500;
    }

    .btn-welcome {
        background: linear-gradient(135deg, var(--yellow), var(--orange));
        color: var(--pure-white);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(251, 133, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s;
        text-decoration: none;
        letter-spacing: 1px;
    }

    .btn-welcome:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(251, 133, 0, 0.35);
    }
    </style>
</head>

<body>

    <div class="card-container">
        <div class="image-wrapper">
            <img class="capsule-image" src="../assets/images/min1.jpg" alt="Badminton Action">
        </div>
        <div class="welcome-content">
            <h1 class="welcome-title">PLAY, SMASH & WIN</h1>
            <p class="welcome-subtitle">Join the MTU Badminton Club to train, play, and connect with fellow players on
                court.</p>
            <a href="login.php" class="btn-welcome">
                Get Started <i class="fa-solid fa-circle-arrow-right"></i>
            </a>
        </div>
    </div>

</body>

</html>