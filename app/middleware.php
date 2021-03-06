<?php

use App\Application\Middleware\CorsMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $container = $app->getContainer();

    $settings = $container->get('settings');
    $logger = $container->get(LoggerInterface::class);

    // JWT Middleware MUST be before other middlewares (especially CORS)
    $app->add(
        new JwtAuthentication(
            [
                //      'path' => '/api', /* or ["/api", "/admin"] */
                'ignore' => ['/frontend', '/login', '/hello'],
                'secret' => 'ipa-project',//$settings['settings']['jwt']['secret'],
                'algorithm' => ['HS256'],
                'logger' => $logger,
                // HTTPS not mandatory for local development
                'relaxed' => ['localhost', 'dev.time-sheet-api', 'http://dev.time-sheet-frontend'],
                'error' => function ($response, $arguments) {
                    $data['status'] = 'error';
                    $data['message'] = $arguments['message'];
                    return $response->getBody()->write(
                        json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT, 512)
                    );
                }
            ]
        )
    );

    $app->add(CorsMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);
};
