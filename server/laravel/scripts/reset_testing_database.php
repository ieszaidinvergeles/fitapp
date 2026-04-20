<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'gymapp_testing';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';

$dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$quotedDatabase = sprintf('`%s`', str_replace('`', '``', $database));

$pdo->exec("DROP DATABASE IF EXISTS {$quotedDatabase}");
$pdo->exec("CREATE DATABASE {$quotedDatabase} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "reset-ok";
