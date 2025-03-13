<?php
require_once 'db.php';
require_once 'authMiddleware.php';

// Check if user is admin or user
checkUserType(['admin', 'user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Retrieve form data
    $recipeName = $_POST['recipe-name'] ?? '';
    $type = $_POST['type'] ?? '';
    $ingredient = $_POST['ingredient'] ?? '';
    $method = $_POST['method'] ?? '';
    $userId = $_SESSION['user_id'];
    $isOverride = isset($_POST['override_warning']) && $_POST['override_warning'] === 'true';

    if (empty($recipeName) || empty($type) || empty($ingredient) || empty($method)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    // image upload
    $imagePath = '/recipe_image/recipe image when no picture is upladed.jpg';
    if (isset($_FILES['recipe-image']) && $_FILES['recipe-image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['recipe-image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $uploadDir = __DIR__ . '/recipe_image/';
        $maxFileSize = 10 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'
            ]);
            exit;
        }

        if ($file['size'] > $maxFileSize) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'File size exceeds the 10MB limit.'
            ]);
            exit;
        }
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'recipe_' . $userId . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . $newFileName;

        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image.'
            ]);
            exit;
        }

        $imagePath = '/recipe_image/' . $newFileName;
    }

    // Poisonous Ingredient Check
    try {
        $isPoisonous = false;
        $poisonousIngredients = [];
        
        // Get all poisonous ingredients from database
        $sql = "SELECT name FROM poisonous_ingredients";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $dbPoisonousIngredients = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // temporarily replace commas inside parentheses with ### characters
        $modifiedIngredient = preg_replace_callback('/\([^)]+\)/', function($matches) {
            return str_replace(',', '###', $matches[0]);
        }, $ingredient);

        // split by commas and restore the original commas
        $userIngredients = array_map(function($item) {
            return str_replace('###', ',', trim($item));
        }, explode(',', $modifiedIngredient));
        
        // to see how it happens logs
        error_log("Original ingredient string: " . $ingredient);
        error_log("Modified ingredient string: " . $modifiedIngredient);
        error_log("Split ingredients: " . print_r($userIngredients, true));
        
        // Check each ingredient with the database list
        foreach ($userIngredients as $userIngredient) {
            $cleanUserIngredient = trim($userIngredient);
            
            foreach ($dbPoisonousIngredients as $dbIngredient) {
                if (strcasecmp($cleanUserIngredient, trim($dbIngredient)) === 0) {
                    $poisonousIngredients[] = $dbIngredient;
                    error_log("Match found - User: '$cleanUserIngredient' DB: '$dbIngredient'");
                    break;
                }
            }
        }
        
        if (!empty($poisonousIngredients)) {
            $isPoisonous = true;
            // Only show warning if not overriding
            if (!$isOverride) {
                echo json_encode([
                    'success' => false,
                    'warning' => true,
                    'poisonous' => true,
                    'ingredients' => $poisonousIngredients,
                    'message' => 'The following ingredients are potentially poisonous: ' . implode(', ', $poisonousIngredients)
                ]);
                exit;
            }
        }

        // If we get here, either there are no poisonous ingredients or we're overriding
        if ($isPoisonous) {
            $sql = "INSERT INTO poisonous_recipes (name, type, ingredient, method, image, user_id, poisonous, status) 
                    VALUES (:name, :type, :ingredient, :method, :image, :user_id, :poisonous, :status)";
            $message = 'Recipe submitted for admin review';
            
            $params = [
                ':name' => $recipeName,
                ':type' => $type,
                ':ingredient' => $ingredient,
                ':method' => $method,
                ':image' => $imagePath,
                ':user_id' => $userId,
                ':poisonous' => 'yes',
                ':status' => 'pending'
            ];
        } else {
            $sql = "INSERT INTO recipe (name, type, ingredient, method, image, user_id, poisonous) 
                    VALUES (:name, :type, :ingredient, :method, :image, :user_id, :poisonous)";
            $message = 'Recipe added successfully!';
            
            $params = [
                ':name' => $recipeName,
                ':type' => $type,
                ':ingredient' => $ingredient,
                ':method' => $method,
                ':image' => $imagePath,
                ':user_id' => $userId,
                ':poisonous' => 'no'
            ];
        }
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if (!$result) {
            error_log('Failed to insert recipe. SQL: ' . $sql);
            error_log('PDO Error Info: ' . print_r($stmt->errorInfo(), true));
            throw new PDOException('Failed to insert recipe: ' . $stmt->errorInfo()[2]);
        }

        echo json_encode([
            'success' => true,
            'isAdmin' => $_SESSION['user_type'] === 'admin',
            'isPoisonous' => $isPoisonous,
            'message' => $message
        ]);
    } catch (PDOException $e) {
        error_log('Database error in addRecipe.php: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
