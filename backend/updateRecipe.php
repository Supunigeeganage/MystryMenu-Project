<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$recipeId = $_POST['recipe_id'] ?? null;

if (!$recipeId) {
    echo json_encode(['success' => false, 'message' => 'Recipe ID not provided']);
    exit;
}

try {
    $imagePath = null;
    if (isset($_FILES['recipe-image']) && $_FILES['recipe-image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['recipe-image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $uploadDir = __DIR__ . '/recipe_image/';
        $maxFileSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }

        if ($file['size'] > $maxFileSize) {
            echo json_encode(['success' => false, 'message' => 'File too large']);
            exit;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'recipe_' . $userId . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $imagePath = '/recipe_image/' . $newFileName;
        }
    }

    $sql = "UPDATE recipe SET 
            name = :name,
            type = :type,
            ingredient = :ingredient,
            method = :method" .
            ($imagePath ? ", image = :image" : "") .
            " WHERE recipe_id = :recipe_id AND user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    
    $params = [
        ':name' => $_POST['recipe-name'],
        ':type' => $_POST['type'],
        ':ingredient' => $_POST['ingredient'],
        ':method' => $_POST['method'],
        ':recipe_id' => $recipeId,
        ':user_id' => $userId
    ];

    if ($imagePath) {
        $params[':image'] = $imagePath;
    }

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Recipe updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update recipe']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 