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
    #[Assert\NotBlank(message: "value should not be blank.")]
    #[Assert\Length(
        min: 2,
        max: 80,
        minMessage: "value should have at least {{ limit }} characters.",
        maxMessage: "value should have at most {{ limit }} characters."
    )]
    public string $name;

    #[Assert\NotBlank(message: "value should not be blank.")]
    #[Assert\Length(
        min: 2,
        max: 10240,
        minMessage: "value should have at least {{ limit }} characters.",
        maxMessage: "value should have at most {{ limit }} characters."
    )]
    public string $description;

    #[Assert\NotBlank(message: "value should not be blank.")]
    #[Assert\Length(
        min: 1,
        max: 80,
        minMessage: "value should have at least {{ limit }} characters.",
        maxMessage: "value should have at most {{ limit }} characters."
    )]
    public string $price;

    #[Assert\Length(
        min: 1,
        max: 6,
        minMessage: "value should have at least {{ limit }} characters.",
        maxMessage: "value should have at most {{ limit }} characters."
    )]
    public string $priceCurrency;
}
