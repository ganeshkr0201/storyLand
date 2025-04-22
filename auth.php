<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the raw input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input received: " . $rawInput);

    try {
        $data = json_decode($rawInput, true);
        
        // Log the decoded data
        error_log("Decoded data: " . print_r($data, true));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }

        if (!isset($data['action'])) {
            throw new Exception("No action specified");
        }

        switch ($data['action']) {
            case 'login':
                handleLogin($data);
                break;
            case 'signup':
                handleSignup($data);
                break;
            case 'logout':
                handleLogout();
                break;
            default:
                throw new Exception("Invalid action");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

function handleSignup($data) {
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        throw new Exception("Missing required fields");
    }

    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = $data['password'];

    if (empty($name) || empty($email) || empty($password)) {
        throw new Exception("All fields are required");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    try {
        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        // Begin transaction
        $conn->beginTransaction();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($hashedPassword === false) {
            throw new Exception("Password hashing failed");
        }

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $success = $stmt->execute([$name, $email, $hashedPassword]);
        
        if (!$success) {
            throw new Exception("Failed to create user account");
        }

        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Signup successful'
        ]);
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        throw $e;
    }
}

function handleLogin($data) {
    error_log("Processing login for email: " . ($data['email'] ?? 'not provided'));

    if (!isset($data['email']) || !isset($data['password'])) {
        throw new Exception("Email and password are required");
    }

    $email = trim($data['email']);
    $password = $data['password'];

    if (empty($email) || empty($password)) {
        throw new Exception("Email and password cannot be empty");
    }

    try {
        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        // Get user by email
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        error_log("User lookup result: " . ($user ? "User found" : "User not found"));

        if (!$user) {
            throw new Exception("Invalid email or password");
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            error_log("Password verification failed for user: " . $email);
            throw new Exception("Invalid email or password");
        }

        // Determine role based on email
        $role = strpos(strtolower($email), 'admin') !== false ? 'admin' : 'user';

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $role;

        error_log("Login successful for user: " . $email . " with role: " . $role);

        // Return success response with redirect based on role
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $role === 'admin' ? 'admin.php' : 'index.html'
        ]);
        exit();
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        throw $e;
    }
}

function handleLogout() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}
?> 