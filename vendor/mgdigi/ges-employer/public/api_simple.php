<?php
/**
 * API Woyofal simple compatible Apache
 * Utilise des paramètres GET pour le routage
 */

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\AchatCreditController;

// Configuration des headers CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion des requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Récupérer l'action demandée via paramètre GET
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'status';
    
    // Router les requêtes
    $controller = new AchatCreditController();
    
    switch ($method . ':' . $action) {
        case 'POST:achat-credit':
            // Endpoint principal pour acheter du crédit
            $controller->acheterCredit();
            break;
            
        case 'GET:compteur':
            // Récupérer les infos d'un compteur
            $numeroCompteur = $_GET['numero'] ?? '';
            if (empty($numeroCompteur)) {
                throw new InvalidArgumentException('Le paramètre "numero" est requis');
            }
            $controller->obtenirInfosCompteur($numeroCompteur);
            break;
            
        case 'GET:tranches':
            // Obtenir les tranches de prix
            $trancheService = new \App\Service\TrancheService();
            echo json_encode([
                'data' => array_map(fn($t) => $t->toArray(), $trancheService->getTranches()),
                'statut' => 'success',
                'code' => 200,
                'message' => 'Tranches récupérées avec succès'
            ]);
            break;
            
        case 'GET:status':
        default:
            // Endpoint de santé de l'API
            echo json_encode([
                'data' => [
                    'service' => 'Woyofal API',
                    'version' => '2.0',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'operational',
                    'endpoints' => [
                        'POST ?action=achat-credit' => 'Acheter du crédit',
                        'GET ?action=compteur&numero=XXX' => 'Infos compteur',
                        'GET ?action=tranches' => 'Liste des tranches',
                        'GET ?action=status' => 'Statut API'
                    ]
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Service opérationnel'
            ]);
            break;
    }
    
} catch (Exception $e) {
    // Gestion globale des erreurs
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
    
    // Log l'erreur
    error_log("Woyofal API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}
