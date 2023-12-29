<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ActivityType $activityType = null;

    
    #[ORM\ManyToMany(targetEntity: Monitor::class, inversedBy: 'activities')]
    private Collection $Monitors;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    
    public function __construct()
    {
        $this->Monitors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityType(): ?ActivityType
    {
        return $this->activityType;
    }

    public function setActivityType(?ActivityType $activityType): static
    {
        $this->activityType = $activityType;

        return $this;
    }

    /**
     * @return Collection<int, Monitor>
     */
    public function getMonitors(): Collection
    {
        return $this->Monitors;
    }

    public function addMonitor(Monitor $monitor): static
    {
        if (!$this->Monitors->contains($monitor)) {
            $this->Monitors->add($monitor);
        }

        return $this;
    }

    public function removeMonitor(Monitor $monitor): static
    {
        $this->Monitors->removeElement($monitor);

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
