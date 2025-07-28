<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input || !isset($input['montant'])) {
        http_response_code(400);
        echo json_encode(["statut" => "error", "message" => "Paramètres invalides"]);
        exit;
    }

    // Traitement
    echo json_encode([
        "statut" => "success",
        "data" => [
            "facture_id" => 42,
            "montant" => $input['montant']
        ]
    ]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Route not found"]);
}
