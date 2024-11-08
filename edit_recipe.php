<?php
// edit_recipe.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn = getDBConnection();

// Check if recipe exists and belongs to user
$stmt = $conn->prepare("
    SELECT * FROM recipes 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$recipe_id, $_SESSION['user_id']]);
$recipe = $stmt->fetch();

if (!$recipe) {
    redirect('my_recipes.php');
}

// Get ingredients
$stmt = $conn->prepare("SELECT * FROM ingredients WHERE recipe_id = ?");
$stmt->execute([$recipe_id]);
$ingredients = $stmt->fetchAll();

// Get steps
$stmt = $conn->prepare("SELECT * FROM steps WHERE recipe_id = ? ORDER BY step_number");
$stmt->execute([$recipe_id]);
$steps = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->beginTransaction();
        
        // Handle recipe image upload
        $imagePath = $recipe['image_path'];
        if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === 0) {
            $newImagePath = handleFileUpload($_FILES['recipe_image'], 'uploads/recipes/');
            if ($newImagePath) {
                // Delete old image if it exists
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $imagePath = $newImagePath;
            }
        }
        
        // Update recipe
        $stmt = $conn->prepare("
            UPDATE recipes 
            SET title = ?, description = ?, prep_time = ?, 
                cook_time = ?, servings = ?, image_path = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            sanitizeInput($_POST['title']),
            sanitizeInput($_POST['description']),
            sanitizeInput($_POST['prep_time']),
            sanitizeInput($_POST['cook_time']),
            (int)$_POST['servings'],
            $imagePath,
            $recipe_id,
            $_SESSION['user_id']
        ]);
        
        // Delete existing ingredients and steps
        $conn->exec("DELETE FROM ingredients WHERE recipe_id = " . $recipe_id);
        $conn->exec("DELETE FROM steps WHERE recipe_id = " . $recipe_id);
        
        // Insert updated ingredients
        $ingredients = $_POST['ingredients'];
        $quantities = $_POST['quantities'];
        $units = $_POST['units'];
        
        $stmt = $conn->prepare("
            INSERT INTO ingredients (recipe_id, name, quantity, unit) 
            VALUES (?, ?, ?, ?)
        ");
        foreach ($ingredients as $i => $ingredient) {
            $stmt->execute([
                $recipe_id,
                sanitizeInput($ingredient),
                sanitizeInput($quantities[$i]),
                sanitizeInput($units[$i])
            ]);
        }
        
        // Insert updated steps
        $steps = $_POST['steps'];
        $stmt = $conn->prepare("
            INSERT INTO steps (recipe_id, step_number, description) 
            VALUES (?, ?, ?)
        ");
        foreach ($steps as $i => $step) {
            $stmt->execute([
                $recipe_id,
                $i + 1,
                sanitizeInput($step)
            ]);
        }
        
        $conn->commit();
        redirect('view_recipe.php?id=' . $recipe_id);
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed to update recipe: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - Recipe Website</title>
    <link rel="stylesheet" href="css/recipe-form.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Edit Recipe</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="edit_recipe.php?id=<?php echo $recipe_id; ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Current Image:</label>
                <?php if ($recipe['image_path']): ?>
                    <img src="<?php echo $recipe['image_path']; ?>" alt="Current recipe image" style="max-width: 200px;">
                <?php else: ?>
                    <p>No image uploaded</p>
                <?php endif; ?>
                <label>Upload New Image (optional):</label>
                <input type="file" name="recipe_image" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Preparation Time:</label>
                <input type="text" name="prep_time" value="<?php echo htmlspecialchars($recipe['prep_time']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Cooking Time:</label>
                <input type="text" name="cook_time" value="<?php echo htmlspecialchars($recipe['cook_time']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Servings:</label>
                <input type="number" name="servings" value="<?php echo htmlspecialchars($recipe['servings']); ?>" required>
            </div>
            
            <div class="ingredients-section">
                <h2>Ingredients</h2>
                <div id="ingredients-list">
                    <?php foreach ($ingredients as $ingredient): ?>
                        <div class="ingredient-row">
                            <input type="text" name="ingredients[]" 
                                   value="<?php echo htmlspecialchars($ingredient['name']); ?>" 
                                   placeholder="Ingredient" required>
                            <input type="text" name="quantities[]" 
                                   value="<?php echo htmlspecialchars($ingredient['quantity']); ?>" 
                                   placeholder="Quantity" required>
                            <input type="text" name="units[]" 
                                   value="<?php echo htmlspecialchars($ingredient['unit']); ?>" 
                                   placeholder="Unit" required>
                            <button type="button" class="btn btn-remove" onclick="removeIngredient(this)">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-add" onclick="addIngredient()">Add Ingredient</button>
            </div>
            
            <div class="steps-section">
                <h2>Steps</h2>
                <div id="steps-list">
                    <?php foreach ($steps as $step): ?>
                        <div class="step-row">
                            <textarea name="steps[]" placeholder="Step description" required><?php echo htmlspecialchars($step['description']); ?></textarea>
                            <button type="button" class="btn btn-remove" onclick="removeStep(this)">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-add" onclick="addStep()">Add Step</button>
            </div>
            
            <button type="submit" class="btn btn-submit">Update Recipe</button>
        </form>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>