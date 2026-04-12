<?php

namespace App\Entity;

use App\Repository\AdminRoomsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Rooms;

#[ORM\Entity(repositoryClass: AdminRoomsRepository::class)]
class AdminRooms
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Room_Name = null;

    #[ORM\Column]
    private ?int $Capacity = null;

    #[ORM\Column(length: 255)]
    private ?string $Status = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomName(): ?string
    {
        return $this->Room_Name;
    }

    public function setRoomName(string $Room_Name): static
    {
        $this->Room_Name = $Room_Name;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->Capacity;
    }

    public function setCapacity(int $Capacity): static
    {
        $this->Capacity = $Capacity;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(string $Status): static
    {
        $this->Status = $Status;
        return $this;
    }

    
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $user): static
    {
        $this->createdBy = $user;
        return $this;
    }
}
