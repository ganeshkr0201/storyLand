<?php
require_once 'config.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Get database connection
$conn = getDBConnection();

// Handle story deletion
if (isset($_POST['delete_story'])) {
    $story_id = $_POST['story_id'];
    $delete_query = "DELETE FROM stories WHERE id = :id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->execute(['id' => $story_id]);
    header('Location: admin.php?message=Story deleted successfully');
    exit();
}

// Handle story addition/update
if (isset($_POST['submit_story'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content = $_POST['content'];
    $age_group = $_POST['age_group'];
    $reading_level = $_POST['reading_level'];
    $genre = $_POST['genre'];
    
    // Handle file upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/stories/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['cover_image']['name']);
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
            $cover_image = $target_path;
        }
    }

    if (isset($_POST['story_id'])) {
        // Update existing story
        $update_query = "UPDATE stories SET 
                        title = :title,
                        description = :description,
                        content = :content,
                        age_group = :age_group,
                        reading_level = :reading_level,
                        genre = :genre,
                        cover_image = :cover_image
                        WHERE id = :id";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'age_group' => $age_group,
            'reading_level' => $reading_level,
            'genre' => $genre,
            'cover_image' => $cover_image ?: $_POST['existing_cover_image'],
            'id' => $_POST['story_id']
        ]);
        $message = "Story updated successfully";
    } else {
        // Add new story
        $insert_query = "INSERT INTO stories (title, description, content, age_group, reading_level, genre, cover_image) 
                        VALUES (:title, :description, :content, :age_group, :reading_level, :genre, :cover_image)";
        $stmt = $conn->prepare($insert_query);
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'age_group' => $age_group,
            'reading_level' => $reading_level,
            'genre' => $genre,
            'cover_image' => $cover_image
        ]);
        $message = "Story added successfully";
    }
    header('Location: admin.php?message=' . urlencode($message));
    exit();
}

// Fetch all stories
$query = "SELECT * FROM stories ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$stories = $stmt->fetchAll();

// Fetch story to edit if ID is provided
$edit_story = null;
if (isset($_GET['edit'])) {
    $edit_query = "SELECT * FROM stories WHERE id = :id";
    $edit_stmt = $conn->prepare($edit_query);
    $edit_stmt->execute(['id' => $_GET['edit']]);
    $edit_story = $edit_stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StoryLand</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Patrick Hand', cursive;
        }
        .comic-border {
            border: 4px solid #000;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-pink-100 via-purple-100 to-blue-100">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-[#9B5DE5] via-[#F15BB5] to-[#00BBF9] p-4 comic-border shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="admin.php" class="text-white text-3xl font-bold tracking-wider transform hover:scale-105 transition-transform duration-300 flex items-center">
                <span class="bg-[#FEE440] text-[#9B5DE5] px-4 py-2 rounded-full comic-border shadow-md hover:bg-[#FFE66D] hover:text-[#7B4BC4] transition-all duration-300">StoryLand</span>
            </a>
            <div class="space-x-6 flex items-center">
                <a href="admin.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Admin Dashboard</a>
                <!-- <a href="library.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Library</a> -->
                <a href="post_story.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Post Story</a>
                <a href="logout.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Admin Dashboard -->
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-[#9B5DE5] mb-2">Admin Dashboard</h1>
            <p class="text-xl text-[#F15BB5]">Manage Stories</p>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Stories List -->
        <div class="bg-white comic-border p-8 shadow-lg">
            <h2 class="text-2xl font-bold text-[#9B5DE5] mb-4">All Stories</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Genre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($stories as $story): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($story['title']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($story['age_group']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($story['genre']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="post_story.php?edit=<?php echo $story['id']; ?>" 
                                   class="text-[#9B5DE5] hover:text-[#7B4BC4] mr-4">Edit</a>
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this story?');">
                                    <input type="hidden" name="story_id" value="<?php echo $story['id']; ?>">
                                    <button type="submit" name="delete_story" 
                                            class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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