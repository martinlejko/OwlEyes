<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\MonitorStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class MonitorStatusRepository
{
    private EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(MonitorStatus::class);
    }

    public function findByMonitor(int $monitorId, int $limit = 10, int $offset = 0): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('s')
            ->where('s.monitor = :monitorId')
            ->setParameter('monitorId', $monitorId)
            ->orderBy('s.startTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function findByMonitorAndTimeRange(int $monitorId, \DateTime $from, \DateTime $to): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('s')
            ->where('s.monitor = :monitorId')
            ->andWhere('s.startTime >= :from')
            ->andWhere('s.startTime <= :to')
            ->setParameter('monitorId', $monitorId)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('s.startTime', 'ASC');
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function getStatusStatsByDay(int $monitorId, \DateTime $from, \DateTime $to): array
    {
        $conn = $this->entityManager->getConnection();
        $sql = '
            SELECT 
                DATE(start_time) as day,
                COUNT(*) as total,
                SUM(CASE WHEN status = true THEN 1 ELSE 0 END) as success
            FROM monitor_statuses
            WHERE monitor_id = :monitorId
            AND start_time >= :from
            AND start_time <= :to
            GROUP BY DATE(start_time)
            ORDER BY day ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('monitorId', $monitorId);
        $stmt->bindValue('from', $from->format('Y-m-d H:i:s'));
        $stmt->bindValue('to', $to->format('Y-m-d H:i:s'));
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function getLatestByMonitor(int $monitorId): ?MonitorStatus
    {
        $queryBuilder = $this->repository->createQueryBuilder('s')
            ->where('s.monitor = :monitorId')
            ->setParameter('monitorId', $monitorId)
            ->orderBy('s.startTime', 'DESC')
            ->setMaxResults(1);
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function save(MonitorStatus $status): void
    {
        $this->entityManager->persist($status);
        $this->entityManager->flush();
    }

    public function saveMany(array $statuses): void
    {
        foreach ($statuses as $status) {
            $this->entityManager->persist($status);
        }
        $this->entityManager->flush();
    }
} 