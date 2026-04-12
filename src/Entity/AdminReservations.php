<?php

namespace App\Entity;

use App\Repository\AdminReservationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminReservationsRepository::class)]
class AdminReservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Customer_Name = null;

    #[ORM\Column(length: 255)]
    private ?string $Contact_Number = null;

    #[ORM\Column]
    private ?\DateTime $Reservation_Date = null;

    #[ORM\Column]
    private ?\DateTime $Start_Time = null;

    #[ORM\Column]
    private ?\DateTime $End_Time = null;

    #[ORM\Column]
    private ?int $Guests = null;

    #[ORM\Column]
    private ?float $Total_Amount = null;

    #[ORM\Column(length: 255)]
    private ?string $Payment_Status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->Customer_Name;
    }

    public function setCustomerName(string $Customer_Name): static
    {
        $this->Customer_Name = $Customer_Name;

        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->Contact_Number;
    }

    public function setContactNumber(string $Contact_Number): static
    {
        $this->Contact_Number = $Contact_Number;

        return $this;
    }

    public function getReservationDate(): ?\DateTime
    {
        return $this->Reservation_Date;
    }

    public function setReservationDate(\DateTime $Reservation_Date): static
    {
        $this->Reservation_Date = $Reservation_Date;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->Start_Time;
    }

    public function setStartTime(\DateTime $Start_Time): static
    {
        $this->Start_Time = $Start_Time;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->End_Time;
    }

    public function setEndTime(\DateTime $End_Time): static
    {
        $this->End_Time = $End_Time;

        return $this;
    }

    public function getGuests(): ?int
    {
        return $this->Guests;
    }

    public function setGuests(int $Guests): static
    {
        $this->Guests = $Guests;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->Total_Amount;
    }

    public function setTotalAmount(float $Total_Amount): static
    {
        $this->Total_Amount = $Total_Amount;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->Payment_Status;
    }

    public function setPaymentStatus(string $Payment_Status): static
    {
        $this->Payment_Status = $Payment_Status;

        return $this;
    }
}
