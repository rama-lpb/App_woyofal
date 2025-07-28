<?php
// api.php dans woyofal

header('Content-Type: application/json');

// Vérifie que c’est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Récupérer le contenu JSON envoyé
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Vérifier les paramètres nécessaires
if (!isset($input['compteur'], $input['solde'], $input['montant'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Traiter les données (exemple simple)
$compteur = $input['compteur'];
$solde = floatval($input['solde']);
$montant = floatval($input['montant']);

// Exemple logique simple: vérifier si montant <= solde
if ($montant > $solde) {
    http_response_code(400);
    echo json_encode(['statut' => 'error', 'message' => 'Solde insuffisant']);
    exit;
}

// Faire le traitement, ici on simule un achat de crédit
$resultat = [
    'facture_id' => rand(1000, 9999),
    'compteur' => $compteur,
    'montant' => $montant,
    'message' => 'Achat de crédit réussi'
];

// Répondre en JSON
echo json_encode([
    'statut' => 'success',
    'data' => $resultat
]);
