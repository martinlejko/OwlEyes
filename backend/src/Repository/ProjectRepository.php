<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProjectRepository
{
    private EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Project::class);
    }

    public function findAll(int $limit = 10, int $offset = 0, ?string $sortBy = null, ?string $sortOrder = 'ASC'): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('p');
        
        if ($sortBy) {
            $queryBuilder->orderBy('p.' . $sortBy, $sortOrder === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $queryBuilder->orderBy('p.createdAt', 'DESC');
        }
        
        $queryBuilder->setMaxResults($limit)
            ->setFirstResult($offset);
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function findById(int $id): ?Project
    {
        return $this->repository->find($id);
    }

    public function findByLabel(string $label): array
    {
        return $this->repository->findBy(['label' => $label]);
    }

    public function findByTags(array $tags): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('p');
        
        foreach ($tags as $index => $tag) {
            $queryBuilder->orWhere("JSONB_CONTAINS(p.tags, :tag{$index}) = true")
                ->setParameter("tag{$index}", json_encode($tag));
        }
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }

    public function delete(Project $project): void
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }

    public function count(): int
    {
        return $this->repository->count([]);
    }
} 