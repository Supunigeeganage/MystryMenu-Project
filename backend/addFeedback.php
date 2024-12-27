<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'not_logged_in'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['recipe_id']) || !isset($data['comment'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO comments (user_id, recipe_id, comment) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $data['recipe_id'],
            $data['comment']
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Comment added successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 