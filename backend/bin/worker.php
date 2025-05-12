<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Entity\Monitor;
use App\Repository\MonitorRepository;
use App\Repository\MonitorStatusRepository;
use App\Service\MonitorService;
use Doctrine\ORM\EntityManagerInterface;
use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Load container
$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

// Get logger
$logger = $container->get(LoggerInterface::class);
$logger->info('Worker started');

// Get dependencies
$entityManager = $container->get(EntityManagerInterface::class);
$monitorRepository = new MonitorRepository($entityManager);
$statusRepository = new MonitorStatusRepository($entityManager);
$monitorService = new MonitorService($monitorRepository, $logger, $statusRepository);

// Main worker loop
try {
    while (true) {
        // Refresh entity manager to prevent stale data
        $entityManager->clear();
        
        // Get all monitors
        $monitors = $monitorRepository->findAll(1000, 0);
        $logger->info('Processing {count} monitors', ['count' => count($monitors)]);
        
        $now = time();
        $monitorsToCheck = [];
        
        // Find monitors that need to be checked based on their periodicity
        foreach ($monitors as $monitor) {
            $latestStatus = $statusRepository->getLatestByMonitor($monitor->getId());
            
            if (!$latestStatus || ($now - $latestStatus->getStartTime()->getTimestamp() >= $monitor->getPeriodicity())) {
                $monitorsToCheck[] = $monitor;
            }
        }
        
        $logger->info('Checking {count} monitors', ['count' => count($monitorsToCheck)]);
        
        // Check the monitors
        foreach ($monitorsToCheck as $monitor) {
            try {
                $status = $monitorService->checkMonitor($monitor);
                $logger->info('Monitor {id} check completed with status {status}', [
                    'id' => $monitor->getId(),
                    'status' => $status->getStatus() ? 'success' : 'failure',
                    'responseTime' => $status->getResponseTime()
                ]);
            } catch (\Exception $e) {
                $logger->error('Error checking monitor {id}: {message}', [
                    'id' => $monitor->getId(),
                    'message' => $e->getMessage(),
                    'exception' => $e
                ]);
            }
        }
        
        // Sleep for a short time before the next iteration
        sleep(5);
    }
} catch (\Exception $e) {
    $logger->critical('Worker crashed: {message}', [
        'message' => $e->getMessage(),
        'exception' => $e
    ]);
    
    exit(1);
} 