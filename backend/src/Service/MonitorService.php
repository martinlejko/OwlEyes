<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Monitor;
use App\Entity\MonitorStatus;
use App\Entity\PingMonitor;
use App\Entity\WebsiteMonitor;
use App\Repository\MonitorRepository;
use App\Repository\MonitorStatusRepository;
use Psr\Log\LoggerInterface;

class MonitorService
{
    private MonitorRepository $monitorRepository;
    private LoggerInterface $logger;
    private ?MonitorStatusRepository $statusRepository;

    public function __construct(
        MonitorRepository $monitorRepository,
        LoggerInterface $logger,
        ?MonitorStatusRepository $statusRepository = null
    ) {
        $this->monitorRepository = $monitorRepository;
        $this->logger = $logger;
        $this->statusRepository = $statusRepository;
    }

    public function getAllMonitors(int $limit = 10, int $offset = 0): array
    {
        $this->logger->info('Fetching all monitors', [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        return $this->monitorRepository->findAll($limit, $offset);
    }

    public function getMonitorById(int $id): ?Monitor
    {
        $this->logger->info('Fetching monitor by ID', ['id' => $id]);
        
        return $this->monitorRepository->findById($id);
    }

    public function getMonitorsByProject(int $projectId, int $limit = 10, int $offset = 0): array
    {
        $this->logger->info('Fetching monitors by project', [
            'projectId' => $projectId,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        return $this->monitorRepository->findByProject($projectId, $limit, $offset);
    }

    public function createPingMonitor(int $projectId, array $data): ?PingMonitor
    {
        $this->logger->info('Creating new ping monitor', [
            'projectId' => $projectId,
            'data' => $data
        ]);
        
        $projectService = new ProjectService($this->monitorRepository->getEntityManager()->getRepository('App\Entity\Project'), $this->logger);
        $project = $projectService->getProjectById($projectId);
        
        if (!$project) {
            $this->logger->warning('Project not found', ['id' => $projectId]);
            return null;
        }
        
        $monitor = new PingMonitor();
        $monitor->setProject($project);
        $monitor->setLabel($data['label']);
        $monitor->setPeriodicity($data['periodicity']);
        $monitor->setBadgeLabel($data['badgeLabel'] ?? $data['label']);
        $monitor->setHost($data['host']);
        $monitor->setPort($data['port']);
        
        $this->monitorRepository->save($monitor);
        
        return $monitor;
    }

    public function createWebsiteMonitor(int $projectId, array $data): ?WebsiteMonitor
    {
        $this->logger->info('Creating new website monitor', [
            'projectId' => $projectId,
            'data' => $data
        ]);
        
        $projectService = new ProjectService($this->monitorRepository->getEntityManager()->getRepository('App\Entity\Project'), $this->logger);
        $project = $projectService->getProjectById($projectId);
        
        if (!$project) {
            $this->logger->warning('Project not found', ['id' => $projectId]);
            return null;
        }
        
        $monitor = new WebsiteMonitor();
        $monitor->setProject($project);
        $monitor->setLabel($data['label']);
        $monitor->setPeriodicity($data['periodicity']);
        $monitor->setBadgeLabel($data['badgeLabel'] ?? $data['label']);
        $monitor->setUrl($data['url']);
        $monitor->setCheckStatus($data['checkStatus'] ?? false);
        
        if (isset($data['keywords']) && is_array($data['keywords'])) {
            $monitor->setKeywords($data['keywords']);
        }
        
        $this->monitorRepository->save($monitor);
        
        return $monitor;
    }

    public function updateMonitor(int $id, array $data): ?Monitor
    {
        $this->logger->info('Updating monitor', ['id' => $id, 'data' => $data]);
        
        $monitor = $this->monitorRepository->findById($id);
        
        if (!$monitor) {
            $this->logger->warning('Monitor not found', ['id' => $id]);
            return null;
        }
        
        if (isset($data['label'])) {
            $monitor->setLabel($data['label']);
        }
        
        if (isset($data['periodicity'])) {
            $monitor->setPeriodicity($data['periodicity']);
        }
        
        if (isset($data['badgeLabel'])) {
            $monitor->setBadgeLabel($data['badgeLabel']);
        }
        
        if ($monitor instanceof PingMonitor) {
            if (isset($data['host'])) {
                $monitor->setHost($data['host']);
            }
            
            if (isset($data['port'])) {
                $monitor->setPort($data['port']);
            }
        } elseif ($monitor instanceof WebsiteMonitor) {
            if (isset($data['url'])) {
                $monitor->setUrl($data['url']);
            }
            
            if (isset($data['checkStatus'])) {
                $monitor->setCheckStatus($data['checkStatus']);
            }
            
            if (isset($data['keywords']) && is_array($data['keywords'])) {
                $monitor->setKeywords($data['keywords']);
            }
        }
        
        $monitor->setUpdatedAt(new \DateTime());
        $this->monitorRepository->save($monitor);
        
        return $monitor;
    }

    public function deleteMonitor(int $id): bool
    {
        $this->logger->info('Deleting monitor', ['id' => $id]);
        
        $monitor = $this->monitorRepository->findById($id);
        
        if (!$monitor) {
            $this->logger->warning('Monitor not found for deletion', ['id' => $id]);
            return false;
        }
        
        $this->monitorRepository->delete($monitor);
        
        return true;
    }

    public function checkMonitor(Monitor $monitor): MonitorStatus
    {
        $this->logger->info('Checking monitor', ['id' => $monitor->getId(), 'type' => get_class($monitor)]);
        
        $status = $monitor->check();
        
        if ($this->statusRepository) {
            $this->statusRepository->save($status);
        }
        
        return $status;
    }

    public function getMonitorStatus(int $monitorId, int $limit = 10, int $offset = 0): array
    {
        if (!$this->statusRepository) {
            $this->logger->warning('Status repository not available');
            return [];
        }
        
        return $this->statusRepository->findByMonitor($monitorId, $limit, $offset);
    }

    public function getLatestStatus(int $monitorId): ?MonitorStatus
    {
        if (!$this->statusRepository) {
            $this->logger->warning('Status repository not available');
            return null;
        }
        
        return $this->statusRepository->getLatestByMonitor($monitorId);
    }
} 