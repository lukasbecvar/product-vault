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
     * Load data fixtures to the database
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

        // realistic categories
        $categoryNames = [
            'Electronics',
            'Home Appliances',
            'Clothing',
            'Sports Equipment',
            'Beauty Products',
            'Toys',
            'Books & Stationery',
            'Automotive Accessories',
            'Food & Beverages',
            'Furniture'
        ];

        // realistic attributes
        $attributeDefinitions = [
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Pink'],
            'Size' => ['S', 'M', 'L', 'XL', 'XXL'],
            'Material' => ['Cotton', 'Leather', 'Wood', 'Metal', 'Plastic'],
            'Brand' => ['Samsung', 'Apple', 'Sony', 'Adidas', 'Nike'],
            'Power' => ['500W', '1000W', '1500W', '2000W'],
            'Capacity' => ['1L', '5L', '10L'],
            'Weight' => ['500g', '1kg', '2kg']
        ];

        // create categories
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        // create attributes
        foreach ($attributeDefinitions as $name => $values) {
            $attribute = new Attribute();
            $attribute->setName($name);
            $manager->persist($attribute);
            $attributes[$name] = $attribute;
        }

        // create products
        for ($i = 1; $i <= 1000; $i++) {
            $product = new Product();
            $productName = $faker->randomElement(['Smartphone', 'Headphones', 'Running Shoes', 'Coffee Maker', 'Backpack']) . ' - ' . $faker->word;
            $productDescription = $faker->sentence(10);
            $product->setName($productName);
            $product->setDescription($productDescription);
            $product->setAddedTime($faker->dateTimeThisYear);
            $product->setLastEditTime($faker->dateTimeThisMonth);
            $product->setPrice((string)$faker->randomFloat(2, 10, 1000));
            $product->setPriceCurrency('USD');
            $product->setActive(true);

            // assign random categories
            foreach ($faker->randomElements($categories, mt_rand(1, 3)) as $category) {
                $productCategory = new ProductCategory();
                $productCategory->setProduct($product);
                $productCategory->setCategory($category);
                $manager->persist($productCategory);
            }

            // assign random attributes with specific values
            foreach ($faker->randomElements(array_keys($attributeDefinitions), mt_rand(2, 4)) as $attributeName) {
                $productAttribute = new ProductAttribute();
                $productAttribute->setAttribute($attributes[$attributeName]);
                $productAttribute->setProduct($product);
                $productAttribute->setValue($faker->randomElement($attributeDefinitions[$attributeName]));
                $productAttribute->setType(is_numeric($faker->randomElement($attributeDefinitions[$attributeName])) ? 'int' : 'string');
                $manager->persist($productAttribute);
            }

            // assign icon and images
            $icon = new ProductIcon();
            $icon->setIconFile('icon-' . $i . '.png');
            $icon->setProduct($product);
            $manager->persist($icon);

            foreach (['image1.jpg', 'image2.jpg', 'image3.jpg'] as $imageFile) {
                $image = new ProductImage();
                $image->setImageFile($imageFile);
                $image->setProduct($product);
                $manager->persist($image);
            }

            // persist product entity
            $manager->persist($product);
        }

        // flush data to the database
        $manager->flush();

        // prepare storage directories and files
        $this->prepareStorage();
    }

    /**
     * Prepare storage directories and files
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

        // prepare testing icons
        $testingIcon = file_get_contents(__DIR__ . '/assets/icons/testing-icon.png');
        file_put_contents($basePath . '/icons/testing-icon.png', $testingIcon);

        // prepare testing images
        $testingImages = [
            'test-image-1.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-1.jpg'),
            'test-image-2.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-2.jpg'),
            'test-image-3.jpg' => file_get_contents(__DIR__ . '/assets/images/test-image-3.jpg'),
        ];
        foreach ($testingImages as $imageFile => $imageData) {
            file_put_contents($basePath . '/images/' . $imageFile, $imageData);
        }
    }
}
