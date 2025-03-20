<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$userId = null;

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $userId = $_GET['user_id'] ?? null;
} else {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;
}

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User ID missing']);
    exit;
}

// Prevent admin from deleting themselves
if ($userId == $_SESSION['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete your own account']);
    exit;
}

try {
    $pdo->beginTransaction();

    // if user exists and is not an admin
    $checkStmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
    $checkStmt->execute([$userId]);
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }

    if ($user['user_type'] === 'admin') {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Cannot delete admin users']);
        exit;
    }

    // Delete user's recipes first
    $recipeStmt = $pdo->prepare("DELETE FROM recipe WHERE user_id = ?");
    $recipeStmt->execute([$userId]);

    // Delete user's hearts
    $heartStmt = $pdo->prepare("DELETE FROM hearts WHERE user_id = ?");
    $heartStmt->execute([$userId]);

    // Delete user's comments
    $commentStmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
    $commentStmt->execute([$userId]);

    // Finally delete the user
    $userStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $userStmt->execute([$userId]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
}
?>
