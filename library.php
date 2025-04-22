<?php
require_once 'config.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header('Location: login.html');
    exit();
}

// Get database connection
$conn = getDBConnection();

// Get filter parameters
$age_group = isset($_GET['age_group']) ? $_GET['age_group'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Function to extract age range numbers
function getAgeRange($age_group) {
    if ($age_group === '13+') {
        return [13, 99]; // Using 99 as upper limit for 13+
    }
    $ranges = explode('-', $age_group);
    if (count($ranges) === 2) {
        return [intval($ranges[0]), intval($ranges[1])];
    }
    return null;
}

// Build query with filters
$query = "SELECT *,
    CASE 
        WHEN title LIKE '%Tortoise%Hare%' THEN 1
        WHEN title LIKE '%Rainbow Unicorn%' THEN 2
        WHEN title LIKE '%Magic Forest%' THEN 3
        ELSE 999
    END as story_order
FROM stories 
WHERE 1=1";
$params = [];

if ($age_group) {
    $age_range = getAgeRange($age_group);
    if ($age_range) {
        $query .= " AND (
            CASE 
                WHEN age_group = '13+' THEN 13
                ELSE CAST(SUBSTRING_INDEX(age_group, '-', 1) AS SIGNED)
            END >= :min_age
            AND 
            CASE 
                WHEN age_group = '13+' THEN 99
                ELSE CAST(SUBSTRING_INDEX(age_group, '-', -1) AS SIGNED)
            END <= :max_age
        )";
        $params['min_age'] = $age_range[0];
        $params['max_age'] = $age_range[1];
    }
}

if ($genre) {
    $query .= " AND genre LIKE :genre";
    $params['genre'] = "%$genre%";
}

if ($search) {
    $query .= " AND (title LIKE :search OR description LIKE :search)";
    $params['search'] = "%$search%";
}

// Order by story_order first, then by title for consistent ordering
$query .= " ORDER BY story_order, title ASC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$stories = $stmt->fetchAll();

// Get unique age groups and genres for filters
$age_groups = array_unique(array_column($stories, 'age_group'));

// Define main genres only
$predefined_genres = [
    'Adventure',
    'Fable',
    'Fairy Tale',
    'Fantasy',
    'Folktale',
    'Historical',
    'Moral Story',
    'Romance'
];

