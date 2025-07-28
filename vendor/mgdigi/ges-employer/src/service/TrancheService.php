<?php
namespace App\Service;

use App\Entity\Tranche;

class TrancheService {
    
    /**
     * Configuration des tranches de prix
     * Système progressif : plus on consomme, plus c'est cher
     */
    private static array $tranches = [
        ['niveau' => 1, 'min' => 0, 'max' => 5000, 'prix' => 85, 'desc' => 'Tranche sociale'],

        ['niveau' => 2, 'min' => 5001, 'max' => 15000, 'prix' => 95, 'desc' => 'Tranche normale'],

        ['niveau' => 3, 'min' => 15001, 'max' => 0, 'prix' => 110, 'desc' => 'Tranche élevée']
    ];

    /**
     * Calcule l'achat de crédit selon le système de tranches
     * 
     * @param float $montant Montant à dépenser
     * @param float $consommationMoisEnCours Montant déjà consommé ce mois
     * @return array Détails de l'achat (kWh, tranche principale, prix moyen)
     */
    public function calculerAchat(float $montant, float $consommationMoisEnCours = 0): array {
        $tranches = $this->getTranches();
        $detailsAchat = [];
        $totalKwh = 0;
        $montantRestant = $montant;
        $montantCumule = $consommationMoisEnCours;
        
        foreach ($tranches as $tranche) {
            if ($montantRestant <= 0) break;
            
            // Calculer la portion du montant qui s'applique à cette tranche
            $montantTranche = $this->calculerMontantPourTranche($tranche, $montantRestant, $montantCumule);
            
            if ($montantTranche > 0) {
                $kwhTranche = $montantTranche / $tranche->getPrixKwh();
                
                $detailsAchat[] = [
                    'tranche' => $tranche->getNiveau(),
                    'description' => $tranche->getDescription(),
                    'montant' => $montantTranche,
                    'prix_kwh' => $tranche->getPrixKwh(),
                    'kwh' => $kwhTranche
                ];
                
                $totalKwh += $kwhTranche;
                $montantRestant -= $montantTranche;
                $montantCumule += $montantTranche;
            }
        }
        
        $tranchePrincipale = $this->determinerTranchePrincipale($detailsAchat);
        $prixMoyen = $montant / $totalKwh;
        
        return [
            'total_kwh' => round($totalKwh, 2),
            'tranche_principale' => $tranchePrincipale,
            'prix_moyen_kwh' => round($prixMoyen, 2),
            'details_tranches' => $detailsAchat,
            'reference' => $this->genererReference(),
            'code_recharge' => $this->genererCodeRecharge($totalKwh)
        ];
    }
    
    /**
     * Calcule le montant applicable à une tranche spécifique
     */
    private function calculerMontantPourTranche(Tranche $tranche, float $montantRestant, float $montantCumule): float {
        $seuilMin = $tranche->getSeuilMin();
        $seuilMax = $tranche->getSeuilMax();
        
        if ($montantCumule < $seuilMin) {
            $montantDispo = $montantRestant;
            $montantMax = $seuilMax === 0 ? $montantDispo : min($montantDispo, $seuilMax - $seuilMin);
            return min($montantDispo, $montantMax);
        }
        
        if ($seuilMax === 0 || $montantCumule < $seuilMax) {
            $montantMax = $seuilMax === 0 ? $montantRestant : min($montantRestant, $seuilMax - $montantCumule);
            return $montantMax;
        }
        
        return 0;
    }
    
    /**
     * Détermine la tranche principale de l'achat
     */
    private function determinerTranchePrincipale(array $detailsAchat): array {
        if (empty($detailsAchat)) {
            return ['niveau' => 1, 'description' => 'Tranche sociale'];
        }
        
        $tranchePrincipale = array_reduce($detailsAchat, function($max, $tranche) {
            return ($tranche['kwh'] > ($max['kwh'] ?? 0)) ? $tranche : $max;
        }, []);
        
        return [
            'niveau' => $tranchePrincipale['tranche'],
            'description' => $tranchePrincipale['description']
        ];
    }
    
    /**
     * Génère une référence unique pour la transaction
     */
    private function genererReference(): string {
        return 'WOY' . date('Ymd') . rand(1000, 9999);
    }
    
    /**
     * Génère un code de recharge basé sur les kWh
     */
    private function genererCodeRecharge(float $kwh): string {
        $base = str_pad((string)round($kwh * 100), 8, '0', STR_PAD_LEFT);
        return substr($base, 0, 4) . '-' . substr($base, 4, 4);
    }
    
    /**
     * Retourne les tranches configurées
     */
    public function getTranches(): array {
        return array_map(function($config) {
            return new Tranche(
                $config['niveau'],
                $config['min'],
                $config['max'],
                $config['prix'],
                $config['desc']
            );
        }, self::$tranches);
    }
    
    /**
     * Met à jour la configuration des tranches (pour l'admin)
     */
    public static function configurerTranches(array $nouvellesTorches): void {
        self::$tranches = $nouvellesTorches;
    }
}
