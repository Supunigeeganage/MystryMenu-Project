<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT recipe_id, name, type, ingredient, method, image 
                              FROM recipe 
                              WHERE recipe_id = ?");
        $stmt->execute([$_GET['id']]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($recipe) {
            echo json_encode([
                'status' => 'success',
                'recipe' => $recipe
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Recipe not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No recipe ID provided']);
}
?> 