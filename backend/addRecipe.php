<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $response = [
            'success' => false,
            'message' => 'User not logged in',
        ];
        echo json_encode($response);
        exit;
    }

    $response = [
        'success' => false,
        'message' => '',
    ];

    // Retrieve form data
    $recipeName = $_POST['recipe-name'] ?? '';
    $type = $_POST['type'] ?? '';
    $ingredient = $_POST['ingredient'] ?? '';
    $method = $_POST['method'] ?? '';
    $userId = $_SESSION['user_id'];

    if (empty($recipeName) || empty($type) || empty($ingredient) || empty($method)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    // image upload
    $imagePath = '';
    if (isset($_FILES['recipe-image']) && $_FILES['recipe-image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['recipe-image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $uploadDir = __DIR__ . '/recipe_image/';
        $maxFileSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            $response['message'] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
            echo json_encode($response);
            exit;
        }

        if ($file['size'] > $maxFileSize) {
            $response['message'] = 'File size exceeds the 10MB limit.';
            echo json_encode($response);
            exit;
        }
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'recipe_' . $userId . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . $newFileName;

        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $response['message'] = 'Failed to upload image.';
            echo json_encode($response);
            exit;
        }

        $imagePath = '/recipe_image/' . $newFileName;
    }

    // Insert data into the database
    try {
        $sql = "INSERT INTO recipe (name, type, ingredient, method, image, user_id) 
                VALUES (:name, :type, :ingredient, :method, :image, :user_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $recipeName,
            ':type' => $type,
            ':ingredient' => $ingredient,
            ':method' => $method,
            ':image' => $imagePath,
            ':user_id' => $userId
        ]);

        $response['success'] = true;
        $response['message'] = 'Recipe added successfully!';
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

    echo json_encode($response);
}
?>
