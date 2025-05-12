<?php
declare(strict_types=1);

use App\Controller\GraphQLController;
use App\Controller\HomeController;
use App\Controller\MonitorController;
use App\Controller\ProjectController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // API v1 group
    $app->group('/api/v1', function (RouteCollectorProxy $group) {
        // Projects
        $group->get('/projects', [ProjectController::class, 'getAll']);
        $group->get('/projects/{id}', [ProjectController::class, 'getOne']);
        $group->post('/projects', [ProjectController::class, 'create']);
        $group->put('/projects/{id}', [ProjectController::class, 'update']);
        $group->delete('/projects/{id}', [ProjectController::class, 'delete']);

        // Monitors
        $group->get('/projects/{id}/monitors', [MonitorController::class, 'getAllByProject']);
        $group->get('/monitors/{id}', [MonitorController::class, 'getOne']);
        $group->post('/projects/{id}/monitors', [MonitorController::class, 'create']);
        $group->put('/monitors/{id}', [MonitorController::class, 'update']);
        $group->delete('/monitors/{id}', [MonitorController::class, 'delete']);
        
        // Monitor status
        $group->get('/monitors/{id}/status', [MonitorController::class, 'getStatus']);
        
        // Monitor badge
        $group->get('/monitors/{id}/badge', [MonitorController::class, 'getBadge']);
    });
    
    // GraphQL endpoint
    $app->post('/graphql', [GraphQLController::class, 'execute']);
    
    // Home page
    $app->get('/', [HomeController::class, 'index']);
}; 