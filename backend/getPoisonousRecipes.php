<?php
require_once 'db.php';
require_once 'authMiddleware.php';
checkUserType(['admin']);

header('Content-Type: application/json');

try { // Get all poisonous recipes
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
    
    // Get all poisonous ingredients from the database
    $ingredientsSql = "SELECT name FROM poisonous_ingredients";
    $ingredientsStmt = $pdo->prepare($ingredientsSql);
    $ingredientsStmt->execute();
    $dbPoisonousIngredients = $ingredientsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // For each recipe find poisonous ingredients
    foreach ($recipes as &$recipe) {
        $recipe['poisonous_ingredients'] = [];
        
        // inside brackets commas are replaced with ###
        $modifiedIngredient = preg_replace_callback('/\([^)]+\)/', function($matches) {
            return str_replace(',', '###', $matches[0]);
        }, $recipe['ingredient']);
        
        // Split by commas and restore original commas
        $ingredients = array_map(function($item) {
            return str_replace('###', ',', trim($item));
        }, explode(',', $modifiedIngredient));
        
        // Check each ingredient with the poisonous ingredients table 
        foreach ($ingredients as $ingredient) {
            $cleanIngredient = trim($ingredient);
            foreach ($dbPoisonousIngredients as $poisonousIngredient) {
                if (strcasecmp($cleanIngredient, trim($poisonousIngredient)) === 0) {
                    $recipe['poisonous_ingredients'][] = $cleanIngredient;
                    break;
                }
            }
        }
    }

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