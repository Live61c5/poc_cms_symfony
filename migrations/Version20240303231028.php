<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303231028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B972283A269F2');
        $this->addSql('CREATE TABLE line (id INT AUTO_INCREMENT NOT NULL, section_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, ordre VARCHAR(255) DEFAULT NULL, INDEX IDX_D114B4F6D823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE line ADD CONSTRAINT FK_D114B4F6D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('ALTER TABLE `row` DROP FOREIGN KEY FK_8430F6DBD823E37A');
        $this->addSql('DROP TABLE `row`');
        $this->addSql('DROP INDEX IDX_831B972283A269F2 ON block');
        $this->addSql('ALTER TABLE block CHANGE row_id line_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B97224D7B7542 FOREIGN KEY (line_id) REFERENCES line (id)');
        $this->addSql('CREATE INDEX IDX_831B97224D7B7542 ON block (line_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B97224D7B7542');
        $this->addSql('CREATE TABLE `row` (id INT AUTO_INCREMENT NOT NULL, section_id INT DEFAULT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ordre VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_8430F6DBD823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `row` ADD CONSTRAINT FK_8430F6DBD823E37A FOREIGN KEY (section_id) REFERENCES section (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE line DROP FOREIGN KEY FK_D114B4F6D823E37A');
        $this->addSql('DROP TABLE line');
        $this->addSql('DROP INDEX IDX_831B97224D7B7542 ON block');
        $this->addSql('ALTER TABLE block CHANGE line_id row_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B972283A269F2 FOREIGN KEY (row_id) REFERENCES `row` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_831B972283A269F2 ON block (row_id)');
    }
}
