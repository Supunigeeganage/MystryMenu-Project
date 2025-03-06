<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in first']);
    exit;
}

$userId = $_SESSION['user_id'];
$isAdmin = $_SESSION['user_type'] === 'admin';

try {
    //an admin is viewing another user's profile
    if ($isAdmin && isset($_GET['user_id'])) {
        $targetUserId = $_GET['user_id'];
        
        // Admin viewing their own profile
        if ($targetUserId == $userId) {
            $stmt = $pdo->prepare("
                SELECT firstName, lastName, gender, profession, email, profilePicture, user_type 
                FROM Users WHERE id = ?
            ");
        } else {
            // Admin viewing other user's profile
            $stmt = $pdo->prepare("
                SELECT firstName, lastName, gender, profession, email, profilePicture 
                FROM Users WHERE id = ?
            ");
        }
        $stmt->execute([$targetUserId]);
    } else {
        // users viewing their own profile
        $stmt = $pdo->prepare("
            SELECT firstName, lastName, gender, profession, email, profilePicture, user_type 
            FROM Users WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // response with non-sensitive data
        $response = [
            'status' => 'success',
            'firstName' => htmlspecialchars($user['firstName']),
            'lastName' => htmlspecialchars($user['lastName']),
            'gender' => htmlspecialchars($user['gender']),
            'profession' => htmlspecialchars($user['profession']),
            'email' => htmlspecialchars($user['email']),
            'profilePicture' => $user['profilePicture']
        ];
        
        // Only include user_type for own profile
        if (isset($user['user_type']) && (!isset($_GET['user_id']) || $_GET['user_id'] == $userId)) {
            $response['user_type'] = $user['user_type'];
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch profile data']);
}
?>
