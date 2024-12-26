<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = $_GET['query'] ?? '';
    $isProfile = isset($_GET['isProfile']) ? filter_var($_GET['isProfile'], FILTER_VALIDATE_BOOLEAN) : false;
    $isSavedRecipes = isset($_GET['isSavedRecipes']) ? filter_var($_GET['isSavedRecipes'], FILTER_VALIDATE_BOOLEAN) : false;
    
    try {
        $searchTerm = "%$searchTerm%";
        
        if ($isProfile === true) {
            $stmt = $pdo->prepare("
                SELECT recipe_id, name, image, type, user_id 
                FROM recipe 
                WHERE user_id = ? 
                AND (name LIKE ? OR ingredient LIKE ? OR type LIKE ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $searchTerm, $searchTerm, $searchTerm]);
        } else if ($isSavedRecipes === true) {
            $stmt = $pdo->prepare("
                SELECT r.recipe_id, r.name, r.image, r.type, r.user_id 
                FROM recipe r
                INNER JOIN saved_recipes sr ON r.recipe_id = sr.recipe_id
                WHERE sr.user_id = ? 
                AND (r.name LIKE ? OR r.ingredient LIKE ? OR r.type LIKE ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $searchTerm, $searchTerm, $searchTerm]);
        } else {
            $stmt = $pdo->prepare("
                SELECT recipe_id, name, image, type, user_id 
                FROM recipe 
                WHERE name LIKE ? 
                OR ingredient LIKE ? 
                OR type LIKE ?
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'recipes' => $recipes,
            'user_id' => $_SESSION['user_id']
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 