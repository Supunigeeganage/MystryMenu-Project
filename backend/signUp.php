<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $gender = $_POST['gender'] ?? '';
    $profession = $_POST['profession'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $userType = 'user';

    try {
        $stmt = $pdo->prepare("INSERT INTO Users (firstName, lastName, gender, profession, email, password_hash, user_type)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $gender, $profession, $email, $password, $userType]);
        header('Location: ../frontend/html/login.html?registered=1');
        exit;
    } catch (PDOException $e) {
        // Handle duplicate email error
        if ($e->getCode() == 23000) {
            header('Location: ../frontend/html/signup.html?exists=1');
        } else {
            header('Location: ../frontend/html/signup.html?error=1');
        }
        exit;
    }
}
?>
