<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'you have to log in first']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT firstName, lastName, gender, profession, email, profilePicture, user_type FROM Users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'firstName' => $user['firstName'],
            'lastName' => $user['lastName'],
            'gender' => $user['gender'],
            'profession' => $user['profession'],
            'email' => $user['email'],
            'profilePicture' => $user['profilePicture'],
            'user_type' => $user['user_type']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch profile data.']);
}
?>
