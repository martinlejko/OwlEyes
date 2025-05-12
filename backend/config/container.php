<?php
declare(strict_types=1);

use App\Service\MonitorService;
use App\Service\ProjectService;
use App\Repository\MonitorRepository;
use App\Repository\ProjectRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    // Configuration
    'settings' => [
        'displayErrorDetails' => (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'dev'),
        'logError' => true,
        'logErrorDetails' => true,
        'doctrine' => [
            'dev_mode' => (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'dev'),
            'cache_dir' => __DIR__ . '/../var/cache/doctrine',
            'metadata_dirs' => [__DIR__ . '/../src/Entity'],
            'connection' => [
                'driver' => 'pdo_pgsql',
                'host' => $_ENV['DB_HOST'] ?? 'db',
                'port' => $_ENV['DB_PORT'] ?? 5432,
                'dbname' => $_ENV['DB_NAME'] ?? 'owleyes',
                'user' => $_ENV['DB_USER'] ?? 'owleyes_user',
                'password' => $_ENV['DB_PASSWORD'] ?? 'owleyes_password',
                'charset' => 'utf8'
            ],
        ],
    ],

    // Logger
    LoggerInterface::class => function (ContainerInterface $container) {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/logs/app.log', Logger::DEBUG));
        $logger->pushProcessor(new WebProcessor());
        return $logger;
    },

    // Database connection
    EntityManagerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $config = ORMSetup::createAttributeMetadataConfiguration(
            $settings['doctrine']['metadata_dirs'],
            $settings['doctrine']['dev_mode'],
            null,
            null
        );

        $connection = DriverManager::getConnection($settings['doctrine']['connection']);
        return new EntityManager($connection, $config);
    },

    // Repositories
    ProjectRepository::class => function (ContainerInterface $container) {
        return $container->get(EntityManagerInterface::class)->getRepository(App\Entity\Project::class);
    },

    MonitorRepository::class => function (ContainerInterface $container) {
        return $container->get(EntityManagerInterface::class)->getRepository(App\Entity\Monitor::class);
    },

    // Services
    ProjectService::class => function (ContainerInterface $container) {
        return new ProjectService(
            $container->get(ProjectRepository::class),
            $container->get(LoggerInterface::class)
        );
    },

    MonitorService::class => function (ContainerInterface $container) {
        return new MonitorService(
            $container->get(MonitorRepository::class),
            $container->get(LoggerInterface::class)
        );
    },
]; 