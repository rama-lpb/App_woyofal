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
     * Vérifie si un compteur existe par numéro, et retourne ses infos + client si possible.
     * @param string $numero
     * @return array|null
     */
    public function findByNumero(string $numero): ?array {
        try {
            $sql = "
                SELECT *
                FROM compteur c
               
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
     public function selectAll(){
         
        }
}
