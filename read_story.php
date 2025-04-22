<?php
require_once 'config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Get story ID from URL
$story_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$story_id) {
    header('Location: library.php');
    exit();
}

// Get database connection
$conn = getDBConnection();

// Get story details
$stmt = $conn->prepare("SELECT * FROM stories WHERE id = ?");
$stmt->execute([$story_id]);
$story = $stmt->fetch();

if (!$story) {
    header('Location: library.php');
    exit();
}

// Update user progress
$progress_query = "INSERT INTO user_progress (user_id, story_id, progress, last_read) 
                  VALUES (:user_id, :story_id, 0, NOW())
                  ON DUPLICATE KEY UPDATE last_read = NOW()";
$progress_stmt = $conn->prepare($progress_query);
$progress_stmt->execute([
    'user_id' => $_SESSION['user_id'],
    'story_id' => $story['id']
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($story['title']); ?> - StoryLand</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bubblegum+Sans&family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF1493;
            --secondary: #00FFFF;
            --accent1: #FFE100;
            --accent2: #9D00FF;
            --accent3: #FF4500;
            --accent4: #00FF7F;
            --accent5: #FF69B4;
            --accent6: #4169E1;
            --accent7: #32CD32;
            --accent8: #FF8C00;
            --bg-light: #F0FFFF;
            --text-dark: #2D3436;
            --gradient1: linear-gradient(135deg, #FF1493, #FF4500);
            --gradient2: linear-gradient(135deg, #00FFFF, #00FF7F);
            --gradient3: linear-gradient(135deg, #9D00FF, #4169E1);
            --gradient4: linear-gradient(45deg, #FFE100, #FF8C00);
            --gradient5: linear-gradient(45deg, #FF69B4, #FF1493);
            --gradient6: linear-gradient(45deg, #32CD32, #00FFFF);
        }

        body {
            font-family: 'Comic Neue', cursive;
            background: linear-gradient(135deg, #FFE5F1, #E0FFFF);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .story-container {
            background: white;
            border-radius: 30px;
            box-shadow: 
                0 15px 35px rgba(0,0,0,0.1),
                0 0 0 10px rgba(255,255,255,0.5);
            overflow: hidden;
        }

        .story-header {
            background: var(--gradient1);
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .story-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 225, 0, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(0, 255, 255, 0.4) 0%, transparent 50%);
        }

        .story-title {
            font-family: 'Bubblegum Sans', cursive;
            font-size: 4rem;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 
                3px 3px 0 rgba(0,0,0,0.1),
                6px 6px 0 rgba(0,0,0,0.05);
            transform: rotate(-2deg);
            animation: titleColor 8s infinite linear;
        }

        @keyframes titleColor {
            0% { color: white; }
            25% { color: #FFE100; }
            50% { color: #00FFFF; }
            75% { color: #FF69B4; }
            100% { color: white; }
        }

        .meta-tags {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            transform: rotate(2deg);
        }

        .meta-tag {
            background: white;
            color: var(--accent2);
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 3px solid var(--accent1);
            position: relative;
            overflow: hidden;
        }

        .meta-tag:nth-child(1) {
            border-color: var(--accent1);
            color: var(--accent3);
        }

        .meta-tag:nth-child(2) {
            border-color: var(--accent5);
            color: var(--accent2);
        }

        .meta-tag:nth-child(3) {
            border-color: var(--accent4);
            color: var(--accent6);
        }

        .meta-tag:hover {
            transform: translateY(-3px) rotate(-2deg);
            border-color: var(--accent2);
            background: var(--gradient3);
            color: white;
        }

        .story-content-wrapper {
            position: relative;
            background: var(--bg-light);
        }

        .story-text {
            padding: 3rem 2rem;
            font-size: 1.3rem;
            line-height: 1.8;
            background: white;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .story-text::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 6px;
            background: var(--gradient6);
            border-radius: 3px;
        }

        .chapter-heading {
            text-align: center;
            font-family: 'Bubblegum Sans', cursive;
            font-size: 2.5rem;
            color: var(--accent2);
            margin: 2rem 0;
            position: relative;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.1);
        }

        .chapter-heading::before,
        .chapter-heading::after {
            content: '★';
            color: var(--accent1);
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
        }

        .chapter-heading::before {
            left: 20%;
        }

        .chapter-heading::after {
            right: 20%;
        }

        .story-paragraph {
            background: var(--bg-light);
            padding: 2.5rem;
            margin-bottom: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            border-left: 8px solid var(--primary);
            font-size: 1.25rem;
            line-height: 2;
            letter-spacing: 0.02em;
            text-align: justify;
        }

        .story-paragraph::first-letter {
            font-size: 3.5em;
            float: left;
            margin-right: 0.2em;
            line-height: 1;
            color: var(--accent2);
            font-family: 'Bubblegum Sans', cursive;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.1);
        }

        .story-paragraph:nth-child(6n+1) {
            border-image: linear-gradient(to bottom, var(--primary), var(--accent5)) 1;
            transform: rotate(0.5deg);
        }

        .story-paragraph:nth-child(6n+2) {
            border-image: linear-gradient(to bottom, var(--secondary), var(--accent6)) 1;
            transform: rotate(-0.5deg);
        }

        .story-paragraph:nth-child(6n+3) {
            border-image: linear-gradient(to bottom, var(--accent3), var(--accent7)) 1;
            transform: rotate(0.3deg);
        }

        .story-paragraph:nth-child(6n+4) {
            border-image: linear-gradient(to bottom, var(--accent4), var(--accent8)) 1;
            transform: rotate(-0.3deg);
        }

        .story-paragraph:nth-child(6n+5) {
            border-image: linear-gradient(to bottom, var(--accent5), var(--primary)) 1;
            transform: rotate(0.4deg);
        }

        .story-paragraph:nth-child(6n+6) {
            border-image: linear-gradient(to bottom, var(--accent6), var(--secondary)) 1;
            transform: rotate(-0.4deg);
        }

        .story-paragraph:hover {
            transform: translateX(10px) scale(1.01);
            box-shadow: 0 12px 25px rgba(0,0,0,0.1);
            background: linear-gradient(45deg, rgba(255,255,255,1), var(--bg-light));
        }

        .story-paragraph + .story-paragraph {
            margin-top: 3rem;
        }

        .story-paragraph::after {
            content: '';
            position: absolute;
            bottom: -1.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 2px;
            background: linear-gradient(to right, transparent, var(--accent1), transparent);
        }

        .story-paragraph:last-child::after {
            display: none;
        }

        .story-image-container {
            background: var(--gradient4);
            padding: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .story-image {
            max-width: 90%;
            max-height: 500px;
            border-radius: 20px;
            box-shadow: 
                0 15px 30px rgba(0,0,0,0.15),
                0 0 0 10px white,
                0 0 0 15px var(--accent1);
            margin: 0 auto;
            display: block;
            transform: rotate(1deg);
            transition: all 0.3s ease;
            object-fit: cover;
        }

        .page-decoration {
            position: absolute;
            width: 40px;
            height: 40px;
            pointer-events: none;
        }

        .page-decoration.top-left {
            top: 2rem;
            left: 2rem;
            border-top: 4px solid var(--accent1);
            border-left: 4px solid var(--accent1);
        }

        .page-decoration.top-right {
            top: 2rem;
            right: 2rem;
            border-top: 4px solid var(--accent2);
            border-right: 4px solid var(--accent2);
        }

        .page-decoration.bottom-left {
            bottom: 2rem;
            left: 2rem;
            border-bottom: 4px solid var(--accent3);
            border-left: 4px solid var(--accent3);
        }

        .page-decoration.bottom-right {
            bottom: 2rem;
            right: 2rem;
            border-bottom: 4px solid var(--accent4);
            border-right: 4px solid var(--accent4);
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            padding: 2rem;
            background: var(--gradient5);
            position: relative;
            overflow: hidden;
        }

        .navigation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 30% 50%, rgba(255,225,0,0.3) 0%, transparent 50%),
                radial-gradient(circle at 70% 50%, rgba(0,255,127,0.3) 0%, transparent 50%);
        }

        .nav-button {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            color: var(--accent2);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border: 3px solid var(--accent1);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .nav-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient6);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        .nav-button:hover {
            transform: translateY(-3px);
            color: white;
            border-color: white;
        }

        .nav-button:hover::before {
            opacity: 1;
        }

        .nav-button svg {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }

        .nav-button:hover svg {
            transform: scale(1.2);
            animation: iconColor 2s infinite linear;
        }

        @keyframes iconColor {
            0% { stroke: white; }
            50% { stroke: var(--accent1); }
            100% { stroke: white; }
        }

        @media (max-width: 768px) {
            .page-container {
                padding: 1rem;
            }

            .story-title {
                font-size: 2.8rem;
            }

            .story-text {
                padding: 2rem 1rem;
                font-size: 1.1rem;
            }

            .story-paragraph {
                padding: 1.5rem;
                font-size: 1.1rem;
                line-height: 1.8;
                margin-bottom: 1.5rem;
            }

            .story-paragraph::first-letter {
                font-size: 2.5em;
            }

            .chapter-heading {
                font-size: 2rem;
            }

            .chapter-heading::before,
            .chapter-heading::after {
                display: none;
            }

            .nav-button {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
        }

        @media (min-width: 769px) {
            .story-paragraph {
                margin-left: 2rem;
                margin-right: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <article class="story-container">
            <header class="story-header">
                <h1 class="story-title"><?php echo htmlspecialchars($story['title']); ?></h1>
                <div class="meta-tags">
                    <span class="meta-tag"><?php echo htmlspecialchars($story['genre']); ?></span>
                    <span class="meta-tag"><?php echo htmlspecialchars($story['reading_level']); ?></span>
                    <span class="meta-tag"><?php echo htmlspecialchars($story['age_group']); ?></span>
                </div>
            </header>

            <div class="story-content-wrapper">
                <div class="story-image-container">
                    <div class="page-decoration top-left"></div>
                    <div class="page-decoration top-right"></div>
                    <div class="page-decoration bottom-left"></div>
                    <div class="page-decoration bottom-right"></div>
                    <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($story['title']); ?>" 
                         class="story-image"
                         onerror="this.onerror=null; this.src='images/default-cover.jpg'; this.classList.add('fallback-image');">
                </div>

                <div class="story-text">
                    <h2 class="chapter-heading">Once Upon a Time...</h2>
                    <?php
                    $paragraphs = explode("\n\n", $story['content']);
                    foreach ($paragraphs as $paragraph) {
                        if (trim($paragraph) !== '') {
                            echo '<div class="story-paragraph">' . nl2br(htmlspecialchars($paragraph)) . '</div>';
                        }
                    }
                    ?>
                </div>

                <nav class="navigation">
                    <a href="library.php" class="nav-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                        </svg>
                        Back to Library
                    </a>
                    <a href="library.php" class="nav-button">
                        Next Story
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </a>
                </nav>
            </div>
        </article>
    </div>
</body>
</html> 