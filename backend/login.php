<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required.']);
        exit;
    }

    try {
        // Fetch user by email
        $stmt = $pdo->prepare("SELECT id, password_hash, user_type FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password.']);
            exit;
        }

        // Verify the password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user['user_type'];

            http_response_code(200);
            echo json_encode(['message' => 'Login successful']);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password.']);
            exit;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Something went wrong.']);
        exit;
    }
}

?>
