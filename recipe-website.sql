-- Create database
CREATE DATABASE recipe_website;
USE recipe_website;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recipes table
CREATE TABLE recipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    prep_time VARCHAR(50),
    cook_time VARCHAR(50),
    servings INT,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Ingredients table
CREATE TABLE ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipe_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    quantity VARCHAR(50),
    unit VARCHAR(30),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Steps table
CREATE TABLE steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipe_id INT NOT NULL,
    step_number INT NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);
