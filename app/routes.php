<?php

use App\Application\Actions\PreflightAction;
use App\Controller\AuthController;
use App\Controllers\TimeSheet\TimeSheetController;
use App\Controllers\User\UserController;
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

    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->options('', PreflightAction::class); // Allow preflight requests
        $group->get('', UserController::class . ':list');
        $group->post('', UserController::class . ':create');

        $group->options('/{id:[0-9]+}', PreflightAction::class); // Allow preflight requests
        $group->get('/{id:[0-9]+}', UserController::class . ':get');
        $group->put('/{id:[0-9]+}', UserController::class . ':update');
        $group->delete('/{id:[0-9]+}', UserController::class . ':delete');
    });

    $app->group('/timers', function (RouteCollectorProxy $group) {
        $group->options('', PreflightAction::class);  // Allow preflight requests
        $group->get('', TimeSheetController::class . ':list');
        $group->post('', TimeSheetController::class . ':startTime');
        $group->put('', TimeSheetController::class . ':stopTime');


        $group->options('/{id:[0-9]+}', PreflightAction::class); // Allow preflight requests
        $group->get('/{id:[0-9]+}', TimeSheetController::class . ':get');
        $group->put('/{id:[0-9]+}', TimeSheetController::class . ':update');
        $group->delete('/{id:[0-9]+}', TimeSheetController::class . ':delete');
    });

    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];
        $response->getBody()->write("Hello, $name");
        $response->getBody()->write(json_encode($response->getStatusCode()));
        throw new HttpInternalServerErrorException('Nooooooooooooooo!');
        return $response;
    });

/*    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });*/


    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last
     */
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
