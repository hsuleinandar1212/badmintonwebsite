<?php

/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../includes/auth.php";

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../config/db.php";

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../includes/csrf.php";

/*
|--------------------------------------------------------------------------
| Escape Output
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
| Get News Posts
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        title,
        content,
        image,
        created_at
    FROM news
    ORDER BY id DESC
";

$result = $pdo->query($sql);
$newsPosts = $result->fetchAll(PDO::FETCH_ASSOC);
$postCount = count($newsPosts);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Manage News - MTU Badminton Club
    </title>


    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">


    <style>
    /* =========================================================
           ROOT
        ========================================================= */

    :root {

        --purple: #8B5CF6;
        --purple-dark: #6D28D9;
        --purple-deep: #5B21B6;

        --purple-soft: #F3E8FF;
        --purple-light: #FAF5FF;

        --black: #111111;
        --text: #1F2937;
        --muted: #6B7280;

        --white: #FFFFFF;

        --background: #F7F7FB;

        --border: #E5E7EB;

        --red: #DC2626;
        --red-soft: #FEE2E2;

        --shadow:
            0 10px 30px rgba(17, 17, 17, 0.06);

        --shadow-hover:
            0 18px 40px rgba(109, 40, 217, 0.12);

        --radius: 18px;

        --transition: all 0.25s ease;
    }


    /* =========================================================
           GLOBAL
        ========================================================= */

    * {
        box-sizing: border-box;
    }


    body {

        margin: 0;

        min-height: 100vh;

        background:
            radial-gradient(circle at top right,
                rgba(139, 92, 246, 0.08),
                transparent 30%),
            var(--background);

        color: var(--text);

        font-family:
            Inter,
            "Segoe UI",
            Arial,
            sans-serif;

        animation: pageFade .45s ease;
    }


    @keyframes pageFade {

        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }


    /* =========================================================
           MAIN CONTAINER
        ========================================================= */

    .page-container {

        width: 100%;

        max-width: 1700px;

        margin: auto;

        padding:
            30px 32px 50px;
    }


    /* =========================================================
           HEADER
        ========================================================= */

    .page-header {

        position: relative;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        overflow: hidden;

        background: white;

        border: 1px solid rgba(139,
                92,
                246,
                0.10);

        border-radius: 22px;

        padding: 27px 30px;

        margin-bottom: 28px;

        box-shadow: var(--shadow);
    }


    .page-header::before {

        content: "";

        position: absolute;

        left: 0;
        top: 0;

        width: 6px;
        height: 100%;

        background:
            linear-gradient(180deg,
                var(--purple),
                var(--purple-dark));
    }


    .page-header::after {

        content: "";

        position: absolute;

        width: 170px;
        height: 170px;

        right: -70px;
        top: -100px;

        border-radius: 50%;

        background: var(--purple-soft);

        opacity: .7;
    }


    .page-title {

        position: relative;

        z-index: 2;
    }


    .page-title h1 {

        margin: 0;

        color: var(--black);

        font-size: 30px;

        font-weight: 800;

        letter-spacing: -1px;
    }


    .page-title p {

        margin:
            7px 0 0;

        color: var(--muted);

        font-size: 14px;

        font-weight: 500;
    }


    /* =========================================================
           HEADER BUTTONS
        ========================================================= */

    .header-actions {

        position: relative;

        z-index: 5;

        display: flex;

        gap: 10px;

        flex-wrap: wrap;
    }


    .header-actions .btn {

        border: none;

        border-radius: 11px;

        padding:
            10px 16px;

        font-size: 13px;

        font-weight: 700;

        transition: var(--transition);
    }


    .header-actions .btn:hover {

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(109, 40, 217, .16);
    }


    .btn-purple {

        background: var(--purple-dark);

        color: white;
    }


    .btn-purple:hover {

        background: var(--purple-deep);

        color: white;
    }


    .btn-dark-custom {

        background: var(--black);

        color: white;
    }


    .btn-dark-custom:hover {

        background: #000;

        color: white;
    }


    /* =========================================================
           STATISTICS
        ========================================================= */

    .stats-grid {

        display: grid;

        grid-template-columns:
            repeat(3, 1fr);

        gap: 18px;

        margin-bottom: 25px;
    }


    .stat-card {

        position: relative;

        overflow: hidden;

        background: white;

        border: 1px solid var(--border);

        border-radius: var(--radius);

        padding: 22px;

        box-shadow: var(--shadow);

        transition: var(--transition);
    }


    .stat-card:hover {

        transform: translateY(-4px);

        box-shadow: var(--shadow-hover);
    }


    .stat-card::after {

        content: "";

        position: absolute;

        width: 100px;
        height: 100px;

        right: -35px;
        bottom: -40px;

        border-radius: 50%;

        background: var(--purple-soft);
    }


    .stat-icon {

        position: relative;

        z-index: 2;

        display: flex;

        align-items: center;

        justify-content: center;

        width: 44px;
        height: 44px;

        border-radius: 12px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 20px;

        margin-bottom: 15px;
    }


    .stat-label {

        position: relative;

        z-index: 2;

        color: var(--muted);

        font-size: 11px;

        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: 1px;

        margin-bottom: 6px;
    }


    .stat-number {

        position: relative;

        z-index: 2;

        color: var(--black);

        font-size: 30px;

        font-weight: 800;
    }


    /* =========================================================
           NEWS AREA
        ========================================================= */

    .news-section {

        background: white;

        border: 1px solid var(--border);

        border-radius: var(--radius);

        box-shadow: var(--shadow);

        overflow: hidden;
    }


    .section-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        padding:
            22px 24px;

        border-bottom:
            1px solid var(--border);
    }


    .section-header h2 {

        margin: 0;

        color: var(--black);

        font-size: 19px;

        font-weight: 800;
    }


    .section-header p {

        margin:
            5px 0 0;

        color: var(--muted);

        font-size: 12px;
    }


    .post-count {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        padding:
            7px 12px;

        border-radius: 999px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 11px;

        font-weight: 800;
    }


    /* =========================================================
           NEWS GRID
        ========================================================= */

    .news-grid {

        display: grid;

        grid-template-columns:
            repeat(3, 1fr);

        gap: 20px;

        padding: 24px;
    }


    /* =========================================================
           NEWS CARD
        ========================================================= */

    .news-card {

        position: relative;

        overflow: hidden;

        background: white;

        border: 1px solid var(--border);

        border-radius: 16px;

        transition: var(--transition);
    }


    .news-card:hover {

        transform: translateY(-5px);

        border-color:
            rgba(139, 92, 246, .25);

        box-shadow:
            0 15px 35px rgba(109, 40, 217, .10);
    }


    /* =========================================================
           IMAGE
        ========================================================= */

    .news-image {

        position: relative;

        width: 100%;

        height: 190px;

        overflow: hidden;

        background:
            linear-gradient(135deg,
                #F3E8FF,
                #EDE9FE);
    }


    .news-image img {

        width: 100%;

        height: 100%;

        object-fit: cover;

        transition:
            transform .45s ease;
    }


    .news-card:hover .news-image img {

        transform: scale(1.05);
    }


    .no-image {

        width: 100%;
        height: 100%;

        display: flex;

        align-items: center;

        justify-content: center;

        color: var(--purple);

        font-size: 45px;

        font-weight: 800;
    }


    /* =========================================================
           POST CONTENT
        ========================================================= */

    .news-content {

        padding: 19px;
    }


    .news-title {

        margin: 0 0 9px;

        color: var(--black);

        font-size: 16px;

        font-weight: 800;

        line-height: 1.35;
    }


    .news-description {

        color: var(--muted);

        font-size: 12px;

        line-height: 1.7;

        display: -webkit-box;

        -webkit-line-clamp: 3;
        /* Standard property for compatibility */
        line-clamp: 3;

        -webkit-box-orient: vertical;

        overflow: hidden;

        margin-bottom: 14px;
    }


    .news-date {

        display: flex;

        align-items: center;

        gap: 6px;

        color: #9CA3AF;

        font-size: 11px;

        font-weight: 600;

        margin-bottom: 15px;
    }


    /* =========================================================
           CARD ACTIONS
        ========================================================= */

    .news-actions {

        display: flex;

        gap: 8px;

        padding-top: 14px;

        border-top:
            1px solid #F1F1F4;
    }


    .news-actions .btn {

        flex: 1;

        border-radius: 9px;

        padding:
            8px 10px;

        font-size: 11px;

        font-weight: 800;

        transition: var(--transition);
    }


    .btn-edit {

        background: var(--purple-soft);

        color: var(--purple-dark);

        border: none;
    }


    .btn-edit:hover {

        background: var(--purple);

        color: white;

        transform: translateY(-1px);
    }


    .btn-delete {

        background: var(--red-soft);

        color: #991B1B;

        border: none;
    }


    .btn-delete:hover {

        background: var(--red);

        color: white;

        transform: translateY(-1px);
    }


    /* =========================================================
           EMPTY STATE
        ========================================================= */

    .empty-state {

        grid-column: 1 / -1;

        text-align: center;

        padding:
            70px 20px;
    }


    .empty-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 70px;
        height: 70px;

        margin:
            0 auto 18px;

        border-radius: 20px;

        background: var(--purple-soft);

        color: var(--purple-dark);

        font-size: 30px;
    }


    .empty-state h3 {

        margin-bottom: 7px;

        color: var(--black);

        font-size: 18px;

        font-weight: 800;
    }


    .empty-state p {

        margin-bottom: 20px;

        color: var(--muted);

        font-size: 13px;
    }


    /* =========================================================
           RESPONSIVE
        ========================================================= */

    @media (max-width: 1100px) {

        .news-grid {

            grid-template-columns:
                repeat(2, 1fr);
        }

        .stats-grid {

            grid-template-columns:
                repeat(3, 1fr);
        }
    }


    @media (max-width: 768px) {

        .page-container {

            padding:
                18px 15px 35px;
        }


        .page-header {

            flex-direction: column;

            align-items: flex-start;

            padding: 23px;

            border-radius: 18px;
        }


        .page-title h1 {

            font-size: 25px;
        }


        .header-actions {

            width: 100%;
        }


        .header-actions .btn {

            flex: 1;
        }


        .stats-grid {

            grid-template-columns:
                1fr;
        }


        .news-grid {

            grid-template-columns:
                1fr;

            padding: 16px;
        }


        .section-header {

            padding:
                19px;
        }


        .news-image {

            height: 210px;
        }
    }


    @media (max-width: 480px) {

        .header-actions {

            flex-direction: column;
        }


        .header-actions .btn {

            width: 100%;
        }


        .section-header {

            align-items: flex-start;

            flex-direction: column;
        }


        .news-content {

            padding: 16px;
        }
    }
    </style>

