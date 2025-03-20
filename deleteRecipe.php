<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require 'db.php';
require_once 'authMiddleware.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

// Check if recipe ID is provided
if ((!isset($data['recipe_id']) && !isset($data['recipeId']))) {
    echo json_encode(['status' => 'error', 'message' => 'Recipe ID not provided']);
    exit;
}

// Support both recipe_id and recipeId
$recipeId = isset($data['recipe_id']) ? $data['recipe_id'] : $data['recipeId'];
// Check if we're deleting from poisonous_recipes table
$isPoisonous = isset($data['type']) && $data['type'] === 'poisonous';

// For poisonous recipes require admin access
if ($isPoisonous) {
    checkUserType(['admin']);
}

try {
    // Check if the user is an admin
    $checkUserStmt = $pdo->prepare("SELECT user_type FROM Users WHERE id = ?");
    $checkUserStmt->execute([$userId]);
    $user = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
    $isAdmin = ($user && $user['user_type'] === 'admin');

    $pdo->beginTransaction();

    if ($isPoisonous) {
        // Get poisonous recipe info for the image path
        $recipeStmt = $pdo->prepare("SELECT image, user_id FROM poisonous_recipes WHERE poisonous_recipe_id = ?");
        $recipeStmt->execute([$recipeId]);
        $recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);
        
        // If recipe doesn't exist, return a success message
        if (!$recipe) {
            echo json_encode(['status' => 'success', 'message' => 'Recipe already deleted or does not exist']);
            exit;
        }

        // Delete the poisonous recipe
        $recipeDeleteStmt = $pdo->prepare("DELETE FROM poisonous_recipes WHERE poisonous_recipe_id = ?");
        $result = $recipeDeleteStmt->execute([$recipeId]);

        $pdo->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Poisonous recipe deleted successfully'
        ]);
    } else {
        // REGULAR RECIPE DELETION LOGIC
        // Get recipe info for the image path
        $recipeStmt = $pdo->prepare("SELECT image, user_id FROM recipe WHERE recipe_id = ?");
        $recipeStmt->execute([$recipeId]);
        $recipe = $recipeStmt->fetch(PDO::FETCH_ASSOC);
        
        // If recipe doesn't exist, return a success msg
        if (!$recipe) {
            echo json_encode(['status' => 'success', 'message' => 'Recipe already deleted or does not exist']);
            exit;
        }
        
        // Only admin or recipe owner can delete 
        if (!$isAdmin && $recipe && $recipe['user_id'] != $userId) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this recipe']);
            exit;
        }

        // hearts table constraint check delete
        $heartStmt = $pdo->prepare("DELETE FROM hearts WHERE recipe_id = ?");
        $heartStmt->execute([$recipeId]);
        $heartsDeleted = $heartStmt->rowCount();

        //if exist delete comments table
        $commentStmt = $pdo->prepare("DELETE FROM comments WHERE recipe_id = ?");
        $commentStmt->execute([$recipeId]);
        $commentsDeleted = $commentStmt->rowCount();
        
        // Delete image file if it exists and is not a default image
        if ($recipe && $recipe['image']) {
            error_log("Recipe image path: " . $recipe['image']);
            $defaultImagePatterns = [
                'recipe image when no picture is upladed',
            ];
            
            // Check if the image path contains any of the default image
            $isDefaultImage = false;
            foreach ($defaultImagePatterns as $pattern) {
                if (stripos($recipe['image'], $pattern) !== false) {
                    $isDefaultImage = true;
                    error_log("Default image detected: " . $recipe['image'] . " matches pattern: " . $pattern);
                    break;
                }
            }
            
            // Only delete the image if it's not a default image
            if (!$isDefaultImage) {
                $imagePath = __DIR__ . $recipe['image'];
                error_log("Attempting to delete non-default image: " . $imagePath);
                
                if (file_exists($imagePath)) {
                    if (unlink($imagePath)) {
                        error_log("Successfully deleted image: " . $imagePath);
                    } else {
                        error_log("Failed to delete image: " . $imagePath);
                    }
                } else {
                    error_log("Image file not found: " . $imagePath);
                }
            } else {
                error_log("Skipping deletion of default image: " . $recipe['image']);
            }
        }

        //delete the recipe
        $recipeDeleteStmt = $pdo->prepare("DELETE FROM recipe WHERE recipe_id = ?");
        $result = $recipeDeleteStmt->execute([$recipeId]);

        $pdo->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Recipe deleted successfully',
            'details' => [
                'hearts_deleted' => $heartsDeleted,
                'comments_deleted' => $commentsDeleted,
                'user_type' => $isAdmin ? 'admin' : 'user'
            ]
        ]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errorType = $e instanceof PDOException ? "Database" : "General";
    error_log("$errorType error in deleteRecipe.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => "$errorType error occurred: " . $e->getMessage()
    ]);
}
?> 