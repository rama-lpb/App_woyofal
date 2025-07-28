<?php
namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use \PDOException;

class PaiementRepository extends AbstractRepository {

    private string $table = 'paiement';

    public function __construct(){
        parent::__construct();
    }

    /**
     * Insère un nouveau paiement et retourne son id
     * @param int $id_compteur
     * @param float $montant
     * @param string|null $reference
     * @param string|null $code_recharge
     * @param int|null $nbre_kwt
     * @param string|null $tranche
     * @param float|null $prix_kwh
     * @param string|null $id_client
     * @param string $statut
     * @return int|null lastInsertId
     */
    public function insertPaiement(
        int $id_compteur,
        float $montant,
        ?string $reference,
        ?string $code_recharge,
        ?int $nbre_kwt,
        ?string $tranche,
        ?float $prix_kwh,
        ?string $id_client,
        string $statut = 'success'
    ): ?int {
        try {
            $sql = "INSERT INTO paiement (
                id_compteur, montant, reference, code_recharge, nbre_kwt,
                tranche, prix_kwh, id_client, statut
            ) VALUES (
                :id_compteur, :montant, :reference, :code_recharge, :nbre_kwt,
                :tranche, :prix_kwh, :id_client, :statut
            )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_compteur' => $id_compteur,
                ':montant' => $montant,
                ':reference' => $reference,
                ':code_recharge' => $code_recharge,
                ':nbre_kwt' => $nbre_kwt,
                ':tranche' => $tranche,
                ':prix_kwh' => $prix_kwh,
                ':id_client' => $id_client,
                ':statut' => $statut
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupérer un paiement par son ID (utile pour générer la facture)
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array {
        try {
            $sql = "SELECT * FROM paiement WHERE id = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
        public function selectAll(){
         
        }

}
