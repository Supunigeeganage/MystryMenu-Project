<?php
session_start();
$protectedPages = [
    'dashboard.html' => ['admin', 'user'],
    'profile.html' => ['admin', 'user'],
    'add_recipe.html' => ['admin','user'],
    'edit_recipe.html' => ['admin', 'user'],
    'edit_profile.html'=> ['admin','user'],
    'edit_recipe.html'=>['admin','user'],
    'save_recipe.html'=>['admin','user'],
    'share_recipe.html'=>['admin','user'],
    'recipeManagment.html' => ['admin'],
    'userManagement.html' => ['admin'],
    'poisonousRecipes.html' => ['admin']
];

// check user type
function checkUserType($allowedTypes = []) {
    if (!isset($_SESSION['user_type']) || empty($allowedTypes) || !in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
    return true;
}

// admin page checks with js and authmiddleware
if (basename($_SERVER['PHP_SELF']) === 'authMiddleware.php') {
    header('Content-Type: application/json');
    if (isset($_SESSION['user_type'])) {
        $allowedPages = [];
        foreach ($protectedPages as $page => $types) {
            if (in_array($_SESSION['user_type'], $types)) {
                $allowedPages[] = $page;
            }
        }

        echo json_encode([
            'user_type' => $_SESSION['user_type'],
            'allowedPages' => $allowedPages
        ]);
    }  else {
        http_response_code(401);
        echo json_encode(['error' => 'Not authorized']);
    }
    exit;
}

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
        $stmt = $pdo->prepare("SELECT firstName, lastName, gender, profession, email, profilePicture, user_type FROM users WHERE id = ?");
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