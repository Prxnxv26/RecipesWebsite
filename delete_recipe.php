<?php
// delete_recipe.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (isset($_GET['id'])) {
    $recipeId = (int)$_GET['id'];
    $conn = getDBConnection();
    
    try {
        $conn->beginTransaction();
        
        // Check if user owns this recipe
        $stmt = $conn->prepare("SELECT user_id, image_path FROM recipes WHERE id = ?");
        $stmt->execute([$recipeId]);
        $recipe = $stmt->fetch();
        
        if ($recipe && $recipe['user_id'] == $_SESSION['user_id']) {
            // Delete recipe image if it exists
            if ($recipe['image_path'] && file_exists($recipe['image_path'])) {
                unlink($recipe['image_path']);
            }
            
            // Get and delete step images if they exist
            $stmt = $conn->prepare("SELECT image_path FROM steps WHERE recipe_id = ? AND image_path IS NOT NULL");
            $stmt->execute([$recipeId]);
            $stepImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($stepImages as $imagePath) {
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete recipe and all related data (ingredients and steps will be deleted automatically due to CASCADE)
            $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
            $stmt->execute([$recipeId]);
            
            $conn->commit();
            
            $_SESSION['flash_message'] = "Recipe deleted successfully!";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "You don't have permission to delete this recipe.";
            $_SESSION['flash_type'] = "error";
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['flash_message'] = "Error deleting recipe: " . $e->getMessage();
        $_SESSION['flash_type'] = "error";
    }
}

redirect('my_recipes.php');
?>