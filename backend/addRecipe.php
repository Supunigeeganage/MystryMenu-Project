<?php
require_once 'db.php';
require_once 'authMiddleware.php';

//permissioned user
getUserProfile($_SESSION['user_id'], ['admin','user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Retrieve form data
    $recipeName = $_POST['recipe-name'] ?? '';
    $type = $_POST['type'] ?? '';
    $ingredient = $_POST['ingredient'] ?? '';
    $method = $_POST['method'] ?? '';
    $userId = $_SESSION['user_id'];

    if (empty($recipeName) || empty($type) || empty($ingredient) || empty($method)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    // image upload
    $imagePath = '/recipe_image/recipe image when no picture is upladed.jpg';
    if (isset($_FILES['recipe-image']) && $_FILES['recipe-image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['recipe-image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $uploadDir = __DIR__ . '/recipe_image/';
        $maxFileSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'
            ]);
            exit;
        }

        if ($file['size'] > $maxFileSize) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'File size exceeds the 10MB limit.'
            ]);
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
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image.'
            ]);
            exit;
        }

        $imagePath = '/recipe_image/' . $newFileName;
    }

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

        echo json_encode([
            'success' => true,
            'message' => 'Recipe added successfully!'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
