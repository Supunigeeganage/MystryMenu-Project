<?php
session_start();

session_unset();
session_destroy();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Logged out successfully'
    ]);
} else {
    header('Location: frontend/html/login.html?logout=1');
}
exit;
