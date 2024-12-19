<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version20241219150034
 *
 * The database migration for create products management tables
 *
 * @package DoctrineMigrations
 */
final class Version20241219150034 extends AbstractMigration
{
    /**
     * Get migration description
     *
     * @return string The migration description
     */
    public function getDescription(): string
    {
        return 'Create products management tables';
    }

    /**
     * Excute migration
     *
     * @param Schema $schema The representation of a database schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE attributes (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, INDEX attributes_name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, INDEX categories_name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_attributes (id INT AUTO_INCREMENT NOT NULL, value VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, attribute_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_A2FCC15BB6E62EFA (attribute_id), INDEX IDX_A2FCC15B4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_categories (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_A99419434584665A (product_id), INDEX IDX_A994194312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_icons (id INT AUTO_INCREMENT NOT NULL, icon_file VARCHAR(255) NOT NULL, product_id INT NOT NULL, UNIQUE INDEX UNIQ_AB362B224584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_images (id INT AUTO_INCREMENT NOT NULL, image_file VARCHAR(255) NOT NULL, product_id INT NOT NULL, INDEX IDX_8263FFCE4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, added_time DATETIME NOT NULL, last_edit_time DATETIME NOT NULL, price NUMERIC(10, 2) NOT NULL, price_currency VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, INDEX products_name_idx (name), INDEX products_price_idx (price), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT FK_A2FCC15BB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id)');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT FK_A2FCC15B4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_categories ADD CONSTRAINT FK_A99419434584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_categories ADD CONSTRAINT FK_A994194312469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE product_icons ADD CONSTRAINT FK_AB362B224584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_images ADD CONSTRAINT FK_8263FFCE4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
    }

    /**
     * Excute undo migration
     *
     * @param Schema $schema The representation of a database schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_attributes DROP FOREIGN KEY FK_A2FCC15BB6E62EFA');
        $this->addSql('ALTER TABLE product_attributes DROP FOREIGN KEY FK_A2FCC15B4584665A');
        $this->addSql('ALTER TABLE product_categories DROP FOREIGN KEY FK_A99419434584665A');
        $this->addSql('ALTER TABLE product_categories DROP FOREIGN KEY FK_A994194312469DE2');
        $this->addSql('ALTER TABLE product_icons DROP FOREIGN KEY FK_AB362B224584665A');
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_8263FFCE4584665A');
        $this->addSql('DROP TABLE attributes');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE product_attributes');
        $this->addSql('DROP TABLE product_categories');
        $this->addSql('DROP TABLE product_icons');
        $this->addSql('DROP TABLE product_images');
        $this->addSql('DROP TABLE products');
    }
}
