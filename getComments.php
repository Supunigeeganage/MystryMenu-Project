<?php
session_start();
require_once 'db.php';
header('content-Type:application/json');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['recipe_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Recipe ID is required'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, u.firstName, u.lastName
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.recipe_id = ?
            ORDER BY c.comment_id DESC
        ");
        
        $stmt->execute([$_GET['recipe_id']]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'comments' => $comments
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 