<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031190534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug LONGTEXT DEFAULT NULL, uuid VARCHAR(36) DEFAULT NULL, group_uuid VARCHAR(36) DEFAULT NULL, type VARCHAR(32) NOT NULL, sku LONGTEXT DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, quantity INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, metadata JSON DEFAULT NULL, excerpt VARCHAR(512) NOT NULL, author VARCHAR(255) NOT NULL, image LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, active TINYINT(1) NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, og_title VARCHAR(255) DEFAULT NULL, og_description VARCHAR(255) DEFAULT NULL, og_url VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_23A0E662B36786B (title), UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), UNIQUE INDEX UNIQ_23A0E66D17F50A6 (uuid), UNIQUE INDEX UNIQ_23A0E66F8250BD6 (group_uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE article_category (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(32) NOT NULL, category_id INT DEFAULT NULL, article_id INT DEFAULT NULL, INDEX IDX_53A4EDAA12469DE2 (category_id), INDEX IDX_53A4EDAA7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE article_tag (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(32) NOT NULL, tag_id INT DEFAULT NULL, article_id INT DEFAULT NULL, INDEX IDX_919694F9BAD26311 (tag_id), INDEX IDX_919694F97294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE attribute (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, slug LONGTEXT NOT NULL, `key` VARCHAR(256) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug LONGTEXT DEFAULT NULL, type VARCHAR(32) NOT NULL, parent INT NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, visible TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_64C19C15E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE shop_orders (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, address2 VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) NOT NULL, country VARCHAR(100) NOT NULL, state VARCHAR(100) NOT NULL, zip VARCHAR(10) NOT NULL, delivery_note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, product_list JSON NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug LONGTEXT DEFAULT NULL, type VARCHAR(32) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nickname VARCHAR(255) DEFAULT NULL, role VARCHAR(48) DEFAULT NULL, email VARCHAR(320) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, password VARCHAR(320) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649A188FE64 (nickname), UNIQUE INDEX UNIQ_8D93D64957698A6A (role), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F9BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F97294869C FOREIGN KEY (article_id) REFERENCES article (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA12469DE2');
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA7294869C');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY FK_919694F9BAD26311');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY FK_919694F97294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_category');
        $this->addSql('DROP TABLE article_tag');
        $this->addSql('DROP TABLE attribute');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE shop_orders');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
    }
}
