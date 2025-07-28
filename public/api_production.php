<?php
/**
 * API Woyofal pour production sur Render
 * Version sans warnings PHP qui causent les erreurs de headers
 */

// Désactiver l'affichage des erreurs en production
error_reporting(0);
ini_set('display_errors', 0);

// Headers avant tout output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Buffer de sortie pour éviter les problèmes de headers
ob_start();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'status';
    
    switch ($method . ':' . $action) {
        case 'POST:achat-credit':
            // Logique d'achat de crédit
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['compteur'], $input['montant'])) {
                http_response_code(422);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 422,
                    'message' => 'Paramètres compteur et montant obligatoires'
                ]);
                break;
            }
            
            $compteur = trim($input['compteur']);
            $montant = (float)$input['montant'];
            
            // Validations
            if (empty($compteur)) {
                http_response_code(422);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 422,
                    'message' => 'Le numéro de compteur ne peut pas être vide'
                ]);
                break;
            }
            
            if ($montant <= 0) {
                http_response_code(400);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le montant doit être supérieur à 0'
                ]);
                break;
            }
            
            if ($montant < 100) {
                http_response_code(400);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le montant minimum est de 100 FCFA'
                ]);
                break;
            }
            
            // Simulation de vérification de compteur
            $compteurs_valides = ['123456789', '987654321', '555666777'];
            if (!in_array($compteur, $compteurs_valides)) {
                http_response_code(404);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 404,
                    'message' => 'Le numéro de compteur n\'a pas été trouvé'
                ]);
                break;
            }
            
            // Calcul des tranches SENELEC
            $kwh = 0;
            $tranche = '';
            $prix_moyen = 0;
            $details_tranches = [];
            
            if ($montant <= 5000) {
                // Tranche 1 uniquement
                $kwh = $montant / 85;
                $tranche = 'Tranche sociale';
                $prix_moyen = 85;
                $details_tranches = [
                    ['tranche' => 1, 'montant' => $montant, 'kwh' => $kwh, 'prix' => 85]
                ];
            } elseif ($montant <= 15000) {
                // Tranches 1 + 2
                $kwh1 = 5000 / 85;
                $kwh2 = ($montant - 5000) / 95;
                $kwh = $kwh1 + $kwh2;
                $tranche = 'Tranche normale';
                $prix_moyen = $montant / $kwh;
                $details_tranches = [
                    ['tranche' => 1, 'montant' => 5000, 'kwh' => $kwh1, 'prix' => 85],
                    ['tranche' => 2, 'montant' => ($montant - 5000), 'kwh' => $kwh2, 'prix' => 95]
                ];
            } else {
                // Tranches 1 + 2 + 3
                $kwh1 = 5000 / 85;
                $kwh2 = 10000 / 95;
                $kwh3 = ($montant - 15000) / 110;
                $kwh = $kwh1 + $kwh2 + $kwh3;
                $tranche = 'Tranche élevée';
                $prix_moyen = $montant / $kwh;
                $details_tranches = [
                    ['tranche' => 1, 'montant' => 5000, 'kwh' => $kwh1, 'prix' => 85],
                    ['tranche' => 2, 'montant' => 10000, 'kwh' => $kwh2, 'prix' => 95],
                    ['tranche' => 3, 'montant' => ($montant - 15000), 'kwh' => $kwh3, 'prix' => 110]
                ];
            }
            
            // Génération des codes
            $reference = 'WOY' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code_base = str_pad((string)round($kwh * 100), 8, '0', STR_PAD_LEFT);
            $code_recharge = substr($code_base, 0, 4) . '-' . substr($code_base, 4, 4);
            
            // Simulation nom client
            $noms_clients = [
                '123456789' => 'Jean Dupont',
                '987654321' => 'Marie Ndiaye',
                '555666777' => 'Abdou Ba'
            ];
            $nom_client = $noms_clients[$compteur] ?? 'Client Inconnu';
            
            http_response_code(200);
            echo json_encode([
                'data' => [
                    'id_facture' => rand(10000, 99999),
                    'compteur' => $compteur,
                    'reference' => $reference,
                    'code' => $code_recharge,
                    'date' => date('Y-m-d H:i:s'),
                    'tranche' => $tranche,
                    'prix' => round($prix_moyen, 2),
                    'nbreKwt' => round($kwh, 2),
                    'client' => $nom_client,
                    'details_calcul' => $details_tranches
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Achat effectué avec succès'
            ]);
            break;
            
        case 'GET:compteur':
            $numero = $_GET['numero'] ?? '';
            if (empty($numero)) {
                http_response_code(400);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 400,
                    'message' => 'Le paramètre "numero" est requis'
                ]);
                break;
            }
            
            // Simulation compteurs existants
            $compteurs_db = [
                '123456789' => ['client' => 'Jean Dupont', 'adresse' => 'Dakar Plateau', 'consommation' => 3500],
                '987654321' => ['client' => 'Marie Ndiaye', 'adresse' => 'Guédiawaye', 'consommation' => 8200],
                '555666777' => ['client' => 'Abdou Ba', 'adresse' => 'Pikine', 'consommation' => 12500]
            ];
            
            if (!isset($compteurs_db[$numero])) {
                http_response_code(404);
                echo json_encode([
                    'data' => null,
                    'statut' => 'error',
                    'code' => 404,
                    'message' => 'Le numéro de compteur n\'a pas été trouvé'
                ]);
                break;
            }
            
            $compteur_info = $compteurs_db[$numero];
            
            http_response_code(200);
            echo json_encode([
                'data' => [
                    'compteur' => $numero,
                    'client' => $compteur_info['client'],
                    'adresse' => $compteur_info['adresse'],
                    'actif' => true,
                    'consommation_mois' => $compteur_info['consommation'],
                    'tranches_disponibles' => [
                        ['niveau' => 1, 'seuil_max' => 5000, 'prix_kwh' => 85, 'description' => 'Tranche sociale'],
                        ['niveau' => 2, 'seuil_max' => 15000, 'prix_kwh' => 95, 'description' => 'Tranche normale'],
                        ['niveau' => 3, 'seuil_max' => 0, 'prix_kwh' => 110, 'description' => 'Tranche élevée']
                    ]
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Informations récupérées avec succès'
            ]);
            break;
            
        case 'GET:tranches':
            http_response_code(200);
            echo json_encode([
                'data' => [
                    ['niveau' => 1, 'seuil_min' => 0, 'seuil_max' => 5000, 'prix_kwh' => 85, 'description' => 'Tranche sociale - Pour les ménages à faible consommation'],
                    ['niveau' => 2, 'seuil_min' => 5001, 'seuil_max' => 15000, 'prix_kwh' => 95, 'description' => 'Tranche normale - Pour la consommation standard'],
                    ['niveau' => 3, 'seuil_min' => 15001, 'seuil_max' => 0, 'prix_kwh' => 110, 'description' => 'Tranche élevée - Pour les gros consommateurs']
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Tranches tarifaires SENELEC récupérées avec succès'
            ]);
            break;
            
        case 'GET:status':
        case 'GET:health':
        default:
            http_response_code(200);
            echo json_encode([
                'data' => [
                    'service' => 'Woyofal API',
                    'version' => '2.0-production',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 'operational',
                    'environment' => 'production',
                    'server' => 'Render',
                    'endpoints' => [
                        'POST ?action=achat-credit' => 'Acheter du crédit électrique',
                        'GET ?action=compteur&numero=XXX' => 'Informations compteur',
                        'GET ?action=tranches' => 'Tranches tarifaires',
                        'GET ?action=status' => 'Statut du service'
                    ],
                    'contact' => 'API Woyofal - Système de prépaiement électrique'
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Service Woyofal opérationnel sur Render'
            ]);
            break;
    }
    
} catch (Exception $e) {
    // Log l'erreur en production (vous pouvez ajouter un système de log)
    error_log("Woyofal API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'data' => null,
        'statut' => 'error',
        'code' => 500,
        'message' => 'Erreur interne du serveur'
    ]);
} catch (Error $e) {
    // Gestion des erreurs fatales
    error_log("Woyofal API Fatal Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'data' => null,
        'statut' => 'error',
        'code' => 500,
        'message' => 'Erreur critique du serveur'
    ]);
}

// Vider le buffer et envoyer la réponse
ob_end_flush();
?>
