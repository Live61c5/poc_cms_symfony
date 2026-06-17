<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303175929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE block (id INT AUTO_INCREMENT NOT NULL, row_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, ordre VARCHAR(255) DEFAULT NULL, INDEX IDX_831B972283A269F2 (row_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, block_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, text LONGTEXT DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, created DATE DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_FEC530A9E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `row` (id INT AUTO_INCREMENT NOT NULL, section_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, INDEX IDX_8430F6DBD823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE section (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_2D737AEFC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B972283A269F2 FOREIGN KEY (row_id) REFERENCES `row` (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9E9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE `row` ADD CONSTRAINT FK_8430F6DBD823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEFC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B972283A269F2');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9E9ED820C');
        $this->addSql('ALTER TABLE `row` DROP FOREIGN KEY FK_8430F6DBD823E37A');
        $this->addSql('ALTER TABLE section DROP FOREIGN KEY FK_2D737AEFC4663E4');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE `row`');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
