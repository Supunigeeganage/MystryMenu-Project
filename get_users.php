<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $searchQuery = $_GET['query'] ?? '';
    
    if (empty($searchQuery)) {
        $stmt = $pdo->prepare("SELECT id, firstName, lastName, email, user_type FROM users");
        $stmt->execute();
    } else {
        // Search in firstName, lastName, and email
        $searchTerm = "%{$searchQuery}%";
        $stmt = $pdo->prepare(
            "SELECT id, firstName, lastName, email, user_type 
            FROM users 
            WHERE firstName LIKE ? 
            OR lastName LIKE ? 
            OR email LIKE ?"
        );
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    }

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'users' => $users]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch users.']);
}
?>
