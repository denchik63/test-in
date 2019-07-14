<?php
return [
    'route' => [
        '/api/auth' => [
            'controller' => \Application\Controller\IndexController::class,
            'action' => 'auth',
        ],
        '/api/generateProducts' => [
            'controller' => \Application\Controller\IndexController::class,
            'action' => 'generateProducts',
        ],
        '/api/getAllProducts' => [
            'controller' => \Application\Controller\IndexController::class,
            'action' => 'getAllProducts',
        ],
        '/api/createOrder' => [
            'controller' => \Application\Controller\IndexController::class,
            'action' => 'createOrder',
        ],
        '/api/payOrder' => [
            'controller' => \Application\Controller\IndexController::class,
            'action' => 'payOrder',
        ],
    ],
    'mysql' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => 'root',
        'db' => 'vsein',
    ],
    'auth' => [
        'login' => 'admin',
        'pass' => '12345',
    ],
];