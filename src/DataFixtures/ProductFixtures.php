<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Attribute;
use App\Entity\ProductIcon;
use App\Entity\ProductImage;
use App\Entity\ProductCategory;
use App\Entity\ProductAttribute;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

/**
 * Class ProductFixtures
 *
 * Testing data fixtures for product entities and relations (categories, attributes, images, icons)
 *
 * @package App\DataFixtures
 */
class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $categories = [];
        $attributes = [];

        // create testing categories
        for ($i = 1; $i <= 10; $i++) {
            $category = new Category();
            $category->setName($faker->word);
            $manager->persist($category);
            $categories[] = $category;
        }

        // create testing attributes
        $attributeNames = ['Color', 'Size', 'Material', 'Brand'];
        foreach ($attributeNames as $name) {
            $attribute = new Attribute();
            $attribute->setName($name);
            $manager->persist($attribute);
            $attributes[] = $attribute;
        }

        // create testing products
        for ($i = 1; $i <= 1000; $i++) {
            $product = new Product();
            $product->setName($faker->word);
            $product->setDescription($faker->sentence);
            $product->setAddedTime($faker->dateTimeThisYear);
            $product->setLastEditTime($faker->dateTimeThisMonth);
            $product->setPrice((string) $faker->randomFloat(2, 10, 1000));
            $product->setPriceCurrency('USD');
            $product->setActive(true);

            // assign categories
            foreach ($faker->randomElements($categories, 2) as $category) {
                $productCategory = new ProductCategory();
                $productCategory->setProduct($product);
                $productCategory->setCategory($category);
                $manager->persist($productCategory);
            }

            // assign attributes
            foreach ($attributes as $attribute) {
                $productAttribute = new ProductAttribute();
                $productAttribute->setAttribute($attribute);
                $productAttribute->setProduct($product);
                $productAttribute->setValue($faker->word);
                $productAttribute->setType('string');
                $manager->persist($productAttribute);
            }

            // assign icon
            $icon = new ProductIcon();
            $icon->setIconFile('/storage/icons/testing-icon.png');
            $icon->setProduct($product);
            $manager->persist($icon);

            // assign images
            foreach (['test-image-1.jpg', 'test-image-2.jpg', 'test-image-3.jpg'] as $imageFile) {
                $image = new ProductImage();
                $image->setImageFile("/storage/images/$imageFile");
                $image->setProduct($product);
                $manager->persist($image);
            }

            // persist product entity
            $manager->persist($product);
        }

        // flush products data to database
        $manager->flush();

        // put testing icon to storage
        $testingIcon = file_get_contents(__DIR__ . '/assets/icons/testing-icon.png');
        file_put_contents(__DIR__ . '/../../storage/icons/testing-icon.png', $testingIcon);

        // put testing images to storage
        $testingImages = [
            'test-image-1.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-1.jpg'),
            'test-image-2.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-2.jpg'),
            'test-image-3.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-3.jpg'),
        ];
        foreach ($testingImages as $imageFile => $imageData) {
            file_put_contents(__DIR__ . '/../../storage/images/' . $imageFile, $imageData);
        }
    }
}
