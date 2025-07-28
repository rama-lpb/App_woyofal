<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Chemin vers le fichier de migration contenu dans le package
$migrationPath = __DIR__ . '/../vendor/mgdigi/ges-employer/seeders/Seeder.php';

if (!file_exists($migrationPath)) {
    echo "❌ Le fichier de migration est introuvable à : $migrationPath\n";
    exit(1);
}

require $migrationPath;
