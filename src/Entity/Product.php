<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Product
 *
 * The Product database table mapping entity
 *
 * @package App\Entity
 */
#[ORM\Table(name: 'products')]
#[ORM\Index(name: 'products_name_idx', columns: ['name'])]
#[ORM\Index(name: 'products_price_idx', columns: ['price'])]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 255)]
    private ?string $price_currency = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $added_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $last_edit_time = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    /**
     * @var Collection<int, ProductCategory>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductCategory::class)]
    private Collection $product_categories;

    /**
     * @var Collection<int, ProductAttribute>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductAttribute::class)]
    private Collection $product_attributes;

    #[ORM\OneToOne(mappedBy: 'product', targetEntity: ProductIcon::class, cascade: ['persist', 'remove'])]
    private ?ProductIcon $icon = null;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImage::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->product_categories = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceCurrency(): ?string
    {
        return $this->price_currency;
    }

    public function setPriceCurrency(string $price_currency): static
    {
        $this->price_currency = $price_currency;

        return $this;
    }

    public function getAddedTime(): ?DateTimeInterface
    {
        return $this->added_time;
    }

    public function setAddedTime(DateTimeInterface $added_time): static
    {
        $this->added_time = $added_time;

        return $this;
    }

    public function getLastEditTime(): ?DateTimeInterface
    {
        return $this->last_edit_time;
    }

    public function setLastEditTime(DateTimeInterface $last_edit_time): static
    {
        $this->last_edit_time = $last_edit_time;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * @return Collection<int, Category|null>
     */
    public function getCategories(): Collection
    {
        return $this->product_categories->map(fn($pc) => $pc->getCategory());
    }

    /**
     * @return array<int, string|null>
     */
    public function getCategoriesRaw(): array
    {
        return $this->product_categories->filter(
            fn(ProductCategory $pc) => $pc->getCategory() !== null
        )->map(
            fn(ProductCategory $pc) => $pc->getCategory() ? $pc->getCategory()->getName() : null
        )->toArray();
    }

    /**
     * @return Collection<int, ProductAttribute>
     */
    public function getProductAttributes(): Collection
    {
        return $this->product_attributes;
    }

    /**
     * @return array<int, string|null>
     */
    public function getProductAttributesList(): array
    {
        return $this->product_attributes->filter(
            fn(ProductAttribute $pa) => $pa->getAttribute() !== null
        )->map(
            fn(ProductAttribute $pa) => $pa->getAttribute() ? $pa->getAttribute()->getName() : null
        )->toArray();
    }

    /**
     * @return array<int, string|null>
     */
    public function getProductAttributesRaw(): array
    {
        return $this->product_attributes->filter(
            fn(ProductAttribute $pa) => $pa->getAttribute() !== null
        )->map(
            fn(ProductAttribute $pa) => $pa->getAttribute() ? $pa->getAttribute()->getName() . ': ' . $pa->getValue() : null
        )->toArray();
    }

    public function getIcon(): ?ProductIcon
    {
        return $this->icon;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @return array<int, string|null>
     */
    public function getImagesRaw(): array
    {
        return $this->images->map(
            fn(ProductImage $pi) => $pi->getImageFile()
        )->toArray();
    }
}