// Combine and sort genres
$all_genres = array_unique($predefined_genres);
sort($all_genres);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Library - StoryLand</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@heroicons/v2/24/outline/index.js"></script>
    <style>
        body {
            font-family: 'Patrick Hand', cursive;
        }
        .comic-border {
            border: 4px solid #000;
            border-radius: 8px;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 8px;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
            border-radius: 8px;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .story-card {
            background: rgba(255, 255, 255, 0.95);
            border: 4px solid #000;
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .story-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .story-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #9B5DE5, #F15BB5, #00BBF9);
        }
        .image-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4/3;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border: 2px solid rgba(255,255,255,0.3);
        }
        .story-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .story-card:hover .story-image {
            transform: scale(1.05);
        }
        .tag {
            display: inline-block;
            padding: 0.25rem 1rem;
            border-radius: 999px;
            font-size: 0.875rem;
            margin: 0 0.25rem 0.5rem 0;
            background: rgba(155, 93, 229, 0.15);
            color: #7C3AED;
            border: 1px solid rgba(124, 58, 237, 0.3);
            transition: all 0.2s ease;
        }
        .tag:hover {
            background: rgba(155, 93, 229, 0.25);
            transform: translateY(-1px);
        }
        .read-button {
            width: 100%;
            padding: 0.75rem;
            background: #FEE440;
            color: #9B5DE5;
            border: 3px solid #000;
            border-radius: 6px;
            font-weight: bold;
            transition: all 0.2s ease;
            margin-top: auto;
            position: relative;
            overflow: hidden;
        }
        .read-button:hover {
            background: #FFE66D;
            color: #7B4BC4;
            transform: translateY(-2px);
        }
        .read-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        .read-button:hover::after {
            transform: translateX(100%);
        }
        .search-input {
            background: rgba(255, 255, 255, 0.9);
            border: 3px solid #000;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(155, 93, 229, 0.3);
        }
        .filter-button {
            background: rgba(255, 255, 255, 0.9);
            border: 3px solid #000;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .filter-button:hover {
            background: #FEE440;
            color: #9B5DE5;
        }
        .filter-button.active {
            background: #FEE440;
            color: #9B5DE5;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .featured-story {
            animation: float 6s ease-in-out infinite;
        }
        .genre-select {
            position: relative;
            width: 100%;
        }
        
        .genre-select select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 100%;
            padding: 0.75rem 1rem;
            padding-right: 2.5rem;
            background: rgba(255, 255, 255, 0.9);
            border: 3px solid #000;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Patrick Hand', cursive;
        }
        
        .genre-select select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(155, 93, 229, 0.3);
            border-color: #9B5DE5;
        }
        
        .genre-select select:hover {
            background: #FEE440;
            color: #9B5DE5;
        }
        
        .genre-select::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #000;
            pointer-events: none;
        }
        
        .genre-select select option {
            padding: 0.5rem;
            background: white;
            color: #333;
        }
        
        .genre-select select option:hover {
            background: #FEE440;
            color: #9B5DE5;
        }
        
        .genre-select select option:checked {
            background: #9B5DE5;
            color: white;
        }
        .like-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            transition: all 0.3s ease;
            cursor: pointer;
            background-color: #f3f4f6;
            border: 2px solid #e5e7eb;
            color: #6b7280;
        }
        
        .like-button.liked {
            background-color: #fee2e2;
            border-color: #fecaca;
            color: #ef4444;
        }
        
        .like-button:hover {
            transform: scale(1.05);
        }
        
        .like-count {
            font-weight: 600;
        }
        
        .story-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            gap: 1rem;
        }
        
        .like-icon {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 9999px;
            color: #6b7280;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .like-icon:hover {
            color: #ef4444;
            transform: scale(1.1);
        }

        .like-icon.liked {
            color: #ef4444;
        }

        .like-icon.liked svg {
            fill: currentColor;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-pink-100 via-purple-100 to-blue-100 min-h-screen">
    <!-- Add stories data -->
    <script src="js/stories.js"></script>
    
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-[#9B5DE5] via-[#F15BB5] to-[#00BBF9] p-4 comic-border shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.html" class="text-white text-3xl font-bold tracking-wider transform hover:scale-105 transition-transform duration-300 flex items-center">
                <span class="bg-[#FEE440] text-[#9B5DE5] px-4 py-2 rounded-full comic-border shadow-md hover:bg-[#FFE66D] hover:text-[#7B4BC4] transition-all duration-300">StoryLand</span>
            </a>
            <div class="space-x-6 flex items-center">
                <a href="index.html" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Home</a>
                <a href="library.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Library</a>
                <a href="about.html" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">About</a>
                
                <!-- User profile dropdown -->
                <div class="dropdown">
                    <button class="bg-[#FEE440] text-[#9B5DE5] px-6 py-2 rounded-full hover:bg-[#FFE66D] hover:text-[#7B4BC4] font-bold flex items-center transform hover:scale-105 transition-all duration-300 comic-border shadow-md">
                        <span class="mr-2">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="dropdown-content bg-white rounded-xl comic-border shadow-xl">
                        <a href="profile.php" class="text-[#9B5DE5] hover:bg-[#FEE440] hover:text-[#7B4BC4] rounded-t-lg font-semibold">Profile</a>
                        <a href="settings.php" class="text-[#9B5DE5] hover:bg-[#FEE440] hover:text-[#7B4BC4] font-semibold">Settings</a>
                        <a href="logout.php" class="text-[#9B5DE5] hover:bg-[#FEE440] hover:text-[#7B4BC4] rounded-b-lg font-semibold">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Library Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-[#9B5DE5] mb-2">Story Library</h1>
            <p class="text-xl text-[#F15BB5]">Discover magical stories for young readers!</p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white comic-border p-6 mb-8 shadow-lg">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="search" placeholder="Search stories..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                    
                    <select name="age_group" class="filter-button">
                        <option value="">All Age Groups</option>
                        <option value="5-7" <?php echo $age_group === '5-7' ? 'selected' : ''; ?>>5-7 years</option>
                        <option value="7-9" <?php echo $age_group === '7-9' ? 'selected' : ''; ?>>7-9 years</option>
                        <option value="9-11" <?php echo $age_group === '9-11' ? 'selected' : ''; ?>>9-11 years</option>
                        <option value="11-13" <?php echo $age_group === '11-13' ? 'selected' : ''; ?>>11-13 years</option>
                        <option value="13+" <?php echo $age_group === '13+' ? 'selected' : ''; ?>>13+ years</option>
                    </select>

                    <div class="genre-select">
                        <select name="genre" class="filter-button">
                            <option value="">All Genres</option>
                            <?php foreach ($all_genres as $g): ?>
                                <option value="<?php echo htmlspecialchars($g); ?>"
                                        <?php echo $g === $genre ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-[#FEE440] text-[#9B5DE5] px-6 py-2 rounded-full hover:bg-[#FFE66D] hover:text-[#7B4BC4] font-bold transform hover:scale-105 transition-all duration-300 comic-border shadow-md">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Story Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($stories)): ?>
                <div class="col-span-full text-center py-8">
                    <p class="text-xl text-[#9B5DE5]">No stories found matching your criteria.</p>
                    <p class="text-gray-600 mt-2">Try adjusting your filters or check back later for new stories.</p>
                </div>
            <?php else: ?>
                <?php foreach ($stories as $story): ?>
                    <div class="story-card">
                        <div class="image-container">
                            <?php if ($story['cover_image']): ?>
                                <img src="<?php echo htmlspecialchars($story['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($story['title']); ?>"
                                     class="story-image"
                                     onerror="this.onerror=null; this.src='images/default-cover.jpg';">
                            <?php else: ?>
                                <div class="story-image placeholder-image">
                                    <?php echo htmlspecialchars($story['title']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="text-xl font-bold text-[#9B5DE5] mb-2"><?php echo htmlspecialchars($story['title']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($story['description']); ?></p>
                            <div class="flex flex-wrap mb-4">
                                <span class="tag"><?php echo htmlspecialchars($story['age_group']); ?></span>
                                <span class="tag"><?php echo htmlspecialchars($story['reading_level']); ?></span>
                                <span class="tag"><?php echo htmlspecialchars($story['genre']); ?></span>
                            </div>
                            <div class="story-actions">
                                <div class="like-icon" onclick="toggleLike(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <a href="read_story.php?id=<?php echo $story['id']; ?>" 
                                   class="read-button text-center flex-grow">
                                    Read Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Function to filter and display stories
        function filterStories() {
            const searchTerm = document.querySelector('.search-input').value.toLowerCase();
            const selectedAgeGroup = document.querySelector('.filter-button[name="age_group"]').value;
            const selectedGenre = document.querySelector('.filter-button[name="genre"]').value;

            const filteredStories = window.stories.filter(story => {
                const matchesSearch = !searchTerm || 
                    story.title.toLowerCase().includes(searchTerm) || 
                    story.description.toLowerCase().includes(searchTerm);
                    
                const matchesAge = isInAgeRange(story.age_group, selectedAgeGroup);
                
                const matchesGenre = !selectedGenre || 
                    story.genre.toLowerCase() === selectedGenre.toLowerCase();

                return matchesSearch && matchesAge && matchesGenre;
            });

            displayStories(filteredStories);
        }

        // Function to display stories
        function displayStories(stories) {
            const storyGrid = document.querySelector('.story-grid');
            
            if (stories.length === 0) {
                storyGrid.innerHTML = `
                    <div class="col-span-full text-center py-8">
                        <p class="text-xl text-[#9B5DE5]">No stories found matching your criteria.</p>
                        <p class="text-gray-600 mt-2">Try adjusting your filters or check back later for new stories.</p>
                    </div>
                `;
                return;
            }

            const storyCards = stories.map(story => `
                <div class="story-card">
                    <div class="image-container">
                        <img src="${story.cover}" 
                             alt="${story.title}"
                             class="story-image"
                             onerror="this.onerror=null; this.src='images/default-cover.jpg';">
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-xl font-bold text-[#9B5DE5] mb-2">${story.title}</h3>
                        <p class="text-gray-600 mb-4">${story.description}</p>
                        <div class="flex flex-wrap mb-4">
                            <span class="tag">${story.age_group}</span>
                            <span class="tag">${story.reading_level}</span>
                            <span class="tag">${story.genre}</span>
                        </div>
                        <div class="story-actions">
                            <div class="like-icon" onclick="toggleLike(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <a href="${story.content}" class="read-button text-center">
                                Read Now
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');

            storyGrid.innerHTML = storyCards;
        }

        // Add event listeners to form inputs
        document.querySelector('.search-input').addEventListener('input', filterStories);
        document.querySelector('.filter-button[name="age_group"]').addEventListener('change', filterStories);
        document.querySelector('.filter-button[name="genre"]').addEventListener('change', filterStories);

        // Initial load
        filterStories();

        async function handleLike(storyId) {
            try {
                const formData = new FormData();
                formData.append('story_id', storyId);

                const response = await fetch('like_story.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    const button = document.querySelector(`.like-button[data-story-id="${storyId}"]`);
                    const countSpan = button.querySelector('.like-count');
                    const heartIcon = button.querySelector('svg');
                    
                    if (data.liked) {
                        button.classList.add('liked');
                        heartIcon.setAttribute('fill', 'currentColor');
                    } else {
                        button.classList.remove('liked');
                        heartIcon.setAttribute('fill', 'none');
                    }
                    
                    countSpan.textContent = data.likeCount;
                } else {
                    if (data.message === 'Please log in to like stories') {
                        window.location.href = 'login.html';
                    } else {
                        alert(data.message);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your like');
            }
        }

        function toggleLike(element) {
            element.classList.toggle('liked');
        }
    </script>

    <!-- Footer -->
    <footer class="bg-[#1D70B8] text-white py-8 comic-border mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 StoryLand. All rights reserved.</p>
            <div class="mt-4 space-x-4">
                <a href="#" class="hover:text-[#E63946]">Privacy Policy</a>
                <a href="#" class="hover:text-[#E63946]">Terms of Service</a>
                <a href="#" class="hover:text-[#E63946]">Contact Us</a>
            </div>
        </div>
    </footer>
</body>
</html> 