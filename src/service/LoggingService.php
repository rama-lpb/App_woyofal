<?php
namespace App\Service;

use App\Entity\LogTransaction;
use App\Enum\StatutPaiement;

class LoggingService {

    /**
     * Enregistre une tentative d'achat de crédit
     */
    public function logTransaction(
        string $numeroCompteur,
        StatutPaiement $statut,
        ?string $codeRecharge = null,
        ?float $nombreKwt = null,
        ?string $detailsErreur = null
    ): LogTransaction {
        
        $log = new LogTransaction(
            0, // ID auto-généré
            date('Y-m-d H:i:s'),
            $this->getLocalisation(),
            $this->getAdresseIp(),
            $statut,
            $numeroCompteur,
            $codeRecharge,
            $nombreKwt,
            $this->getUserAgent(),
            $detailsErreur
        );

        // Ici on pourrait sauvegarder en base via un LogRepository
        $this->sauvegarderLog($log);
        
        return $log;
    }

    /**
     * Récupère l'adresse IP du client
     */
    private function getAdresseIp(): string {
        $headers = [
            'HTTP_CLIENT_IP',            
            'HTTP_X_FORWARDED_FOR',      
            'HTTP_X_FORWARDED',          
            'HTTP_X_CLUSTER_CLIENT_IP',  
            'HTTP_FORWARDED_FOR',       
            'HTTP_FORWARDED',           
            'REMOTE_ADDR'                
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Récupère le User-Agent du client
     */
    private function getUserAgent(): string {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    /**
     * Récupère la localisation (peut être amélioré avec une API de géolocalisation)
     */
    private function getLocalisation(): string {
       
        $ip = $this->getAdresseIp();
        
        if ($ip === '127.0.0.1' || $ip === 'localhost' || str_starts_with($ip, '192.168.')) {
            return 'Local';
        }
 
        return 'Sénégal, Dakar';
    }

    /**
     * Sauvegarde le log (à implémenter avec un repository)
     */
    private function sauvegarderLog(LogTransaction $log): void {
        $logData = json_encode($log->toArray()) . "\n";
        
        $logFile = __DIR__ . '/../../logs/transactions_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
        
        
    }

    /**
     * Log spécifique pour les erreurs
     */
    public function logErreur(string $numeroCompteur, string $messageErreur, int $codeErreur = 400): LogTransaction {
        return $this->logTransaction(
            $numeroCompteur,
            StatutPaiement::ECHEC,
            null,
            null,
            "Code: $codeErreur - $messageErreur"
        );
    }

    /**
     * Log spécifique pour les succès
     */
    public function logSucces(string $numeroCompteur, string $codeRecharge, float $nombreKwt): LogTransaction {
        return $this->logTransaction(
            $numeroCompteur,
            StatutPaiement::SUCCESS,
            $codeRecharge,
            $nombreKwt
        );
    }
}
