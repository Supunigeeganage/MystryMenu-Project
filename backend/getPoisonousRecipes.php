<?php
require_once 'db.php';
require_once 'authMiddleware.php';
checkUserType(['admin']);

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM poisonous_recipes 
            ORDER BY 
                CASE status 
                    WHEN 'pending' THEN 1 
                    WHEN 'approved' THEN 2 
                    WHEN 'rejected' THEN 3 
                END,
                poisonous_recipe_id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'recipes' => $recipes
    ]);
} catch (PDOException $e) {
    error_log('Database error in getPoisonousRecipes.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 