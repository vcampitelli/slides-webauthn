<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->setBasePath('/api');
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->post('/register/init', \App\Application\Actions\RegisterAction::class);
    $app->post('/register/attestation', \App\Application\Actions\RegisterAttestationAction::class);
    $app->post('/login/init', \App\Application\Actions\LoginAction::class);
    $app->post('/login/assertion', \App\Application\Actions\LoginAssertionAction::class);
};
