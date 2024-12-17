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

    // Validate inputs
    if (empty($recipeName) || empty($type) || empty($ingredient) || empty($method)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['recipe-image']) && $_FILES['recipe-image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'recipe_Image/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Get file information
        $fileName = $_FILES['recipe-image']['name'];
        $tmpName = $_FILES['recipe-image']['tmp_name'];
        $fileSize = $_FILES['recipe-image']['size'];
        $fileType = $_FILES['recipe-image']['type'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($fileType, $allowedTypes)) {
            $response['message'] = 'Invalid file type. Only JPG, JPEG & PNG files are allowed.';
            echo json_encode($response);
            exit;
        }

        // Generate unique filename
        $imageExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueFilename = uniqid() . '.' . $imageExtension;
        $imagePath = $uploadDir . $uniqueFilename;

        // Move the uploaded file
        if (!move_uploaded_file($tmpName, $imagePath)) {
            $response['message'] = 'Failed to upload image.';
            echo json_encode($response);
            exit;
        }
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
