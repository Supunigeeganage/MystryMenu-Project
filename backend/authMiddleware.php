<?php
session_start();
require 'db.php';

function getUserProfile($userId, $allowedUserTypes = []) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT firstName, lastName, gender, profession, email, profilePicture, user_type FROM Users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || (!empty($allowedUserTypes) && !in_array($user['user_type'], $allowedUserTypes))) {
        return null;
    }
    return $user;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'not_logged_in']);
    exit;
}

try {
    $user = getUserProfile($_SESSION['user_id'], ['admin', 'user']);
    if ($user) {
        echo json_encode(['status' => 'success', 'data' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error.']);
}
?> 