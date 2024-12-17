<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT recipe_id, name, type, ingredient, method, image FROM recipe WHERE user_id = ? ORDER BY recipe_id DESC");
    $stmt->execute([$userId]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($recipes) {
        echo json_encode([
            'status' => 'success',
            'recipes' => $recipes
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No recipes found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 