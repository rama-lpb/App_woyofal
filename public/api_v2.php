<?php
/**
 * Point d'entrée API unifié pour Woyofal
 * Gère le routage et l'authentification
 */

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\AchatCreditController;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $requestUri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    
    $route = strtok($requestUri, '?');
    
    $route = str_replace('/public/api_v2.php', '', $route);
    $route = ltrim($route, '/');
    
    $controller = new AchatCreditController();
    
    switch ($method . ':' . $route) {
        case 'POST:achat-credit':
        case 'POST:':
            $controller->acheterCredit();
            break;
            
        case 'GET:compteur':
            $numeroCompteur = $_GET['numero'] ?? '';
            if (empty($numeroCompteur)) {
                throw new InvalidArgumentException('Le paramètre "numero" est requis');
            }
            $controller->obtenirInfosCompteur($numeroCompteur);
            break;
            
        case 'GET:tranches':
            $trancheService = new \App\Service\TrancheService();
            echo json_encode([
                'data' => array_map(fn($t) => $t->toArray(), $trancheService->getTranches()),
                'statut' => 'success',
                'code' => 200,
                'message' => 'Tranches récupérées avec succès'
            ]);
            break;
            
        case 'GET:status':
            echo json_encode([
                'data' => [
                    'service' => 'Woyofal API',
                    'version' => '2.0',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'operational'
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Service opérationnel'
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'data' => null,
                'statut' => 'error',
                'code' => 404,
                'message' => 'Endpoint non trouvé. Routes disponibles: POST /achat-credit, GET /compteur?numero=XXX, GET /tranches, GET /status'
            ]);
            break;
    }
    
} catch (Exception $e) {
    $code = 500;
    if ($e instanceof InvalidArgumentException) {
        $code = 400;
    }
    
    http_response_code($code);
    echo json_encode([
        'data' => null,
        'statut' => 'error',
        'code' => $code,
        'message' => $e->getMessage()
    ]);
    
    error_log("Woyofal API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}
