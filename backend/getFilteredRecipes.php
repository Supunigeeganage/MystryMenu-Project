<?php
require 'db.php';

$type = $_GET['type'] ?? 'all';

try {
    //when beginer button clicks its filter recipes with less than 4 comma seperated ingredients
    if ($type === 'beginner') {
        $stmt = $pdo->prepare("
            SELECT recipe_id, name, image, type, ingredient, method 
            FROM recipe 
            WHERE LENGTH(ingredient) - LENGTH(REPLACE(ingredient, ',', '')) < 4
        ");
        $stmt->execute();
    } else if ($type === 'all') {
        $stmt = $pdo->prepare("SELECT recipe_id, name, image, type, ingredient, method FROM recipe");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT recipe_id, name, image, type, ingredient, method FROM recipe WHERE type = ?");
        $stmt->execute([$type]);
    }
    
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Always return success, even with empty recipes array
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'recipes' => $recipes,
        'count' => count($recipes)
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'recipes' => []
    ]);
}
?> 