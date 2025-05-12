<?php
declare(strict_types=1);

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {
    // CORS middleware
    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });

    // Content Length middleware
    $app->add(new ContentLengthMiddleware());

    // Error Middleware
    $errorMiddleware = $app->addErrorMiddleware(
        isset($_ENV['APP_DEBUG']) ? (bool)$_ENV['APP_DEBUG'] : false,
        true,
        true
    );

    // Body parsing middleware
    $app->addBodyParsingMiddleware();
}; 