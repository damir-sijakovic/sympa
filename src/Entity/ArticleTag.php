<?php

namespace App\Entity;

use App\Repository\ArticleTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleTagRepository::class)]
class ArticleTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Update the ManyToOne relationship for Tag to include cascade operations
    #[ORM\ManyToOne(inversedBy: 'Article', cascade: ['remove'])]
    private ?Tag $tag = null;

    // Update the ManyToOne relationship for Article to include cascade operations
    #[ORM\ManyToOne(cascade: ['remove'])]
    private ?Article $article = null;

    #[ORM\Column(length: 32)]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }
    
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
       
    
}
