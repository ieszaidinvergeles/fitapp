<?php
define('API_BASE', 'http://nginx:8000/api/v1');

function fitapp_request(string $method, string $endpoint, ?array $body = null, bool $auth = false): array
{
    $url     = API_BASE . $endpoint;
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    // Mock token for user 6 (jrvilchez)
    // Actually, I'll need a token. I'll get it from the DB.
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT,        10);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($raw, true) ?? ['raw' => $raw, 'code' => $httpCode];
}

$res = fitapp_request('GET', '/dashboard');
echo json_encode($res, JSON_PRETTY_PRINT);
