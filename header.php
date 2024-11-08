<?php
// Ensure we have access to session data and helper functions
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Website</title>
    <link rel="stylesheet" href="/recipe-web/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <a href="index.php" class="logo">Recipe Hub</a>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="add_recipe.php">Add Recipe</a>
                    <a href="my_recipes.php">My Recipes</a>
                    <div class="user-menu">
                        <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Flash Messages for success/error notifications -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
            <?php 
                echo $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
            ?>
        </div>
    <?php endif; ?>
</body>
</html>