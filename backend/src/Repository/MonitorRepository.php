<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Monitor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class MonitorRepository
{
    private EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Monitor::class);
    }

    public function findAll(int $limit = 10, int $offset = 0): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function findById(int $id): ?Monitor
    {
        return $this->repository->find($id);
    }

    public function findByProject(int $projectId, int $limit = 10, int $offset = 0): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('m')
            ->where('m.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function findByType(string $type, int $limit = 10, int $offset = 0): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('m')
            ->where('m INSTANCE OF :type')
            ->setParameter('type', "App\\Entity\\{$type}Monitor")
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function findByLabel(string $label): array
    {
        return $this->repository->findBy(['label' => $label]);
    }

    public function findByStatus(bool $status, int $limit = 10, int $offset = 0): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('m')
            ->join('m.statuses', 's')
            ->where('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.startTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Monitor $monitor): void
    {
        $this->entityManager->persist($monitor);
        $this->entityManager->flush();
    }

    public function delete(Monitor $monitor): void
    {
        $this->entityManager->remove($monitor);
        $this->entityManager->flush();
    }

    public function count(): int
    {
        return $this->repository->count([]);
    }
} 