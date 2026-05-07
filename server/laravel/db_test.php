<?php
try {
    $pdo = new PDO('mysql:host=mysql;dbname=voltgym', 'voltgym_user', 'VoltGymDB2026!');
    echo 'CONECTADO';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
