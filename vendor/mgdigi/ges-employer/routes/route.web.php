<?php

use App\Controller\CitoyenController;

$routes = [
    'GET:/api/citoyens' => [
        'controller' => CitoyenController::class,
        'method' => 'index',
    ],

    'GET:/api/citoyens/{cni}' => [
        'controller' => CitoyenController::class,
        'method' => 'show',
    ]

];