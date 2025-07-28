<?php
namespace App\Entity;

use App\Core\Abstract\AbstractEntity;

class Compteur extends AbstractEntity {
    private int $id;
    private string $numero;
    private int $idClient;
    private string $nomClient;
    private string $prenomClient;
    private bool $actif;
    private string $dateInstallation;
    private ?string $adresse;

    public function __construct(
        int $id = 0,
        string $numero = '',
        int $idClient = 0,
        string $nomClient = '',
        string $prenomClient = '',
        bool $actif = true,
        string $dateInstallation = '',
        ?string $adresse = null
    ) {
        $this->id = $id;
        $this->numero = $numero;
        $this->idClient = $idClient;
        $this->nomClient = $nomClient;
        $this->prenomClient = $prenomClient;
        $this->actif = $actif;
        $this->dateInstallation = $dateInstallation;
        $this->adresse = $adresse;
    }

    public static function toObject(array $data): static {
        return new static(
            $data['id'] ?? 0,
            $data['numero'] ?? '',
            $data['id_client'] ?? 0,
            $data['nom_client'] ?? '',
            $data['prenom_client'] ?? '',
            (bool)($data['actif'] ?? true),
            $data['date_installation'] ?? '',
            $data['adresse'] ?? null
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'id_client' => $this->idClient,
            'nom_client' => $this->nomClient,
            'prenom_client' => $this->prenomClient,
            'actif' => $this->actif,
            'date_installation' => $this->dateInstallation,
            'adresse' => $this->adresse
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNumero(): string { return $this->numero; }
    public function getIdClient(): int { return $this->idClient; }
    public function getNomClient(): string { return $this->nomClient; }
    public function getPrenomClient(): string { return $this->prenomClient; }
    public function getClientComplet(): string { 
        return trim($this->prenomClient . ' ' . $this->nomClient);
    }
    public function isActif(): bool { return $this->actif; }
    public function getDateInstallation(): string { return $this->dateInstallation; }
    public function getAdresse(): ?string { return $this->adresse; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setNumero(string $numero): void { $this->numero = $numero; }
    public function setIdClient(int $idClient): void { $this->idClient = $idClient; }
    public function setNomClient(string $nomClient): void { $this->nomClient = $nomClient; }
    public function setPrenomClient(string $prenomClient): void { $this->prenomClient = $prenomClient; }
    public function setActif(bool $actif): void { $this->actif = $actif; }
    public function setDateInstallation(string $dateInstallation): void { $this->dateInstallation = $dateInstallation; }
    public function setAdresse(?string $adresse): void { $this->adresse = $adresse; }
}
