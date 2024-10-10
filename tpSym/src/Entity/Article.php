<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 5,
        max: 50,
        minMessage: "Le nom d'un article doit comporter au moins {{ limit }} caractères",
        maxMessage: "Le nom d'un article doit comporter au plus {{ limit }} caractères"
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotEqualTo(
        value: 0,
        message: "Le prix d’un article ne doit pas être égal à 0"
    )]
    private ?string $prix = null; // Consider keeping prix as string to handle precision properly

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrix(): ?string // Keep return type as string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static // Keep parameter type as string
    {
        $this->prix = $prix;
        return $this;
    }

    public function getCategory(): ?Category // Corrected method name to match naming conventions
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static // Corrected method name to match naming conventions
    {
        $this->category = $category;
        return $this;
    }
}
