<?php
/**
 * API Woyofal standalone - sans dépendances vendor
 * Pour contourner le problème de vendor corrompu
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'status';
    
    switch ($method . ':' . $action) {
        case 'POST:achat-credit':
            // Logique d'achat de crédit simplifiée
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['compteur'], $input['montant'])) {
                throw new InvalidArgumentException('Paramètres compteur et montant obligatoires');
            }
            
            $compteur = $input['compteur'];
            $montant = (float)$input['montant'];
            
            if ($montant <= 0) {
                throw new InvalidArgumentException('Le montant doit être supérieur à 0');
            }
            
            // Simulation du calcul de tranches
            $kwh = 0;
            $tranche = '';
            $prix = 0;
            
            if ($montant <= 5000) {
                $kwh = $montant / 85; // 85 FCFA/kWh
                $tranche = 'Tranche sociale';
                $prix = 85;
            } elseif ($montant <= 15000) {
                $kwh1 = 5000 / 85; // Première tranche
                $kwh2 = ($montant - 5000) / 95; // Deuxième tranche
                $kwh = $kwh1 + $kwh2;
                $tranche = 'Tranche normale';
                $prix = $montant / $kwh; // Prix moyen
            } else {
                $kwh1 = 5000 / 85; // Première tranche
                $kwh2 = 10000 / 95; // Deuxième tranche
                $kwh3 = ($montant - 15000) / 110; // Troisième tranche
                $kwh = $kwh1 + $kwh2 + $kwh3;
                $tranche = 'Tranche élevée';
                $prix = $montant / $kwh; // Prix moyen
            }
            
            $reference = 'WOY' . date('Ymd') . rand(1000, 9999);
            $code = str_pad((string)round($kwh * 100), 8, '0', STR_PAD_LEFT);
            $codeRecharge = substr($code, 0, 4) . '-' . substr($code, 4, 4);
            
            echo json_encode([
                'data' => [
                    'id_facture' => rand(1000, 9999),
                    'compteur' => $compteur,
                    'reference' => $reference,
                    'code' => $codeRecharge,
                    'date' => date('Y-m-d H:i:s'),
                    'tranche' => $tranche,
                    'prix' => round($prix, 2),
                    'nbreKwt' => round($kwh, 2),
                    'client' => 'Client Test'
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Achat effectué avec succès'
            ]);
            break;
            
        case 'GET:compteur':
            $numero = $_GET['numero'] ?? '';
            if (empty($numero)) {
                throw new InvalidArgumentException('Le paramètre "numero" est requis');
            }
            
            // Simulation d'un compteur valide
            if ($numero === '123456789') {
                echo json_encode([
                    'data' => [
                        'compteur' => $numero,
                        'client' => 'Jean Dupont',
                        'actif' => true,
                        'consommation_mois' => 3500,
                        'tranches_disponibles' => [
                            ['niveau' => 1, 'seuil_max' => 5000, 'prix_kwh' => 85],
                            ['niveau' => 2, 'seuil_max' => 15000, 'prix_kwh' => 95],
                            ['niveau' => 3, 'seuil_max' => 0, 'prix_kwh' => 110]
                        ]
                    ],
                    'statut' => 'success',
                    'code' => 200,
                    'message' => 'Informations récupérées avec succès'
                ]);
            } else {
                throw new RuntimeException('Le numéro de compteur n\'a pas été trouvé');
            }
            break;
            
        case 'GET:tranches':
            echo json_encode([
                'data' => [
                    ['niveau' => 1, 'seuil_min' => 0, 'seuil_max' => 5000, 'prix_kwh' => 85, 'description' => 'Tranche sociale'],
                    ['niveau' => 2, 'seuil_min' => 5001, 'seuil_max' => 15000, 'prix_kwh' => 95, 'description' => 'Tranche normale'],
                    ['niveau' => 3, 'seuil_min' => 15001, 'seuil_max' => 0, 'prix_kwh' => 110, 'description' => 'Tranche élevée']
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Tranches récupérées avec succès'
            ]);
            break;
            
        case 'GET:status':
        default:
            echo json_encode([
                'data' => [
                    'service' => 'Woyofal API Standalone',
                    'version' => '2.0',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'operational',
                    'endpoints' => [
                        'POST ?action=achat-credit' => 'Acheter du crédit',
                        'GET ?action=compteur&numero=123456789' => 'Infos compteur',
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
    $code = 500;
    if ($e instanceof InvalidArgumentException) {
        $code = 400;
    } elseif ($e instanceof RuntimeException && str_contains($e->getMessage(), 'non trouvé')) {
        $code = 404;
    }
    
    http_response_code($code);
    echo json_encode([
        'data' => null,
        'statut' => 'error',
        'code' => $code,
        'message' => $e->getMessage()
    ]);
}
