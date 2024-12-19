<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductAttributeRepository;

/**
 * Class ProductAttribute
 *
 * The ProductAttribute database table mapping entity
 *
 * @package App\Entity
 */
#[ORM\Table(name: 'product_attributes')]
#[ORM\Entity(repositoryClass: ProductAttributeRepository::class)]
class ProductAttribute
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Attribute::class, inversedBy: 'productAttributes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attribute $attribute = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productAttributes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

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

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }
}
