<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'success' => false,
        'message' => '',
    ];

    // Retrieve form data
    $recipeName = $_POST['recipe-name'] ?? '';
    $type = $_POST['type'] ?? '';
    $ingredient = $_POST['ingredient'] ?? '';
    $method = $_POST['method'] ?? '';

    // Validate inputs
    if (empty($recipeName) || empty($type) || empty($ingredient) || empty($method)) {
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }

    // Handle image upload
    $imagePath = '';
    if (!empty($_FILES['recipe-image']['name'])) {
        $imageTmpName = $_FILES['recipe-image']['tmp_name'];
        $imageName = basename($_FILES['recipe-image']['name']);
        $imagePath = 'uploads/' . $imageName;

        // Check for uploads directory
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Move the uploaded file
        if (!move_uploaded_file($imageTmpName, $imagePath)) {
            $response['message'] = 'Failed to upload image.';
            echo json_encode($response);
            exit;
        }
    }

    // Insert data into the database
    try {
        $sql = "INSERT INTO recipes (recipe_name, type, ingredient, method, image_path) 
                VALUES (:recipeName, :type, :ingredient, :method, :imagePath)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':recipeName' => $recipeName,
            ':type' => $type,
            ':ingredient' => $ingredient,
            ':method' => $method,
            ':imagePath' => $imagePath,
        ]);

        $response['success'] = true;
        $response['message'] = 'Recipe added successfully!';
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

    echo json_encode($response);
}
?>
