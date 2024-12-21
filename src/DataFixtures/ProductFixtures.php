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
 * Testing data fixtures for product entity
 *
 * @package App\DataFixtures
 */
class ProductFixtures extends Fixture
{
    /**
     * Load product fixtures
     *
     * @param ObjectManager $manager The entity manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $categories = [];
        $attributes = [];

        // testing categories
        $categoryNames = ['Electronics', 'Home Appliances', 'Fashion', 'Sports & Outdoors', 'Health & Beauty', 'Toys & Games', 'Books', 'Automotive', 'Groceries', 'Furniture'];

        // testing attributes
        $attributeNames = [
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White'],
            'Size' => [5, 10, 30, 55],
            'Material' => ['Cotton', 'Leather', 'Polyester', 'Silk'],
            'Brand' => ['Nike', 'Adidas', 'Puma', 'Reebok']
        ];

        // create categories
        for ($i = 1; $i <= 5; $i++) {
            $category = new Category();
            $category->setName($faker->randomElement($categoryNames));
            $manager->persist($category);
            $categories[] = $category;
        }

        // create attributes with sample values
        foreach ($attributeNames as $name => $values) {
            $attribute = new Attribute();
            $attribute->setName($name);
            $manager->persist($attribute);
            $attributes[$name] = $values;
        }

        // create products
        for ($i = 1; $i <= 1000; $i++) {
            $product = new Product();
            $product->setName($faker->word . ' ' . $faker->randomElement($attributes['Material']) . ' ' . $faker->randomElement($attributes['Color']));
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

            // assign attributes with random values
            foreach ($attributes as $attributeName => $values) {
                $productAttribute = new ProductAttribute();
                $productAttribute->setAttribute($attribute);
                $productAttribute->setProduct($product);
                $productAttribute->setValue($faker->randomElement($values));
                $productAttribute->setType(in_array($attributeName, ['Size', 'Price']) ? 'int' : 'string');
                $manager->persist($productAttribute);
            }

            // assign icon and images
            $icon = new ProductIcon();
            $icon->setIconFile('testing-icon.png');
            $icon->setProduct($product);
            $manager->persist($icon);

            foreach (['test-image-1.jpg', 'test-image-2.jpg', 'test-image-3.jpg'] as $imageFile) {
                $image = new ProductImage();
                $image->setImageFile($imageFile);
                $image->setProduct($product);
                $manager->persist($image);
            }

            // persist product entity
            $manager->persist($product);
        }

        // flush data to database
        $manager->flush();

        // prepare storage directories and files (icons, images)
        $this->prepareStorage();
    }

    /**
     * Prepare storage directories and files (icons, images)
     *
     * @return void
     */
    private function prepareStorage(): void
    {
        $basePath = __DIR__ . '/../../storage/' . $_ENV['APP_ENV'];
        $fileTypes = ['icons', 'images'];
        foreach ($fileTypes as $fileType) {
            $storagePath = $basePath . '/' . $fileType;
            if (!file_exists($storagePath)) {
                mkdir($storagePath, recursive: true);
            }
        }

        // put testing icon to storage
        $testingIcon = file_get_contents(__DIR__ . '/assets/icons/testing-icon.png');
        file_put_contents(__DIR__ . '/../../storage/' . $_ENV['APP_ENV'] . '/icons/testing-icon.png', $testingIcon);

        // put testing images to storage
        $testingImages = [
            'test-image-1.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-1.jpg'),
            'test-image-2.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-2.jpg'),
            'test-image-3.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-3.jpg'),
        ];
        foreach ($testingImages as $imageFile => $imageData) {
            file_put_contents(__DIR__ . '/../../storage/' . $_ENV['APP_ENV'] . '/images/' . $imageFile, $imageData);
        }
    }
}
