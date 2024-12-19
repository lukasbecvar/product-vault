<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductIconRepository;

/**
 * Class ProductIcon
 *
 * The ProductIcon database table mapping entity
 *
 * @package App\Entity
 */
#[ORM\Table(name: 'product_icons')]
#[ORM\Entity(repositoryClass: ProductIconRepository::class)]
class ProductIcon
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $icon_file = null;

    #[ORM\OneToOne(inversedBy: 'icon', targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIconFile(): ?string
    {
        return $this->icon_file;
    }

    public function setIconFile(string $icon_file): static
    {
        $this->icon_file = $icon_file;

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
