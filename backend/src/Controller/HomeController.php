<?php
declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
    public function index(Request $request, Response $response): Response
    {
        $data = [
            'name' => 'OwlEyes',
            'description' => 'A simple monitoring service similar to UptimeRobot',
            'repository' => 'https://github.com/martinlejko/OwlEyes',
        ];
        
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
} 