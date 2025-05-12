<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'monitors')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['ping' => PingMonitor::class, 'website' => WebsiteMonitor::class])]
abstract class Monitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(type: 'integer')]
    private int $periodicity;

    #[ORM\Column(type: 'string', length: 255)]
    private string $badgeLabel;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'monitors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\OneToMany(mappedBy: 'monitor', targetEntity: MonitorStatus::class, cascade: ['persist', 'remove'])]
    private Collection $statuses;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->statuses = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getPeriodicity(): int
    {
        return $this->periodicity;
    }

    public function setPeriodicity(int $periodicity): self
    {
        // Validate periodicity between 5 and 300 seconds
        if ($periodicity < 5 || $periodicity > 300) {
            throw new \InvalidArgumentException('Periodicity must be between 5 and 300 seconds.');
        }
        
        $this->periodicity = $periodicity;
        return $this;
    }

    public function getBadgeLabel(): string
    {
        return $this->badgeLabel;
    }

    public function setBadgeLabel(string $badgeLabel): self
    {
        $this->badgeLabel = $badgeLabel;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(MonitorStatus $status): self
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setMonitor($this);
        }
        return $this;
    }

    public function removeStatus(MonitorStatus $status): self
    {
        if ($this->statuses->removeElement($status)) {
            if ($status->getMonitor() === $this) {
                $status->setMonitor(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    abstract public function check(): MonitorStatus;
} 