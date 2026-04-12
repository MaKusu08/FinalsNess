<?php

namespace App\Entity;

use App\Repository\AdminMoviesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminMoviesRepository::class)]
class AdminMovies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Movie_Name = null;

    #[ORM\Column(length: 255)]
    private ?string $Movie_Description = null;

    #[ORM\Column(length: 255)]
    private ?string $Movie_Image = null;

    #[ORM\Column(length: 255)]
    private ?string $Movie_Duration = null;

    #[ORM\Column]
    private ?float $Movie_Price = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovieName(): ?string
    {
        return $this->Movie_Name;
    }

    public function setMovieName(string $Movie_Name): static
    {
        $this->Movie_Name = $Movie_Name;

        return $this;
    }

    public function getMovieDescription(): ?string
    {
        return $this->Movie_Description;
    }

    public function setMovieDescription(string $Movie_Description): static
    {
        $this->Movie_Description = $Movie_Description;

        return $this;
    }

    public function getMovieImage(): ?string
    {
        return $this->Movie_Image;
    }

    public function setMovieImage(string $Movie_Image): static
    {
        $this->Movie_Image = $Movie_Image;

        return $this;
    }

    public function getMovieDuration(): ?string
    {
        return $this->Movie_Duration;
    }

    public function setMovieDuration(string $Movie_Duration): static
    {
        $this->Movie_Duration = $Movie_Duration;

        return $this;
    }

    public function getMoviePrice(): ?float
    {
        return $this->Movie_Price;
    }

    public function setMoviePrice(float $Movie_Price): static
    {
        $this->Movie_Price = $Movie_Price;

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
