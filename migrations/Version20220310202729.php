<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220310202729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, question VARCHAR(255) NOT NULL, answer VARCHAR(255) NOT NULL, INDEX IDX_E8FF75CC12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_posts (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, content LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, INDEX IDX_7F892528A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE faq ADD CONSTRAINT FK_E8FF75CC12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE profile_posts ADD CONSTRAINT FK_7F892528A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commande DROP status');
        $this->addSql('ALTER TABLE produit DROP updated_at');
        $this->addSql('ALTER TABLE reclamation ADD category_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640412469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_CE60640412469DE2 ON reclamation (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE faq DROP FOREIGN KEY FK_E8FF75CC12469DE2');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640412469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE profile_posts');
        $this->addSql('ALTER TABLE commande ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD updated_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_CE60640412469DE2 ON reclamation');
        $this->addSql('ALTER TABLE reclamation DROP category_id, CHANGE user_id user_id INT NOT NULL');
    }
}
