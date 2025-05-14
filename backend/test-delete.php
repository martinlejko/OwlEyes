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

$projectRepository = new App\Repository\ProjectRepository($entityManager);
$projectService = new App\Service\ProjectService($projectRepository, $logger);

// Get project ID from command line
$projectId = isset($argv[1]) ? (int)$argv[1] : 1;

echo "Attempting to delete project with ID: {$projectId}\n";

// Delete the project
$result = $projectService->deleteProject($projectId);

if ($result) {
    echo "Project deleted successfully\n";
} else {
    echo "Failed to delete project\n";
} 