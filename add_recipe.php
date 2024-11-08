<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = getDBConnection();
        $conn->beginTransaction();
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === 0) {
            $uploadDir = "uploads/recipes/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['recipe_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['recipe_image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            }
        }
        
        // Insert recipe
        $stmt = $conn->prepare("
            INSERT INTO recipes (user_id, title, description, prep_time, cook_time, servings, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            sanitizeInput($_POST['title']),
            sanitizeInput($_POST['description']),
            sanitizeInput($_POST['prep_time']),
            sanitizeInput($_POST['cook_time']),
            (int)$_POST['servings'],
            $imagePath
        ]);
        
        $recipeId = $conn->lastInsertId();
        
        // Insert ingredients
        if (isset($_POST['ingredients']) && is_array($_POST['ingredients'])) {
            $stmt = $conn->prepare("
                INSERT INTO ingredients (recipe_id, name, quantity, unit) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($_POST['ingredients'] as $i => $ingredient) {
                if (!empty($ingredient)) {
                    $stmt->execute([
                        $recipeId,
                        sanitizeInput($ingredient),
                        sanitizeInput($_POST['quantities'][$i]),
                        sanitizeInput($_POST['units'][$i])
                    ]);
                }
            }
        }
        
        // Insert steps
        if (isset($_POST['steps']) && is_array($_POST['steps'])) {
            $stmt = $conn->prepare("
                INSERT INTO steps (recipe_id, step_number, description) 
                VALUES (?, ?, ?)
            ");
            
            foreach ($_POST['steps'] as $i => $step) {
                if (!empty($step)) {
                    $stmt->execute([
                        $recipeId,
                        $i + 1,
                        sanitizeInput($step)
                    ]);
                }
            }
        }
        
        $conn->commit();
        
        $_SESSION['flash_message'] = "Recipe added successfully!";
        $_SESSION['flash_type'] = "success";
        
        redirect('view_recipe.php?id=' . $recipeId);
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed to add recipe: " . $e->getMessage();
        // If there was an uploaded image, remove it
        if ($imagePath && file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe - Recipe Hub</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/recipe-form.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="recipe-form-container">
        <h1>Add New Recipe</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="add_recipe.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="recipe_image">Recipe Image:</label>
                <input type="file" id="recipe_image" name="recipe_image" accept="image/*" onchange="previewImage(this)">
                <img id="image-preview" class="image-preview" alt="Preview">
            </div>
            
            <div class="form-group">
                <label for="prep_time">Preparation Time:</label>
                <input type="text" id="prep_time" name="prep_time" placeholder="e.g., 30 minutes" required>
            </div>
            
            <div class="form-group">
                <label for="cook_time">Cooking Time:</label>
                <input type="text" id="cook_time" name="cook_time" placeholder="e.g., 1 hour" required>
            </div>
            
            <div class="form-group">
                <label for="servings">Servings:</label>
                <input type="number" id="servings" name="servings" min="1" required>
            </div>
            
            <div class="ingredients-section">
                <h2>Ingredients</h2>
                <div id="ingredients-list">
                    <div class="ingredient-row">
                        <input type="text" name="ingredients[]" placeholder="Ingredient" required>
                        <input type="text" name="quantities[]" placeholder="Quantity" required>
                        <input type="text" name="units[]" placeholder="Unit" required>
                        <button type="button" class="btn btn-remove" onclick="removeIngredient(this)">Remove</button>
                    </div>
                </div>
                <button type="button" class="btn btn-add" onclick="addIngredient()">Add Ingredient</button>
            </div>
            
            <div class="steps-section">
                <h2>Steps</h2>
                <div id="steps-list">
                    <div class="step-row">
                        <textarea name="steps[]" placeholder="Step description" required></textarea>
                        <button type="button" class="btn btn-remove" onclick="removeStep(this)">Remove</button>
                    </div>
                </div>
                <button type="button" class="btn btn-add" onclick="addStep()">Add Step</button>
            </div>
            
            <button type="submit" class="btn btn-submit">Add Recipe</button>
        </form>
    </div>

    <script src="js/script.js"></script>
</body>
</html>