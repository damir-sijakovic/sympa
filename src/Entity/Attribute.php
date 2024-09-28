<?php

// src/Entity/Attribute.php
namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeRepository::class)]
#[ORM\Table(name: 'attribute')]
class Attribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $articleId;

    #[ORM\Column(type: 'string', length: 256)]
    private string $key;

    #[ORM\Column(type: 'text')]
    private string $value;

    // Getters and Setters
    public function getId(): ?int
    {
        return $id;
    }

    public function getArticleId(): int
    {
        return $articleId;
    }

    public function setArticleId(int $articleId): self
    {
        $this->articleId = $articleId;
        return $this;
    }

    public function getKey(): string
    {
        return $key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getValue(): string
    {
        return $value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }
}
