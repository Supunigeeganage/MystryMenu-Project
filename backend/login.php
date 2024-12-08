<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        header('Location: ../frontend/html/login.html?error=1');
        exit;
    }

    try {
        // Fetch user by email
        $stmt = $pdo->prepare("SELECT id, password_hash, user_type FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Location: ../frontend/html/login.html?error=1');
            exit;
        }

        // Verify the password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, create a session and store user data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user['user_type'];
            
            header('Location: ../frontend/html/dashboard.html?login=1');
            exit;
        } else {
            header('Location: ../frontend/html/login.html?error=1');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: ../frontend/html/login.html?error=1');
        exit;
    }
}
?>
