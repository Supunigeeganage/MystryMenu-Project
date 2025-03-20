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

    try {
        $stmt = $pdo->prepare("DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Recipe removed from saved recipes']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Saved recipe not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?> 