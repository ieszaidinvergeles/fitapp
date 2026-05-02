-- Runs automatically on first MySQL container start
-- Creates the WordPress database alongside the main voltgym database

CREATE DATABASE IF NOT EXISTS voltgym_wordpress
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON voltgym_wordpress.* TO 'voltgym_user'@'%';
FLUSH PRIVILEGES;
