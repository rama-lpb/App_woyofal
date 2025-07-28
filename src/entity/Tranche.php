<?php
namespace App\Entity;

class Tranche {
    private int $niveau;
    private float $seuilMin;
    private float $seuilMax;
    private float $prixKwh;
    private string $description;

    public function __construct(
        int $niveau,
        float $seuilMin,
        float $seuilMax,
        float $prixKwh,
        string $description = ''
    ) {
        $this->niveau = $niveau;
        $this->seuilMin = $seuilMin;
        $this->seuilMax = $seuilMax;
        $this->prixKwh = $prixKwh;
        $this->description = $description;
    }

    // Getters
    public function getNiveau(): int { return $this->niveau; }
    public function getSeuilMin(): float { return $this->seuilMin; }
    public function getSeuilMax(): float { return $this->seuilMax; }
    public function getPrixKwh(): float { return $this->prixKwh; }
    public function getDescription(): string { return $this->description; }

    /**
     * Vérifie si un montant appartient à cette tranche
     */
    public function contientMontant(float $montant): bool {
        return $montant >= $this->seuilMin && 
               ($this->seuilMax === 0 || $montant <= $this->seuilMax);
    }

    /**
     * Calcule le montant applicable à cette tranche
     */
    public function calculerMontantTranche(float $montantTotal, float $montantDejaConsomme = 0): float {
        $montantRestant = $montantTotal - $montantDejaConsomme;
        
        if ($montantRestant <= 0) {
            return 0;
        }

        $montantDansTrancheMin = max(0, $this->seuilMin - $montantDejaConsomme);
        $montantDansTrancheMax = $this->seuilMax === 0 ? $montantRestant : 
                                min($montantRestant, $this->seuilMax - $montantDejaConsomme);

        return max(0, $montantDansTrancheMax - $montantDansTrancheMin);
    }

    public function toArray(): array {
        return [
            'niveau' => $this->niveau,
            'seuil_min' => $this->seuilMin,
            'seuil_max' => $this->seuilMax,
            'prix_kwh' => $this->prixKwh,
            'description' => $this->description
        ];
    }
}
