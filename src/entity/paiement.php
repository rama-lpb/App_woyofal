<?php
namespace App\Entity;

use App\Core\Abstract\AbstractEntity;
use App\Enum\StatutPaiement;

class Paiement extends AbstractEntity {
    private int $id;
    private int $idCompteur;
    private float $montant;
    private ?string $reference;
    private ?string $codeRecharge;
    private ?int $nbreKwt;
    private ?string $tranche;
    private ?float $prixKwh;
    private string $dateAchat;
    private StatutPaiement $statut;

    public function __construct(
        int $id = 0,
        int $idCompteur = 0,
        float $montant = 0.0,
        ?string $reference = null,
        ?string $codeRecharge = null,
        ?int $nbreKwt = null,
        ?string $tranche = null,
        ?float $prixKwh = null,
        string $dateAchat = '',
        StatutPaiement $statut = StatutPaiement::SUCCESS
    ) {
        $this->id = $id;
        $this->idCompteur = $idCompteur;
        $this->montant = $montant;
        $this->reference = $reference;
        $this->codeRecharge = $codeRecharge;
        $this->nbreKwt = $nbreKwt;
        $this->tranche = $tranche;
        $this->prixKwh = $prixKwh;
        $this->dateAchat = $dateAchat;
        $this->statut = $statut;
    }

    public static function toObject(array $data): static {
        return new static(
            $data['id'],
            $data['id_compteur'],
            $data['montant'],
            $data['reference'] ?? null,
            $data['code_recharge'] ?? null,
            $data['nbre_kwt'] ?? null,
            $data['tranche'] ?? null,
            $data['prix_kwh'] ?? null,
            $data['date_achat'] ?? '',
            StatutPaiement::from($data['statut'] ?? 'success')
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'id_compteur' => $this->idCompteur,
            'montant' => $this->montant,
            'reference' => $this->reference,
            'code_recharge' => $this->codeRecharge,
            'nbre_kwt' => $this->nbreKwt,
            'tranche' => $this->tranche,
            'prix_kwh' => $this->prixKwh,
            'date_achat' => $this->dateAchat,
            'statut' => $this->statut->value
        ];
    }

    // Getters/Setters à ajouter selon besoin
}
