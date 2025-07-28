<?php
/**
 * Test direct de l'API sans dépendances
 * Pour vérifier si PHP fonctionne
 */

header('Content-Type: application/json');

// Test simple sans autoloader
echo json_encode([
    'service' => 'Woyofal API Test',
    'status' => 'PHP fonctionne !',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'query_string' => $_SERVER['QUERY_STRING'] ?? 'N/A'
    ],
    'message' => 'Si vous voyez ceci, PHP fonctionne correctement'
]);
?>
