<?php
// view_recipe.php - Single recipe view
require_once 'config.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM recipes r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
$stmt->execute([$_GET['id']]);
$recipe = $stmt->fetch();

if (!$recipe) {
    redirect('index.php');
}

// Get ingredients
$stmt = $conn->prepare("SELECT * FROM ingredients WHERE recipe_id = ?");
$stmt->execute([$_GET['id']]);
$ingredients = $stmt->fetchAll();

// Get steps
$stmt = $conn->prepare("SELECT * FROM steps WHERE recipe_id = ? ORDER BY step_number");
$stmt->execute([$_GET['id']]);
$steps = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Recipe Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="recipe-detail">
            <div class="recipe-header">
                <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                <p class="author">By <?php echo htmlspecialchars($recipe['username']); ?></p>
                
                <?php if (isLoggedIn() && $_SESSION['user_id'] == $recipe['user_id']): ?>
                    <div class="recipe-actions">
                        <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="button btn btn-edit">Edit</a>
                        <button onclick="confirmDelete(<?php echo $recipe['id']; ?>)" class="button delete btn btn-delete">Delete</button>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="recipe-image">
                <img src="<?php echo $recipe['image_path'] ?: 'images/default-recipe.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($recipe['title']); ?>">
            </div>
            
            <div class="recipe-info">
                <div class="info-item">
                    <span class="label">Prep Time:</span>
                    <span><?php echo htmlspecialchars($recipe['prep_time']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Cook Time:</span>
                    <span><?php echo htmlspecialchars($recipe['cook_time']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Servings:</span>
                    <span><?php echo htmlspecialchars($recipe['servings']); ?></span>
                </div>
            </div>
            
            <div class="recipe-description">
                <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
            </div>
            
            <div class="recipe-ingredients">
                <h2>Ingredients</h2>
                <ul>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <li>
                            <?php 
                            echo htmlspecialchars($ingredient['quantity']) . ' ' . 
                                 htmlspecialchars($ingredient['unit']) . ' ' . 
                                 htmlspecialchars($ingredient['name']); 
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="recipe-steps">
                <h2>Instructions</h2>
                <ol>
                    <?php foreach ($steps as $step): ?>
                        <li>
                            <p><?php echo nl2br(htmlspecialchars($step['description'])); ?></p>
                            <?php if ($step['image_path']): ?>
                                <img src="<?php echo $step['image_path']; ?>" alt="Step <?php echo $step['step_number']; ?>">
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>