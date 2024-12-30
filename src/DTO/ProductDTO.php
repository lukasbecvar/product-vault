<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductDTO
 *
 * Data transfer object for product entity
 *
 * @package App\DTO
 */
class ProductDTO
{
    #[Assert\NotBlank(message: "name: should not be blank.")]
    #[Assert\Length(
        min: 2,
        max: 80,
        minMessage: "name: should have at least {{ limit }} characters.",
        maxMessage: "name: should have at most {{ limit }} characters."
    )]
    public string $name;

    #[Assert\NotBlank(message: "description: should not be blank.")]
    #[Assert\Length(
        min: 2,
        max: 10240,
        minMessage: "description: should have at least {{ limit }} characters.",
        maxMessage: "description: should have at most {{ limit }} characters."
    )]
    public string $description;

    #[Assert\NotBlank(message: "price: should not be blank.")]
    #[Assert\Length(
        min: 1,
        max: 80,
        minMessage: "price: should have at least {{ limit }} characters.",
        maxMessage: "price: should have at most {{ limit }} characters."
    )]
    #[Assert\Type(type: 'numeric')]
    public string $price;

    #[Assert\Length(
        min: 1,
        max: 6,
        minMessage: "price-currency: should have at least {{ limit }} characters.",
        maxMessage: "price-currency: should have at most {{ limit }} characters."
    )]
    public string $priceCurrency;
}
