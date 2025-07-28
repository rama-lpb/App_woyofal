<?php
namespace App\Entity;

use App\Enum\StatutPaiement;

class LogTransaction {
    private int $id;
    private string $dateHeure;
    private string $localisation;
    private string $adresseIp;
    private StatutPaiement $statut;
    private string $numeroCompteur;
    private ?string $codeRecharge;
    private ?float $nombreKwt;
    private ?string $userAgent;
    private ?string $detailsErreur;

    public function __construct(
        int $id = 0,
        string $dateHeure = '',
        string $localisation = '',
        string $adresseIp = '',
        StatutPaiement $statut = StatutPaiement::SUCCESS,
        string $numeroCompteur = '',
        ?string $codeRecharge = null,
        ?float $nombreKwt = null,
        ?string $userAgent = null,
        ?string $detailsErreur = null
    ) {
        $this->id = $id;
        $this->dateHeure = $dateHeure ?: date('Y-m-d H:i:s');
        $this->localisation = $localisation;
        $this->adresseIp = $adresseIp;
        $this->statut = $statut;
        $this->numeroCompteur = $numeroCompteur;
        $this->codeRecharge = $codeRecharge;
        $this->nombreKwt = $nombreKwt;
        $this->userAgent = $userAgent;
        $this->detailsErreur = $detailsErreur;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'date_heure' => $this->dateHeure,
            'localisation' => $this->localisation,
            'adresse_ip' => $this->adresseIp,
            'statut' => $this->statut->value,
            'numero_compteur' => $this->numeroCompteur,
            'code_recharge' => $this->codeRecharge,
            'nombre_kwt' => $this->nombreKwt,
            'user_agent' => $this->userAgent,
            'details_erreur' => $this->detailsErreur
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getDateHeure(): string { return $this->dateHeure; }
    public function getLocalisation(): string { return $this->localisation; }
    public function getAdresseIp(): string { return $this->adresseIp; }
    public function getStatut(): StatutPaiement { return $this->statut; }
    public function getNumeroCompteur(): string { return $this->numeroCompteur; }
    public function getCodeRecharge(): ?string { return $this->codeRecharge; }
    public function getNombreKwt(): ?float { return $this->nombreKwt; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function getDetailsErreur(): ?string { return $this->detailsErreur; }
}
