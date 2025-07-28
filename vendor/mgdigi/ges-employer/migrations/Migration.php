<?php

function prompt(string $message): string {
    echo $message;
    return trim(fgets(STDIN));
}

function writeEnvIfNotExists(array $config): void {
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath)) {
        $env = <<<ENV
DB_DRIVER={$config['driver']}
DB_HOST={$config['host']}
DB_PORT={$config['port']}
DB_NAME={$config['dbname']}
DB_USER={$config['user']}
DB_PASSWORD={$config['pass']}

dns="{$config['driver']}:host={$config['host']};dbname={$config['dbname']};port={$config['port']}"
ENV;
        file_put_contents($envPath, $env);
        echo ".env généré avec succès à la racine du projet.\n";
    } else {
        echo "ℹ Le fichier .env existe déjà, aucune modification.\n";
    }
}

$driver = strtolower(prompt("Quel SGBD utiliser ? (mysql / pgsql) : "));
$host = prompt("Hôte (default: 127.0.0.1) : ") ?: "127.0.0.1";
$port = prompt("Port (default: 3307 ou 5432) : ") ?: ($driver === 'pgsql' ? "5432" : "3307");
$user = prompt("Utilisateur (default: root) : ") ?: "root";
$pass = prompt("Mot de passe : ");
$dbName = prompt("Nom de la base à créer : ");

try {
    $initialDb = $driver === 'pgsql' ? 'postgres' : null;
    $dsn = "$driver:host=$host;port=$port" . ($initialDb ? ";dbname=$initialDb" : '');
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($driver === 'mysql') {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Base de données MySQL `$dbName` créée.\n";
    } elseif ($driver === 'pgsql') {
        $check = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$dbName'")->fetch();
        if (!$check) {
            $pdo->exec("CREATE DATABASE \"$dbName\"");
            echo "✅ Base de données PostgreSQL `$dbName` créée.\n";
        } else {
            echo "ℹ La base PostgreSQL `$dbName` existe déjà.\n";
        }
    }

    // Reconnexion à la bonne base
    $dsn = "$driver:host=$host;port=$port;dbname=$dbName";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer ENUM PostgreSQL si nécessaire
    if ($driver === 'pgsql') {
        $pdo->exec("DO $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'statut_type') THEN
                CREATE TYPE statut_type AS ENUM ('success', 'echec');
            END IF;
        END$$;");
    }

    // Création des tables
    if ($driver === 'mysql') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS compteur (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero VARCHAR(20) NOT NULL UNIQUE
            );
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS paiement (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_compteur INT NOT NULL,
                montant DECIMAL(10,2) NOT NULL,
                reference VARCHAR(100),
                code_recharge VARCHAR(100),
                nbre_kwt INT,
                tranche VARCHAR(50),
                prix_kwh DECIMAL(10,2),
                date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    id_client VARCHAR(100),
                statut ENUM('success', 'echec'),
                FOREIGN KEY (id_compteur) REFERENCES compteur(id) ON DELETE CASCADE
            );
        ");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS compteur (
                id SERIAL PRIMARY KEY,
                numero VARCHAR(20) NOT NULL UNIQUE
            );
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS paiement (
                id SERIAL PRIMARY KEY,
                id_compteur INT NOT NULL,
                montant DECIMAL(10,2) NOT NULL,
                reference VARCHAR(100),
                code_recharge VARCHAR(100),
                nbre_kwt INT,
                tranche VARCHAR(50),
                prix_kwh DECIMAL(10,2),
                date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    id_client VARCHAR(100), 

                statut statut_type,
                FOREIGN KEY (id_compteur) REFERENCES compteur(id) ON DELETE CASCADE
            );
        ");
    }

    echo "✅ Tables `compteur` et `paiement` créées dans `$dbName`.\n";

    writeEnvIfNotExists([
        'driver' => $driver,
        'host' => $host,
        'port' => $port,
        'user' => $user,
        'pass' => $pass,
        'dbname' => $dbName
    ]);

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
