<?php
session_start();

// Check if user is logged in and is an admin
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    echo json_encode(['user_type' => $_SESSION['user_type']]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized']);
}
$protectedPages = [
    'dashboard.html' => ['admin', 'user'],
    'profile.html' => ['admin', 'user'],
    'add_recipe.html' => ['admin','user'],
    'edit_recipe.html' => ['admin', 'user'],
    'edit_profile.html'=> ['admin','user'],
    'edit_recipe.html'=>['admin','user'],
    'save_recipe.html'=>['admin','user'],
    'share_recipe.html'=>['admin','user'],
    'admin_view.html' => ['admin'],
    'admin_view2.html' => ['admin'],
    'admin_view3.html' => ['admin']
];

function getUserProfile($userId, $allowedUserTypes = []) {
    global $pdo;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    // Check user type
    if (!empty($allowedUserTypes) && !in_array($_SESSION['user_type'], $allowedUserTypes)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'you do not have access for this feature']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT firstName, lastName, gender, profession, email, profilePicture, user_type FROM Users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        return $user;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
}
?> 