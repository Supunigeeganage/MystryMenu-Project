<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: frontend/html/forgot_password.html?error=session_expired");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the passwords
    if (empty($new_password) || empty($confirm_password)) {
        header('Location: frontend/html/set_password.html?error=empty_fields');
        exit;
    }

    if ($new_password !== $confirm_password) {
        header('Location: frontend/html/set_password.html?error=password_mismatch');
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    try {
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);

        // Remove OTP and expire
        $stmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expire = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        session_destroy();

        header("Location: frontend/html/login.html?success=password_reset");
        exit;

        // to find the error created a log 
    } catch (PDOException $e) {
        $logFile = '../logs/error_log.txt';  
        $handle = fopen($logFile, 'a');
        
        $errorMessage = "Database error: " . $e->getMessage(); 
        fwrite($handle, "[" . date('Y-m-d H:i:s') . "] " . $errorMessage . PHP_EOL); 
        
        fclose($handle); 

        header("Location: frontend/html/set_password.html?error=database_error");
        exit;
    }
}
?>
