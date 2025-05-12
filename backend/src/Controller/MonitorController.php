<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\MonitorService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class MonitorController
{
    private MonitorService $monitorService;
    private LoggerInterface $logger;

    public function __construct(MonitorService $monitorService, LoggerInterface $logger)
    {
        $this->monitorService = $monitorService;
        $this->logger = $logger;
    }

    public function getAllByProject(Request $request, Response $response, array $args): Response
    {
        $projectId = (int) $args['id'];
        $queryParams = $request->getQueryParams();
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 10;
        $offset = isset($queryParams['offset']) ? (int) $queryParams['offset'] : 0;
        
        $monitors = $this->monitorService->getMonitorsByProject($projectId, $limit, $offset);
        
        $response->getBody()->write(json_encode([
            'data' => $monitors,
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
        $monitor = $this->monitorService->getMonitorById($id);
        
        if (!$monitor) {
            $response->getBody()->write(json_encode([
                'error' => 'Monitor not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $response->getBody()->write(json_encode([
            'data' => $monitor
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $projectId = (int) $args['id'];
        $data = $request->getParsedBody();
        
        if (!isset($data['type']) || !in_array($data['type'], ['ping', 'website'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Valid monitor type is required (ping or website)'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        if (!isset($data['label'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Label is required'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        if (!isset($data['periodicity']) || (int) $data['periodicity'] < 5 || (int) $data['periodicity'] > 300) {
            $response->getBody()->write(json_encode([
                'error' => 'Periodicity must be between 5 and 300 seconds'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        if ($data['type'] === 'ping') {
            if (!isset($data['host'])) {
                $response->getBody()->write(json_encode([
                    'error' => 'Host is required for ping monitor'
                ]));
                
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }
            
            if (!isset($data['port']) || (int) $data['port'] <= 0) {
                $response->getBody()->write(json_encode([
                    'error' => 'Valid port is required for ping monitor'
                ]));
                
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }
            
            $monitor = $this->monitorService->createPingMonitor($projectId, $data);
        } else {
            if (!isset($data['url'])) {
                $response->getBody()->write(json_encode([
                    'error' => 'URL is required for website monitor'
                ]));
                
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }
            
            $monitor = $this->monitorService->createWebsiteMonitor($projectId, $data);
        }
        
        if (!$monitor) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to create monitor'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
        
        $response->getBody()->write(json_encode([
            'data' => $monitor,
            'message' => 'Monitor created successfully'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();
        
        $monitor = $this->monitorService->updateMonitor($id, $data);
        
        if (!$monitor) {
            $response->getBody()->write(json_encode([
                'error' => 'Monitor not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $response->getBody()->write(json_encode([
            'data' => $monitor,
            'message' => 'Monitor updated successfully'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $result = $this->monitorService->deleteMonitor($id);
        
        if (!$result) {
            $response->getBody()->write(json_encode([
                'error' => 'Monitor not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $response->getBody()->write(json_encode([
            'message' => 'Monitor deleted successfully'
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function getStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $queryParams = $request->getQueryParams();
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 10;
        $offset = isset($queryParams['offset']) ? (int) $queryParams['offset'] : 0;
        
        $monitor = $this->monitorService->getMonitorById($id);
        
        if (!$monitor) {
            $response->getBody()->write(json_encode([
                'error' => 'Monitor not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $statuses = $this->monitorService->getMonitorStatus($id, $limit, $offset);
        
        $response->getBody()->write(json_encode([
            'data' => $statuses,
            'monitor' => $monitor,
            'limit' => $limit,
            'offset' => $offset
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function getBadge(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $monitor = $this->monitorService->getMonitorById($id);
        
        if (!$monitor) {
            $response->getBody()->write(json_encode([
                'error' => 'Monitor not found'
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        $status = $this->monitorService->getLatestStatus($id);
        
        $label = $monitor->getBadgeLabel();
        $value = $status && $status->getStatus() ? 'up' : 'down';
        $color = $status && $status->getStatus() ? 'green' : 'red';
        
        // Create SVG badge
        $labelWidth = strlen($label) * 7 + 10; // Approximate width calculation
        $valueWidth = strlen($value) * 7 + 10;
        $totalWidth = $labelWidth + $valueWidth;
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $totalWidth . '" height="20">';
        $svg .= '<linearGradient id="b" x2="0" y2="100%">';
        $svg .= '<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>';
        $svg .= '<stop offset="1" stop-opacity=".1"/>';
        $svg .= '</linearGradient>';
        $svg .= '<mask id="a">';
        $svg .= '<rect width="' . $totalWidth . '" height="20" rx="3" fill="#fff"/>';
        $svg .= '</mask>';
        $svg .= '<g mask="url(#a)">';
        $svg .= '<path fill="#555" d="M0 0h' . $labelWidth . 'v20H0z"/>';
        $svg .= '<path fill="' . $color . '" d="M' . $labelWidth . ' 0h' . $valueWidth . 'v20H' . $labelWidth . 'z"/>';
        $svg .= '<path fill="url(#b)" d="M0 0h' . $totalWidth . 'v20H0z"/>';
        $svg .= '</g>';
        $svg .= '<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">';
        $svg .= '<text x="' . ($labelWidth/2) . '" y="15" fill="#010101" fill-opacity=".3">' . $label . '</text>';
        $svg .= '<text x="' . ($labelWidth/2) . '" y="14">' . $label . '</text>';
        $svg .= '<text x="' . ($labelWidth + $valueWidth/2) . '" y="15" fill="#010101" fill-opacity=".3">' . $value . '</text>';
        $svg .= '<text x="' . ($labelWidth + $valueWidth/2) . '" y="14">' . $value . '</text>';
        $svg .= '</g>';
        $svg .= '</svg>';
        
        $response->getBody()->write($svg);
        
        return $response
            ->withHeader('Content-Type', 'image/svg+xml')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withStatus(200);
    }
} 