<?php
require_once 'functions.php';
$res = api_get('/recipes', auth: true);
header('Content-Type: application/json');
echo json_encode($res, JSON_PRETTY_PRINT);
