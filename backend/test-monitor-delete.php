<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/config/container.php';
$settings = $config['settings'];

// Set up database connection
$metadata = Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
    $settings['doctrine']['metadata_dirs'],
    $settings['doctrine']['dev_mode'],
    null,
    null
);

$conn = Doctrine\DBAL\DriverManager::getConnection($settings['doctrine']['connection']);
$entityManager = new Doctrine\ORM\EntityManager($conn, $metadata);

// Create repository and service
$logger = new Monolog\Logger('test');
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/var/logs/test.log', Monolog\Logger::DEBUG));

$monitorRepository = new App\Repository\MonitorRepository($entityManager);
$monitorService = new App\Service\MonitorService($monitorRepository, $logger);

// Get monitor ID from command line
$monitorId = isset($argv[1]) ? (int)$argv[1] : 1;

echo "Attempting to delete monitor with ID: {$monitorId}\n";

// Delete the monitor
$result = $monitorService->deleteMonitor($monitorId);

if ($result) {
    echo "Monitor deleted successfully\n";
} else {
    echo "Failed to delete monitor\n";
} 