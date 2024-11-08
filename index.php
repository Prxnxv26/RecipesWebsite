<?php
// index.php
require_once 'config.php';

$conn = getDBConnection();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get total recipes count
$stmt = $conn->query("SELECT COUNT(*) FROM recipes");
$total_recipes = $stmt->fetchColumn();
$total_pages = ceil($total_recipes / $per_page);

// Get recipes for current page with proper LIMIT syntax
$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM recipes r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC 
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$recipes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Website - Home</title>
    <link rel="stylesheet" href="/recipe-web/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="recipes-grid">
            <?php if (empty($recipes)): ?>
                <p>No recipes found. <?php if (!isLoggedIn()): ?>
                    <a href="login.php">Login</a> or <a href="register.php">Register</a> to add recipes!
                <?php else: ?>
                    <a href="add_recipe.php">Add your first recipe!</a>
                <?php endif; ?></p>
            <?php else: ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <a href="view_recipe.php?id=<?php echo $recipe['id']; ?>">
                            <img src="<?php echo $recipe['image_path'] ?: 'images/default-recipe.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="author">By <?php echo htmlspecialchars($recipe['username']); ?></p>
                            <p class="description"><?php echo substr(htmlspecialchars($recipe['description']), 0, 100) . '...'; ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="<?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>