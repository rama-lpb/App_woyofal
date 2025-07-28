<?php
namespace App\Controller;

use App\Repository\CompteurRepository;
use App\Repository\PaiementRepository;
use App\Service\TrancheService;
use App\Service\LoggingService;
use App\Entity\Compteur;
use App\Entity\Paiement;
use App\Enum\StatutPaiement;

class AchatCreditController {
    
    private CompteurRepository $compteurRepository;
    private PaiementRepository $paiementRepository;
    private TrancheService $trancheService;
    private LoggingService $loggingService;

    public function __construct() {
        $this->compteurRepository = new CompteurRepository();
        $this->paiementRepository = new PaiementRepository();
        $this->trancheService = new TrancheService();
        $this->loggingService = new LoggingService();
    }

    /**
     * Point d'entrée principal pour l'achat de crédit
     * POST /api/achat-credit
     */
    public function acheterCredit(): void {

        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->retournerErreur(405, 'Méthode non autorisée');
                return;
            }

            $input = $this->obtenirDonneesEntree();
            
            $this->validerParametres($input);
            
            $numeroCompteur = $input['compteur'];
            $montant = (float)$input['montant'];

            $compteur = $this->verifierCompteur($numeroCompteur);
            
            $consommationMois = $this->compteurRepository->getConsommationMoisEnCours($compteur['id']);
            
            $detailsAchat = $this->trancheService->calculerAchat($montant, $consommationMois);
            
            $paiementId = $this->enregistrerPaiement($compteur, $montant, $detailsAchat);
            
            $this->loggingService->logSucces(
                $numeroCompteur,
                $detailsAchat['code_recharge'],
                $detailsAchat['total_kwh']
            );
            
            $this->retournerSucces($compteur, $detailsAchat, $paiementId);
            
        } catch (\Exception $e) {
            $this->gererErreur($e, $numeroCompteur ?? 'inconnu');
        }
    }

    /**
     * Récupère et décode les données JSON de la requête
     */
    private function obtenirDonneesEntree(): array {
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);
        
        if (!$input || json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('JSON invalide');
        }
        
        return $input;
    }

    /**
     * Valide les paramètres obligatoires
     */
    private function validerParametres(array $input): void {
        if (!isset($input['compteur']) || empty(trim($input['compteur']))) {
            throw new \InvalidArgumentException('Le numéro de compteur est obligatoire');
        }
        
        if (!isset($input['montant']) || !is_numeric($input['montant'])) {
            throw new \InvalidArgumentException('Le montant est obligatoire et doit être numérique');
        }
        
        $montant = (float)$input['montant'];
        if ($montant <= 0) {
            throw new \InvalidArgumentException('Le montant doit être supérieur à 0');
        }
        
        if ($montant < 100) {
            throw new \InvalidArgumentException('Le montant minimum est de 100 FCFA');
        }
    }

    /**
     * Vérifie l'existence et la validité du compteur
     */
    private function verifierCompteur(string $numeroCompteur): array {
        $compteur = $this->compteurRepository->findByNumero($numeroCompteur);
        
        if (!$compteur) {
            throw new \RuntimeException('Le numéro de compteur n\'a pas été trouvé');
        }
        
        if (!$compteur['actif']) {
            throw new \RuntimeException('Ce compteur est désactivé');
        }
        
        return $compteur;
    }

    /**
     * Enregistre le paiement en base de données
     */
    private function enregistrerPaiement(array $compteur, float $montant, array $detailsAchat): int {
        $tranchePrincipale = $detailsAchat['tranche_principale'];
        
        return $this->paiementRepository->insertPaiement(
            $compteur['id'],
            $montant,
            $detailsAchat['reference'],
            $detailsAchat['code_recharge'],
            (int)$detailsAchat['total_kwh'],
            $tranchePrincipale['description'],
            $detailsAchat['prix_moyen_kwh'],
            $compteur['id_client'], 
            'success'
        );
    }

    /**
     * Retourne une réponse de succès formatée
     */
    private function retournerSucces(array $compteur, array $detailsAchat, int $paiementId): void {
        $tranchePrincipale = $detailsAchat['tranche_principale'];
        
        $reponse = [
            'data' => [
                'id_facture' => $paiementId,
                'compteur' => $compteur['numero'],
                'reference' => $detailsAchat['reference'],
                'code' => $detailsAchat['code_recharge'],
                'date' => date('Y-m-d H:i:s'),
                'tranche' => $tranchePrincipale['description'],
                'prix' => $detailsAchat['prix_moyen_kwh'],
                'nbreKwt' => $detailsAchat['total_kwh'],
                'client' => trim($compteur['prenom_client'] . ' ' . $compteur['nom_client'])
            ],
            'statut' => 'success',
            'code' => 200,
            'message' => 'Achat effectué avec succès'
        ];
        
        http_response_code(200);
        echo json_encode($reponse);
    }

    /**
     * Retourne une réponse d'erreur formatée
     */
    private function retournerErreur(int $codeHttp, string $message): void {
        $reponse = [
            'data' => null,
            'statut' => 'error',
            'code' => $codeHttp,
            'message' => $message
        ];
        
        http_response_code($codeHttp);
        echo json_encode($reponse);
    }

    /**
     * Gère les erreurs et les log
     */
    private function gererErreur(\Exception $e, string $numeroCompteur): void {
        $message = $e->getMessage();
        $codeErreur = 400;
        
        // Déterminer le code d'erreur approprié
        if (str_contains($message, 'non trouvé') || str_contains($message, 'not found')) {
            $codeErreur = 404;
        } elseif (str_contains($message, 'JSON invalide') || str_contains($message, 'obligatoire')) {
            $codeErreur = 422;
        } elseif (str_contains($message, 'Méthode non autorisée')) {
            $codeErreur = 405;
        }
        
        $this->loggingService->logErreur($numeroCompteur, $message, $codeErreur);
        
        $this->retournerErreur($codeErreur, $message);
    }

    /**
     * Endpoint pour obtenir les informations d'un compteur
     * GET /api/compteur/{numero}
     */
    public function obtenirInfosCompteur(string $numeroCompteur): void {
        header('Content-Type: application/json');
        
        try {
            $compteur = $this->verifierCompteur($numeroCompteur);
            $consommationMois = $this->compteurRepository->getConsommationMoisEnCours($compteur['id']);
            
            $reponse = [
                'data' => [
                    'compteur' => $compteur['numero'],
                    'client' => trim($compteur['prenom_client'] . ' ' . $compteur['nom_client']),
                    'actif' => $compteur['actif'],
                    'consommation_mois' => $consommationMois,
                    'tranches_disponibles' => $this->trancheService->getTranches()
                ],
                'statut' => 'success',
                'code' => 200,
                'message' => 'Informations récupérées avec succès'
            ];
            
            http_response_code(200);
            echo json_encode($reponse);
            
        } catch (\Exception $e) {
            $this->gererErreur($e, $numeroCompteur);
        }
    }
}
