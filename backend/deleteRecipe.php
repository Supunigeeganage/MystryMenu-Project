<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

if (!isset($data['recipe_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Recipe ID not provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT image FROM recipe WHERE recipe_id = ? AND user_id = ?");
    $stmt->execute([$data['recipe_id'], $userId]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recipe && $recipe['image']) {
        $imagePath = __DIR__ . $recipe['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM recipe WHERE recipe_id = ? AND user_id = ?");
    if ($stmt->execute([$data['recipe_id'], $userId])) {
        echo json_encode(['status' => 'success', 'message' => 'Recipe deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete recipe']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 