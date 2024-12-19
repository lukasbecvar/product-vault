<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Attribute
 *
 * The Attribute database table mapping entity
 *
 * @package App\Entity
 */
#[ORM\Table(name: 'attributes')]
#[ORM\Index(name: 'attributes_name_idx', columns: ['name'])]
#[ORM\Entity(repositoryClass: AttributeRepository::class)]
class Attribute
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ProductAttribute>
     */
    #[ORM\OneToMany(mappedBy: 'attribute', targetEntity: ProductAttribute::class)]
    private Collection $product_attributes;

    public function __construct()
    {
        $this->product_attributes = new ArrayCollection();
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
        return $this->product_attributes->map(fn($pa) => $pa->getProduct());
    }
}
