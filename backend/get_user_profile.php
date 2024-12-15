<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/html/login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT firstName, lastName, gender, profession, email, profilePicture FROM Users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch profile data.']);
}
?>
