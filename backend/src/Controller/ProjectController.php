<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ProjectService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class ProjectController
{
    private ProjectService $projectService;
    private LoggerInterface $logger;

    public function __construct(ProjectService $projectService, LoggerInterface $logger)
    {
        $this->projectService = $projectService;
        $this->logger = $logger;
    }

    public function getAll(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 10;
        $offset = isset($queryParams['offset']) ? (int) $queryParams['offset'] : 0;
        $sortBy = $queryParams['sortBy'] ?? null;
        $sortOrder = $queryParams['sortOrder'] ?? null;
        
        $projects = $this->projectService->getAllProjects($limit, $offset, $sortBy, $sortOrder);
        
        $response->getBody()->write(json_encode([
            'data' => $projects,
            'count' => $this->projectService->getProjectCount(),
            'limit' => $limit,
            'offset' => $offset
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function getOne(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $project = $this->projectService->getProjectById($id);
        
        if (!$project) {
            $response->getBody()->write(json_encode([
                'error' => 'Project not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $response->getBody()->write(json_encode([
            'data' => $project
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        if (!isset($data['label'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Label is required'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        $project = $this->projectService->createProject($data);
        
        $response->getBody()->write(json_encode([
            'data' => $project,
            'message' => 'Project created successfully'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();
        
        $project = $this->projectService->updateProject($id, $data);
        
        if (!$project) {
            $response->getBody()->write(json_encode([
                'error' => 'Project not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $response->getBody()->write(json_encode([
            'data' => $project,
            'message' => 'Project updated successfully'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $result = $this->projectService->deleteProject($id);
        
        if (!$result) {
            $response->getBody()->write(json_encode([
                'error' => 'Project not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $response->getBody()->write(json_encode([
            'message' => 'Project deleted successfully'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
} 