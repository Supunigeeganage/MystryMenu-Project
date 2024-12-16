<?php
header('Content-Type: application/json');
error_reporting(0);

try {
    require 'db.php';
    session_start();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
    $result = $stmt->execute([$userId]);
    
    if ($result) {
        $pdo->commit();
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Profile deleted successfully']);
    } else {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete profile']);
    }
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'An error occurred']);
}
?>
