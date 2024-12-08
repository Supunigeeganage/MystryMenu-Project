<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $gender = $_POST['gender']?? '';;
    $profession = $_POST['profession'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $userType = 'user'; // Default to 'user'

    try {
        $stmt = $pdo->prepare("INSERT INTO Users (firstName, lastName, gender, profession, email, password_hash, user_type)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $gender, $profession, $email, $password, $userType]);
        echo "Registration successful!";
    } catch (PDOException $e) {
        // Handle duplicate email error
        if ($e->getCode() == 23000) {
            echo "Error: Email already registered.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
