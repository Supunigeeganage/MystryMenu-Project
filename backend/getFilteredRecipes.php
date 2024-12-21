<?php
require 'db.php';

$type = $_GET['type'] ?? 'all';

try {
    if ($type === 'all') {
        $stmt = $pdo->prepare("SELECT recipe_id, name, image, type, ingredient, method FROM recipe");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT recipe_id, name, image, type, ingredient, method FROM recipe WHERE type = ?");
        $stmt->execute([$type]);
    }
    
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'recipes' => $recipes
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 