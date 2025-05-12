<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\MonitorService;
use App\Service\ProjectService;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class GraphQLController
{
    private ProjectService $projectService;
    private MonitorService $monitorService;
    private LoggerInterface $logger;

    public function __construct(
        ProjectService $projectService,
        MonitorService $monitorService,
        LoggerInterface $logger
    ) {
        $this->projectService = $projectService;
        $this->monitorService = $monitorService;
        $this->logger = $logger;
    }

    public function execute(Request $request, Response $response): Response
    {
        $input = $request->getParsedBody();
        $query = $input['query'] ?? null;
        $variables = $input['variables'] ?? null;
        
        if ($query === null) {
            $response->getBody()->write(json_encode([
                'errors' => [['message' => 'Query is required']]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        // Define GraphQL types
        $statusType = new ObjectType([
            'name' => 'Status',
            'fields' => [
                'date' => [
                    'type' => Type::string(),
                    'resolve' => function ($status) {
                        return $status->getStartTime()->format(\DateTime::ATOM);
                    }
                ],
                'ok' => [
                    'type' => Type::boolean(),
                    'resolve' => function ($status) {
                        return $status->getStatus();
                    }
                ],
                'responseTime' => [
                    'type' => Type::int(),
                    'resolve' => function ($status) {
                        return $status->getResponseTime();
                    }
                ]
            ]
        ]);
        
        $monitorType = new ObjectType([
            'name' => 'Monitor',
            'fields' => [
                'identifier' => [
                    'type' => Type::id(),
                    'resolve' => function ($monitor) {
                        return $monitor->getId();
                    }
                ],
                'label' => [
                    'type' => Type::id(),
                    'resolve' => function ($monitor) {
                        return $monitor->getLabel();
                    }
                ],
                'type' => [
                    'type' => Type::string(),
                    'resolve' => function ($monitor) {
                        $class = get_class($monitor);
                        return strtolower(substr($class, strrpos($class, '\\') + 1, -7)); // Extract type from class name
                    }
                ],
                'periodicity' => [
                    'type' => Type::int(),
                    'resolve' => function ($monitor) {
                        return $monitor->getPeriodicity();
                    }
                ],
                'host' => [
                    'type' => Type::string(),
                    'resolve' => function ($monitor) {
                        return $monitor->getHost() ?? null;
                    }
                ],
                'url' => [
                    'type' => Type::string(),
                    'resolve' => function ($monitor) {
                        return $monitor->getUrl() ?? null;
                    }
                ],
                'badgeUrl' => [
                    'type' => Type::string(),
                    'resolve' => function ($monitor) {
                        return "/api/v1/monitors/{$monitor->getId()}/badge";
                    }
                ]
            ]
        ]);
        
        $projectType = new ObjectType([
            'name' => 'Project',
            'fields' => [
                'identifier' => [
                    'type' => Type::id(),
                    'resolve' => function ($project) {
                        return $project->getId();
                    }
                ],
                'label' => [
                    'type' => Type::id(),
                    'resolve' => function ($project) {
                        return $project->getLabel();
                    }
                ],
                'description' => [
                    'type' => Type::id(),
                    'resolve' => function ($project) {
                        return $project->getDescription();
                    }
                ],
                'monitors' => [
                    'type' => Type::listOf($monitorType),
                    'resolve' => function ($project) {
                        return $project->getMonitors()->toArray();
                    }
                ]
            ]
        ]);
        
        // Define query type
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'projects' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($projectType))),
                    'resolve' => function ($root, $args) {
                        return $this->projectService->getAllProjects(100, 0);
                    }
                ],
                'status' => [
                    'type' => Type::listOf(Type::nonNull($statusType)),
                    'args' => [
                        'monitorIdentifier' => Type::nonNull(Type::string()),
                        'from' => Type::int(),
                        'to' => Type::int()
                    ],
                    'resolve' => function ($root, $args) {
                        $monitorId = (int) $args['monitorIdentifier'];
                        
                        if (isset($args['from']) && isset($args['to'])) {
                            $from = new \DateTime('@' . $args['from']);
                            $to = new \DateTime('@' . $args['to']);
                            
                            // This is a simplified example - you would need to implement a method to get statuses by time range
                            return $this->monitorService->getMonitorStatus($monitorId, 100, 0);
                        }
                        
                        return $this->monitorService->getMonitorStatus($monitorId, 100, 0);
                    }
                ]
            ]
        ]);
        
        // Create schema
        $schema = new Schema([
            'query' => $queryType
        ]);
        
        try {
            // Execute the query
            $debug = $_ENV['APP_ENV'] === 'dev' ? DebugFlag::INCLUDE_DEBUG_MESSAGE : DebugFlag::NONE;
            $result = GraphQL::executeQuery($schema, $query, null, null, $variables)
                ->setErrorsHandler(function (array $errors, callable $formatter) use ($debug) {
                    return array_map($formatter, $errors);
                })
                ->toArray($debug);
            
            $response->getBody()->write(json_encode($result));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $this->logger->error('GraphQL error: ' . $e->getMessage());
            
            $response->getBody()->write(json_encode([
                'errors' => [['message' => $e->getMessage()]]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
} 