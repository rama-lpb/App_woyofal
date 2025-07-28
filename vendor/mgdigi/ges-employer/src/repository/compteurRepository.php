<?php
namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use \PDOException;

class CompteurRepository extends AbstractRepository {

    private string $table = 'compteur';

    public function __construct(){
        parent::__construct();
    }

    /**
     * Vérifie si un compteur existe par numéro, et retourne ses infos + client
     * @param string $numero
     * @return array|null
     */
    public function findByNumero(string $numero): ?array {
        try {
            $sql = "
                SELECT 
                    c.id,
                    c.numero,
                    c.id_client,
                    c.actif,
                    c.date_installation,
                    c.adresse,
                    c.nom_client,
                    c.prenom_client
                FROM compteur c
                WHERE c.numero = :numero 
                AND c.actif = true
                LIMIT 1
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':numero' => $numero]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * Récupère la consommation du mois en cours pour un compteur
     * @param int $idCompteur
     * @return float
     */
    public function getConsommationMoisEnCours(int $idCompteur): float {
        try {
            $sql = "
                SELECT COALESCE(SUM(p.montant), 0) as total_mois
                FROM paiement p
                WHERE p.id_compteur = :id_compteur
                AND p.statut = 'success'
                AND EXTRACT(YEAR FROM p.date_achat::date) = EXTRACT(YEAR FROM CURRENT_DATE)
                AND EXTRACT(MONTH FROM p.date_achat::date) = EXTRACT(MONTH FROM CURRENT_DATE)
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_compteur' => $idCompteur]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (float)($result['total_mois'] ?? 0);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
     public function selectAll(){
         
        }
}
