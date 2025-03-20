<?php
require_once 'db.php';
require_once 'authMiddleware.php';
checkUserType(['admin']);

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['recipeId']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Recipe ID and status are required'
    ]);
    exit;
}

$recipeId = $data['recipeId'];
$status = $data['status'];

// Validate status
if (!in_array($status, ['pending', 'approved', 'rejected'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // If status is 'approved', move recipe to regular recipe table and delete from poisonous_recipes
    if ($status === 'approved') {
        // get the recipe details
        $getRecipeStmt = $pdo->prepare("SELECT * FROM poisonous_recipes WHERE poisonous_recipe_id = :id");
        $getRecipeStmt->execute([':id' => $recipeId]);
        $recipe = $getRecipeStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$recipe) {
            throw new PDOException('Recipe not found');
        }
        
        // Insert into regular recipe table
        $insertStmt = $pdo->prepare("
            INSERT INTO recipe (name, type, ingredient, method, image, user_id, poisonous) 
            VALUES (:name, :type, :ingredient, :method, :image, :user_id, 'no')
        ");
        
        $insertResult = $insertStmt->execute([
            ':name' => $recipe['name'],
            ':type' => $recipe['type'],
            ':ingredient' => $recipe['ingredient'],
            ':method' => $recipe['method'],
            ':image' => $recipe['image'],
            ':user_id' => $recipe['user_id']
        ]);
        
        if (!$insertResult) {
            throw new PDOException('Failed to insert recipe into regular table');
        }
        
        // Delete from poisonous_recipes table
        $deleteStmt = $pdo->prepare("DELETE FROM poisonous_recipes WHERE poisonous_recipe_id = :id");
        $deleteResult = $deleteStmt->execute([':id' => $recipeId]);
        
        if (!$deleteResult) {
            throw new PDOException('Failed to delete from poisonous recipes table');
        }
        
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Recipe approved and moved to regular recipes'
        ]);
    } else {
        // Just update the status for rejected or pending
        $sql = "UPDATE poisonous_recipes SET status = :status WHERE poisonous_recipe_id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':status' => $status,
            ':id' => $recipeId
        ]);

        if (!$result) {
            throw new PDOException('Failed to update recipe status');
        }
        
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Recipe status updated to ' . $status
        ]);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Database error in updatePoisonousRecipeStatus.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 