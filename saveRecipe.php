<?php
session_start();
require 'db.php';

header('content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $recipe_id = $_POST['recipe_id'];
    $force_save = isset($_POST['force_save']) && $_POST['force_save'] === 'true';

    try {
        $stmt = $pdo->prepare("SELECT user_id FROM recipe WHERE recipe_id = ?");
        $stmt->execute([$recipe_id]);
        $recipe = $stmt->fetch();

        if ($recipe && $recipe['user_id'] == $user_id && !$force_save) {
            echo json_encode(['status' => 'own_recipe', 'message' => 'This is your own recipe. Are you sure you want to save it to your collection?']);
            exit;
        }

        // Check if recipe is already saved
        $stmt = $pdo->prepare("SELECT * FROM saved_recipes WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Recipe already saved to your collection']);
            exit;
        }

        // Save the recipe
        $stmt = $pdo->prepare("INSERT INTO saved_recipes (user_id, recipe_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $recipe_id]);

        echo json_encode(['status' => 'success', 'message' => 'Recipe saved successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?> 