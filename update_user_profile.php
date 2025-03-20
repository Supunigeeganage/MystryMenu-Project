<?php
require 'db.php';
session_start();

header('content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

// Handle password change
if (isset($data['action']) && $data['action'] === 'changePassword') {
    if (!isset($data['currentPassword'], $data['newPassword'], $data['confirmPassword'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing password data']);
        exit;
    }

    // Verify current password
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['currentPassword'], $user['password_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        exit;
    }

    // Update password
    $hashedPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE Users SET password_hash = ? WHERE id = ?");
    if ($stmt->execute([$hashedPassword, $userId])) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
    }
}
// Handle profile update
else if (isset($data['action']) && $data['action'] === 'updateProfile') {
    if (!isset($data['firstName'], $data['lastName'], $data['profession'], $data['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing profile data']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET firstName = ?, lastName = ?, profession = ?, email = ? WHERE id = ?");
    if ($stmt->execute([
        $data['firstName'],
        $data['lastName'],
        $data['profession'],
        $data['email'],
        $userId
    ])) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>