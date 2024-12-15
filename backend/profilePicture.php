<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];

if (!isset($_FILES['profilePicture']) || $_FILES['profilePicture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or an error occurred.']);
    exit;
}

$file = $_FILES['profilePicture'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$uploadDir = __DIR__ . '/profile_picture_uploads/';
$maxFileSize = 10 * 1024 * 1024;   

// check  file type and the size 
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
    exit;
}
if ($file['size'] > $maxFileSize) {
    echo json_encode(['status' => 'error', 'message' => 'File size exceeds the 10MB limit.']);
    exit;
}

// Generate a unique file name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'profile_' . $userId . '_' . time() . '.' . $ext;

// Move the file to the upload directory
if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save the uploaded file.']);
    exit;
}

// Update the database
$profilePicturePath = '/profile_picture_uploads/' . $newFileName;
$stmt = $pdo->prepare("UPDATE Users SET profilePicture = ? WHERE id = ?");
if ($stmt->execute([$profilePicturePath, $userId])) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Profile picture updated successfully', 
        'profilePicture' => $profilePicturePath
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile picture in the database.']);
}
?>
