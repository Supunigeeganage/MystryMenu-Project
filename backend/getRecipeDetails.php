<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT recipe_id, name, type, ingredient, method, image 
                              FROM recipe 
                              WHERE recipe_id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $userId]);
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
    try {
        $stmt = $pdo->prepare("SELECT recipe_id, name, type, ingredient, method, image 
                              FROM recipe 
                              WHERE user_id = ? 
                              ORDER BY recipe_id DESC 
                              LIMIT 1");
        $stmt->execute([$userId]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($recipe) {
            echo json_encode([
                'status' => 'success',
                'recipe' => $recipe
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No recipe found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?> 