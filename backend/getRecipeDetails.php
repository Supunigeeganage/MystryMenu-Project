<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        try {
            // Get recipe details and heart count (all recipe details)
            $stmt = $pdo->prepare("
                SELECT r.*,
                       (SELECT COUNT(*) FROM hearts WHERE recipe_id = r.recipe_id) as hearts,
                       EXISTS(SELECT 1 FROM hearts WHERE user_id = ? AND recipe_id = r.recipe_id) as hasHearted
                FROM recipe r 
                WHERE r.recipe_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $_GET['id']]);
            $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($recipe) {
                echo json_encode([
                    'status' => 'success',
                    'recipe' => $recipe,
                    'hasHearted' => (bool)$recipe['hasHearted'],
                    'hearts' => (int)$recipe['hearts']
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Recipe not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    }
} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['recipe_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Recipe ID required']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT 1 FROM hearts WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$_SESSION['user_id'], $data['recipe_id']]);
        $hasHearted = $stmt->fetch();

        if ($hasHearted) {
            $stmt = $pdo->prepare("DELETE FROM hearts WHERE user_id = ? AND recipe_id = ?");
        } else {
            $stmt = $pdo->prepare("INSERT INTO hearts (user_id, recipe_id) VALUES (?, ?)");
        }
        $stmt->execute([$_SESSION['user_id'], $data['recipe_id']]);

        // Get updated heart count
        $stmt = $pdo->prepare("SELECT COUNT(*) as hearts FROM hearts WHERE recipe_id = ?");
        $stmt->execute([$data['recipe_id']]);
        $hearts = $stmt->fetch(PDO::FETCH_ASSOC)['hearts'];

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'hearts' => $hearts,
            'action' => $hasHearted ? 'removed' : 'added'
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>
