<?php
require_once 'db.php';
require_once 'authMiddleware.php';
checkUserType(['user', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    //  checking what POST is sending 
    //error_log("Update Recipe POST data: " . print_r($_POST, true));
    
    // Check if it's a poisonous recipe update (admin only)
    $isPoisonous = isset($_GET['type']) && $_GET['type'] === 'poisonous';
    if ($isPoisonous) {
        checkUserType(['admin']);
    }
    
    // Retrieve form data
    $recipeId = $_POST['recipe_id'] ?? '';
    $recipeName = $_POST['recipe-name'] ?? '';
    $type = $_POST['type'] ?? '';
    $ingredient = $_POST['ingredient'] ?? '';
    $method = $_POST['method'] ?? '';
    
    // cheking the extracted recipe ID
    //error_log("Recipe ID for update: " . $recipeId);
    
    if (empty($recipeId) || empty($recipeName) || empty($type) || empty($ingredient) || empty($method)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required. Recipe ID: ' . $recipeId
        ]);
        exit;
    }
    
    try {
        // fougurigng which table to use
        $table = $isPoisonous ? 'poisonous_recipes' : 'recipe';
        $idField = $isPoisonous ? 'poisonous_recipe_id' : 'recipe_id';
        
        // Verify the recipe exists before updating
        $checkExistsSql = "SELECT COUNT(*) FROM $table WHERE $idField = :id";
        $checkStmt = $pdo->prepare($checkExistsSql);
        $checkStmt->execute([':id' => $recipeId]);
        $recipeExists = (int)$checkStmt->fetchColumn();
        
        if (!$recipeExists) {
            echo json_encode([
                'success' => false,
                'message' => "Recipe not found with ID: $recipeId in table $table"
            ]);
            exit;
        }
        
        // Handle image upload if provided
        $imagePath = null;
        if (isset($_FILES['recipe-image']) && $_FILES['recipe-image']['error'] === UPLOAD_ERR_OK) {
            // Get current image path
            $currentImageSql = "SELECT image FROM $table WHERE $idField = :id";
            $currentImageStmt = $pdo->prepare($currentImageSql);
            $currentImageStmt->execute([':id' => $recipeId]);
            $currentImage = $currentImageStmt->fetchColumn();
            
            $file = $_FILES['recipe-image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $uploadDir = __DIR__ . '/recipe_image/';
            $maxFileSize = 10 * 1024 * 1024;

            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'
                ]);
                exit;
            }

            if ($file['size'] > $maxFileSize) {
                echo json_encode([
                    'success' => false,
                    'message' => 'File size exceeds the 10MB limit.'
                ]);
                exit;
            }
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'recipe_' . time() . '.' . $ext;
            $filePath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $imagePath = '/recipe_image/' . $newFileName;
                
                // Delete old image if it exists and is not the default
                if ($currentImage && strpos($currentImage, 'no picture is upladed') === false) {
                    $oldImagePath = __DIR__ . $currentImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to upload image.'
                ]);
                exit;
            }
        } else {
            // Get current image path if no new image uploaded
            $imageSql = "SELECT image FROM $table WHERE $idField = :id";
            $imageStmt = $pdo->prepare($imageSql);
            $imageStmt->execute([':id' => $recipeId]);
            $imagePath = $imageStmt->fetchColumn();
        }
        
        // checking ingredients of poisonous recipes after update
        $poisonousIngredients = [];
        if ($isPoisonous) {
            // Get all poisonous ingredients from database
            $poisonousSql = "SELECT name FROM poisonous_ingredients";
            $poisonousStmt = $pdo->prepare($poisonousSql);
            $poisonousStmt->execute();
            $allPoisonousIngredients = $poisonousStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Temporarily replace commas inside parentheses with ### characters
            $modifiedIngredient = preg_replace_callback('/\([^)]+\)/', function($matches) {
                return str_replace(',', '###', $matches[0]);
            }, $ingredient);
            
            // Replace and, or with commas to get them as separate ingredients
            $modifiedIngredient = preg_replace('/ and | or /i', ',', $modifiedIngredient);
            
            // Split by commas and restore the original commas
            $userIngredients = array_map(function($item) {
                return str_replace('###', ',', trim($item));
            }, explode(',', $modifiedIngredient));
            
            // to avoid duplicates
            $processedIngredients = [];
            
            // Check each ingredient with the database list
            foreach ($userIngredients as $userIngredient) {
                $cleanUserIngredient = trim($userIngredient);
                
                // Skip empty ingredients or ones we've already processed
                if (empty($cleanUserIngredient) || in_array($cleanUserIngredient, $processedIngredients)) {
                    continue;
                }
                
                $processedIngredients[] = $cleanUserIngredient;
                
                foreach ($allPoisonousIngredients as $poisonousIngredient) {
                    // Check for exact match or if poisonous ingredient in the recipe
                    if (strcasecmp($cleanUserIngredient, trim($poisonousIngredient)) === 0 || 
                        stripos($cleanUserIngredient, trim($poisonousIngredient)) !== false) {
                        // Only add if not already in the list
                        if (!in_array($poisonousIngredient, $poisonousIngredients)) {
                            $poisonousIngredients[] = $poisonousIngredient;
                            error_log("Match found - User: '$cleanUserIngredient' DB: '$poisonousIngredient'");
                        }
                        break;
                    }
                }
            }
        }
        
        // makeing sure updating an existing recipe
        $sql = "UPDATE $table SET 
                name = :name, 
                type = :type, 
                ingredient = :ingredient, 
                method = :method";
                
        // Only include image in update if have one already
        if ($imagePath) {
            $sql .= ", image = :image";
        }
        
        // update a specific recipe
        $sql .= " WHERE $idField = :id";
                
        $params = [
            ':name' => $recipeName,
            ':type' => $type,
            ':ingredient' => $ingredient,
            ':method' => $method,
            ':id' => $recipeId
        ];
        
        if ($imagePath) {
            $params[':image'] = $imagePath;
        }
        
        // checking the SQL and parameters
        error_log("Update SQL: " . $sql);
        error_log("Parameters: " . print_r($params, true));
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Recipe updated successfully',
                'recipe_id' => $recipeId
            ];
            
            // Add poisonous ingredients to response if needed
            if ($isPoisonous) {
                $response['poisonousIngredients'] = $poisonousIngredients;
            }
            
            echo json_encode($response);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update recipe. Error: ' . implode(', ', $stmt->errorInfo())
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error in updateRecipe.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 