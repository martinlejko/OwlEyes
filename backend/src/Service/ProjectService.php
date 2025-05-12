<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Psr\Log\LoggerInterface;

class ProjectService
{
    private ProjectRepository $projectRepository;
    private LoggerInterface $logger;

    public function __construct(
        ProjectRepository $projectRepository,
        LoggerInterface $logger
    ) {
        $this->projectRepository = $projectRepository;
        $this->logger = $logger;
    }

    public function getAllProjects(int $limit = 10, int $offset = 0, ?string $sortBy = null, ?string $sortOrder = null): array
    {
        $this->logger->info('Fetching all projects', [
            'limit' => $limit,
            'offset' => $offset,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
        
        return $this->projectRepository->findAll($limit, $offset, $sortBy, $sortOrder);
    }

    public function getProjectById(int $id): ?Project
    {
        $this->logger->info('Fetching project by ID', ['id' => $id]);
        
        return $this->projectRepository->findById($id);
    }

    public function createProject(array $data): Project
    {
        $this->logger->info('Creating new project', $data);
        
        $project = new Project();
        $project->setLabel($data['label']);
        
        if (isset($data['description'])) {
            $project->setDescription($data['description']);
        }
        
        if (isset($data['tags']) && is_array($data['tags'])) {
            $project->setTags($data['tags']);
        }
        
        $this->projectRepository->save($project);
        
        return $project;
    }

    public function updateProject(int $id, array $data): ?Project
    {
        $this->logger->info('Updating project', ['id' => $id, 'data' => $data]);
        
        $project = $this->projectRepository->findById($id);
        
        if (!$project) {
            $this->logger->warning('Project not found', ['id' => $id]);
            return null;
        }
        
        if (isset($data['label'])) {
            $project->setLabel($data['label']);
        }
        
        if (array_key_exists('description', $data)) {
            $project->setDescription($data['description']);
        }
        
        if (isset($data['tags']) && is_array($data['tags'])) {
            $project->setTags($data['tags']);
        }
        
        $project->setUpdatedAt(new \DateTime());
        $this->projectRepository->save($project);
        
        return $project;
    }

    public function deleteProject(int $id): bool
    {
        $this->logger->info('Deleting project', ['id' => $id]);
        
        $project = $this->projectRepository->findById($id);
        
        if (!$project) {
            $this->logger->warning('Project not found for deletion', ['id' => $id]);
            return false;
        }
        
        $this->projectRepository->delete($project);
        
        return true;
    }

    public function filterProjectsByTags(array $tags, int $limit = 10, int $offset = 0): array
    {
        $this->logger->info('Filtering projects by tags', ['tags' => $tags]);
        
        return $this->projectRepository->findByTags($tags);
    }

    public function getProjectCount(): int
    {
        return $this->projectRepository->count();
    }
} 