</head>


<body>


    <div class="page-container">


        <!-- =========================================================
         HEADER
    ========================================================= -->

        <div class="page-header">

            <div class="page-title">

                <h1>
                    Manage News
                </h1>

                <p>
                    Create, manage and organize MTU Badminton Club posts
                </p>

            </div>


            <div class="header-actions">

                <a href="dashboard.php" class="btn btn-dark-custom">
                    ← Dashboard
                </a>


                <a href="add_news.php" class="btn btn-purple">
                    + Create Post
                </a>

            </div>

        </div>


        <!-- =========================================================
         STATISTICS
    ========================================================= -->

        <?php

    ?>


        <div class="stats-grid">


            <div class="stat-card">

                <div class="stat-icon">
                    📰
                </div>

                <div class="stat-label">
                    Total Posts
                </div>

                <div class="stat-number">
                    <?= $postCount ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    🏸
                </div>

                <div class="stat-label">
                    Club Updates
                </div>

                <div class="stat-number">
                    <?= $postCount ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    ✨
                </div>

                <div class="stat-label">
                    Latest Content
                </div>

                <div class="stat-number">

                    <?php if ($postCount > 0): ?>

                    01

                    <?php else: ?>

                    00

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- =========================================================
         NEWS SECTION
    ========================================================= -->

        <div class="news-section">


            <div class="section-header">

                <div>

                    <h2>
                        Published Posts
                    </h2>

                    <p>
                        Manage your badminton club announcements and news
                    </p>

                </div>


                <span class="post-count">

                    <?= $postCount ?>

                    <?= $postCount == 1 ? 'Post' : 'Posts' ?>

                </span>

            </div>


            <!-- =====================================================
             NEWS GRID
        ===================================================== -->

            <div class="news-grid">


                <?php if ($postCount > 0): ?>


                <?php foreach ($newsPosts as $row): ?>


                <div class="news-card">


                    <!-- IMAGE -->

                    <div class="news-image">

                        <?php

                            $imagePath = '';

                            if (!empty($row['image'])) {

                                $imagePath = "../uploads/news/" .
                                             $row['image'];
                            }

                            ?>


                        <?php if (
                                !empty($row['image']) &&
                                file_exists(
                                    __DIR__ .
                                    "/../uploads/news/" .
                                    $row['image']
                                )
                            ): ?>

                        <img src="<?= e($imagePath) ?>" alt="<?= e($row['title']) ?>">

                        <?php else: ?>

                        <div class="no-image">
                            🏸
                        </div>

                        <?php endif; ?>

                    </div>


                    <!-- CONTENT -->

                    <div class="news-content">


                        <h3 class="news-title">

                            <?= e($row['title']) ?>

                        </h3>


                        <div class="news-description">

                            <?= e($row['content']) ?>

                        </div>


                        <div class="news-date">

                            📅

                            <?= e($row['created_at']) ?>

                        </div>


                        <!-- ACTIONS -->

                        <div class="news-actions">


                            <a href="edit_news.php?id=<?= (int)$row['id'] ?>" class="btn btn-edit">
                                ✏ Edit
                            </a>


                            <form method="POST" action="delete_news.php" style="flex:1;"
                                onsubmit="return confirm('Are you sure you want to permanently delete this post?');">

                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">


                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">


                                <button type="submit" class="btn btn-delete w-100">
                                    🗑 Delete
                                </button>

                            </form>


                        </div>

                    </div>

                </div>


                <?php endforeach; ?>


                <?php else: ?>


                <!-- EMPTY -->

                <div class="empty-state">

                    <div class="empty-icon">
                        📰
                    </div>


                    <h3>
                        No Posts Yet
                    </h3>


                    <p>
                        You haven't created any badminton club news posts yet.
                    </p>


                    <a href="add_news.php" class="btn btn-purple">
                        + Create Your First Post
                    </a>

                </div>


                <?php endif; ?>


            </div>

        </div>


    </div>


</body>

</html>