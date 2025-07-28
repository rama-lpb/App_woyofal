<?php


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    $pdo = new PDO($_ENV['dns'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Insert Compteurs
    $compteurs = ['COMP000', 'COMP001', 'COMP002', 'COMP003', 'COMP004', 'COMP005', 'COMP006', 'COMP007', 'COMP008', 'COMP009'];
    $compteurIds = [];

    $stmtInsertCompteur = $pdo->prepare("INSERT INTO compteur (numero) VALUES (:numero)");
    $stmtFetchCompteurId = $pdo->prepare("SELECT id FROM compteur WHERE numero = :numero");

    foreach ($compteurs as $numero) {
        try {
            $stmtInsertCompteur->execute([':numero' => $numero]);
        } catch (PDOException $e) {
            // Si doublon, on ignore l'erreur
        }

        $stmtFetchCompteurId->execute([':numero' => $numero]);
        $id = $stmtFetchCompteurId->fetchColumn();
        if ($id !== false) {
            $compteurIds[$numero] = $id;
        }
    }

    echo "✅ Données insérées dans la table `compteur`.\n";

    // 2. Paiements
    $paiements = [
        ['COMP000', 4462.43, 'REF0000', 'RECH0000', 31, 'Tranche A', 128.59, 'CNI00345Z', 'success'],
        ['COMP001', 4671.64, 'REF0001', 'RECH0001', 51, 'Tranche B', 75.57, 'CNI00456W', 'echec'],
        ['COMP002', 6407.15, 'REF0002', 'RECH0002', 93, 'Tranche A', 113.49, 'CNI00234Y', 'success'],
        ['COMP003', 5346.87, 'REF0003', 'RECH0003', 95, 'Tranche B', 134.60, 'CNI00345Z', 'echec'],
        ['COMP004', 4322.48, 'REF0004', 'RECH0004', 62, 'Tranche B', 91.96, 'CNI00234Y', 'success'],
        ['COMP005', 8134.13, 'REF0005', 'RECH0005', 47, 'Tranche B', 110.51, 'CNI00345Z', 'echec'],
        ['COMP006', 8827.97, 'REF0006', 'RECH0006', 64, 'Tranche C', 89.85, 'CNI00234Y', 'success'],
        ['COMP007', 1006.00, 'REF0007', 'RECH0007', 22, 'Tranche A', 118.87, 'CNI00567V', 'success'],
        ['COMP008', 3617.51, 'REF0008', 'RECH0008', 17, 'Tranche A', 78.89, 'CNI00567V', 'echec'],
        ['COMP009', 2925.54, 'REF0009', 'RECH0009', 85, 'Tranche A', 119.05, 'CNI00567V', 'echec'],
    ];

    $stmtInsertPaiement = $pdo->prepare("
        INSERT INTO paiement (
            id_compteur, montant, reference, code_recharge, nbre_kwt,
            tranche, prix_kwh, id_client, statut
        ) VALUES (
            :id_compteur, :montant, :reference, :code_recharge, :nbre_kwt,
            :tranche, :prix_kwh, :id_client, :statut
        )
    ");

    foreach ($paiements as $p) {
        $id_compteur = $compteurIds[$p[0]] ?? null;

        if ($id_compteur) {
            $stmtInsertPaiement->execute([
                ':id_compteur' => $id_compteur,
                ':montant' => $p[1],
                ':reference' => $p[2],
                ':code_recharge' => $p[3],
                ':nbre_kwt' => $p[4],
                ':tranche' => $p[5],
                ':prix_kwh' => $p[6],
                ':id_client' => $p[7],
                ':statut' => $p[8],
            ]);
        }
    }

    echo "✅ Données insérées dans la table `paiement`.\n";

} catch (PDOException $e) {
    echo "❌ Erreur d'insertion : " . $e->getMessage() . "\n";
}
