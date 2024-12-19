<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Category
 *
 * The Category database table mapping entity
 *
 * @package App\Entity
 */
#[ORM\Table(name: 'categories')]
#[ORM\Index(name: 'categories_name_idx', columns: ['name'])]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ProductCategory>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: ProductCategory::class)]
    private Collection $product_categories;

    public function __construct()
    {
        $this->product_categories = new ArrayCollection();
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

    /**
     * @return Collection<int, Product|null>
     */
    public function getProducts(): Collection
    {
        return $this->product_categories->map(fn($pc) => $pc->getProduct());
    }
}
