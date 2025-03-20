<?php
session_start();
require 'db.php';
header('content-Type:application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    //load all recipies here all recipe order if wanted can be changed desending order selected for now 
    $stmt = $pdo->prepare("
        SELECT r.* 
        FROM recipe r 
        JOIN saved_recipes s ON r.recipe_id = s.recipe_id 
        WHERE s.user_id = ?
        ORDER BY s.saved_at DESC 
    ");
    $stmt->execute([$user_id]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get last saved from the first recipe
    $lastSaved = !empty($recipes) ? $recipes[0] : null;

    echo json_encode([
        'status' => 'success', 
        'recipes' => $recipes,
        'lastSaved' => $lastSaved
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 