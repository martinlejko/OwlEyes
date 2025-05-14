<?php
declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;

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

return ConsoleRunner::createHelperSet($entityManager); 