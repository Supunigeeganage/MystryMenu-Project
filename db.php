<?php
$host = 'sql310.ezyro.com';
$dbname = 'ezyro_38517786_recipe';
$username = 'ezyro_38517786';
$password = '137ae53e7';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>