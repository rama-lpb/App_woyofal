<?php
namespace App\Controller;




class paiementController {

public function getinfos (){
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);
var_dump($input);

$compteur = $input['compteur'] ?? null;
$solde = isset($input['solde']) ? (float)$input['solde'] : 0;
$compte = $input['compte'] ?? null; // ici tu récupères ton tableau
$montant = isset($input['montant']) ? (float)$input['montant'] : 0;

// Valider les données minimum
if (!$compteur || $montant <= 0 || $solde < $montant) {
    http_response_code(400);
    echo json_encode([
        'data' => null,
        'statut' => 'error',
        'code' => 400,
        'message' => 'Données invalides ou solde insuffisant.'
    ]);
    exit;
}

// Tu peux maintenant exploiter $compte (tableau) comme tu veux, 
// par ex. vérifier si client existe dans ce tableau, etc.

// Exemple simple : juste afficher les données reçues dans la réponse pour test
http_response_code(200);
echo json_encode([
    'data' => [
        'compteur' => $compteur,
        'solde' => $solde,
        'compte' => $compte,
        'montant' => $montant
    ],
    'statut' => 'success',
    'code' => 200,
    'message' => 'Données reçues avec succès'
]);
exit;
}
}