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
    <title><?php echo $edit_story ? 'Edit Story' : 'Post Story'; ?> - StoryLand</title>
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
                <!-- <a href="admin.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Home</a> -->
                <!-- <a href="library.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Library</a> -->
                <a href="admin.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Admin Dashboard</a>
                <a href="logout.php" class="text-white text-lg hover:text-[#FEE440] transform hover:scale-110 transition-all duration-300 font-semibold">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Story Form -->
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-[#9B5DE5] mb-2"><?php echo $edit_story ? 'Edit Story' : 'Post New Story'; ?></h1>
            <p class="text-xl text-[#F15BB5]"><?php echo $edit_story ? 'Update story details' : 'Add a new story to the library'; ?></p>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Story Form -->
        <div class="bg-white comic-border p-8 shadow-lg mb-8">
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if ($edit_story): ?>
                    <input type="hidden" name="story_id" value="<?php echo $edit_story['id']; ?>">
                    <input type="hidden" name="existing_cover_image" value="<?php echo $edit_story['cover_image']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Title</label>
                        <input type="text" name="title" required
                               value="<?php echo $edit_story ? htmlspecialchars($edit_story['title']) : ''; ?>"
                               class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Age Group</label>
                        <input type="text" name="age_group" required
                               value="<?php echo $edit_story ? htmlspecialchars($edit_story['age_group']) : ''; ?>"
                               class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Reading Level</label>
                        <input type="text" name="reading_level" required
                               value="<?php echo $edit_story ? htmlspecialchars($edit_story['reading_level']) : ''; ?>"
                               class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Genre</label>
                        <input type="text" name="genre" required
                               value="<?php echo $edit_story ? htmlspecialchars($edit_story['genre']) : ''; ?>"
                               class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Description</label>
                    <textarea name="description" required rows="3"
                              class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]"><?php echo $edit_story ? htmlspecialchars($edit_story['description']) : ''; ?></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Content</label>
                    <textarea name="content" required rows="10"
                              class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]"><?php echo $edit_story ? htmlspecialchars($edit_story['content']) : ''; ?></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Cover Image</label>
                    <?php if ($edit_story && $edit_story['cover_image']): ?>
                        <div class="mb-2">
                            <img src="<?php echo htmlspecialchars($edit_story['cover_image']); ?>" 
                                 alt="Current cover" class="w-32 h-32 object-cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept="image/*"
                           class="w-full px-4 py-2 comic-border focus:outline-none focus:ring-2 focus:ring-[#9B5DE5]">
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="admin.php" class="bg-gray-500 text-white px-6 py-2 rounded-full hover:bg-gray-600 font-bold transform hover:scale-105 transition-all duration-300 comic-border shadow-md">
                        Cancel
                    </a>
                    <button type="submit" name="submit_story"
                            class="bg-[#FEE440] text-[#9B5DE5] px-6 py-2 rounded-full hover:bg-[#FFE66D] hover:text-[#7B4BC4] font-bold transform hover:scale-105 transition-all duration-300 comic-border shadow-md">
                        <?php echo $edit_story ? 'Update Story' : 'Add Story'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 