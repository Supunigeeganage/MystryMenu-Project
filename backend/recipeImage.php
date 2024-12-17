<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];

if (!isset($_FILES['recipe-image']) || $_FILES['recipe-image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or an error occurred.']);
    exit;
}

$file = $_FILES['recipe-image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$uploadDir = __DIR__ . '/recipe_image/';
$maxFileSize = 10 * 1024 * 1024;   

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
    exit;
}

if ($file['size'] > $maxFileSize) {
    echo json_encode(['status' => 'error', 'message' => 'File size exceeds the 10MB limit.']);
    exit;
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'recipe_' . $userId . '_' . time() . '.' . $ext;
$filePath = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save the uploaded file.']);
    exit;
}

$recipePath = '/recipe_image/' . $newFileName;

try {
    // Get the latest recipe_id for this user
    $stmt = $pdo->prepare("SELECT recipe_id FROM recipe WHERE user_id = ? ORDER BY recipe_id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($recipe) {
        $stmt = $pdo->prepare("UPDATE recipe SET image = ? WHERE recipe_id = ?");
        if ($stmt->execute([$recipePath, $recipe['recipe_id']])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Recipe image uploaded successfully',
                'imagePath' => $recipePath
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update recipe image in database.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No recipe found to update.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
