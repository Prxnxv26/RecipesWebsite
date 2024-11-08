<?php
// my_recipes.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT * FROM recipes 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$recipes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recipes - Recipe Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>My Recipes</h1>
        
        <?php if (empty($recipes)): ?>
            <p>You haven't added any recipes yet. <a href="add_recipe.php">Add your first recipe!</a></p>
        <?php else: ?>
            <div class="recipes-grid">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <div class="recipe-content">
                            <a href="view_recipe.php?id=<?php echo $recipe['id']; ?>">
                                <img src="<?php echo $recipe['image_path'] ?: 'images/default-recipe.jpg'; ?>" 
                                        alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <p class="description">
                                    <?php echo substr(htmlspecialchars($recipe['description']), 0, 100) . '...'; ?>
                                </p>
                            </a>
                        </div>
                        <div class="recipe-actions">
                            <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn btn-edit">Edit</a>
                            <button onclick="confirmDelete(<?php echo $recipe['id']; ?>)" class="btn btn-delete">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>