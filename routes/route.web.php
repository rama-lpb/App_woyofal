<?php

use App\Controller\paiementController;
use App\Entity\Paiement;

$routes = [
    'GET:/api/citoyens' => [
        'controller' => paiementController::class,
        'method' => 'getinfos',
    ],

    'GET:/api/citoyens/{cni}' => [
        'controller' => paiementController::class,
        'method' => 'show',
    ]

];