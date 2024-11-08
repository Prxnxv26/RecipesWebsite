<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'recipe_website');

// Create database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to redirect
function redirect($path) {
    header("Location: $path");
    exit();
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle file upload
function handleFileUpload($file, $targetDir = "uploads/") {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $targetFile = $targetDir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    }
    
    return false;
}
