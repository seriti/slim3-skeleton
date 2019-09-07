<?php
return [
    'settings' => [
        'displayErrorDetails' => DEBUG, // set in env.php
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        //'determineRouteBeforeAppMiddleware' => true, // Set this if you need access to route within middleware

        // View/Tremplate settings
        'templates' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'seriti-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ]
    ],
];
