<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: "text", nullable: true)]
	private ?string $slug = null;

    #[ORM\Column]
    private ?int $parent = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

	#[ORM\Column]
    private ?bool $visible = null;
	

    /**
     * @var Collection<int, ArticleCategory>
     */
    #[ORM\OneToMany(targetEntity: ArticleCategory::class, mappedBy: 'category')]
    private Collection $Article;

    public function __construct()
    {
        $this->Article = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(int $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, ArticleCategory>
     */
    public function getArticle(): Collection
    {
        return $this->Article;
    }

    public function addArticle(ArticleCategory $article): static
    {
        if (!$this->Article->contains($article)) {
            $this->Article->add($article);
            $article->setCategory($this);
        }

        return $this;
    }

    public function removeArticle(ArticleCategory $article): static
    {
        if ($this->Article->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getCategory() === $this) {
                $article->setCategory(null);
            }
        }

        return $this;
    }
    
    
    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }
    
    public function getVisible(): bool
    {
        return $this->visible;
    }
    
}
