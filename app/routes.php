<?php

use App\Application\Actions\PreflightAction;
use App\Controller\AuthController;
use App\Controllers\TimeSheet\TimeSheetController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->options('/login', PreflightAction::class); // Allow preflight requests
    $app->post('/login', AuthController::class . ':login')->setName('auth.login');

    $app->options('/register', PreflightAction::class); // Allow preflight requests
    $app->post('/register', AuthController::class . ':register')->setName('auth.register');

    $app->group(
        '/timers',
        function (RouteCollectorProxy $group) {
            $group->options('', PreflightAction::class);  // Allow preflight requests
            $group->get('', TimeSheetController::class . ':getTimer');
            $group->post('', TimeSheetController::class . ':startTime');
            $group->put('', TimeSheetController::class . ':stopTime');


            $group->options('/{id:[0-9]+}', PreflightAction::class); // Allow preflight requests
            $group->get('/{id:[0-9]+}', TimeSheetController::class . ':get');
            $group->put('/{id:[0-9]+}', TimeSheetController::class . ':update');
            $group->delete('/{id:[0-9]+}', TimeSheetController::class . ':delete');
        }
    );

    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last
     */
    $app->map(
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        '/{routes:.+}',
        function ($request, $response) {
            throw new HttpNotFoundException($request);
        }
    );
};
