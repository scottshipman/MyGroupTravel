<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151102110404 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE passenger (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, tour INT DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, gender VARCHAR(255) NOT NULL, dob DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_3BEFE8DD8D93D649 (user), INDEX IDX_3BEFE8DD6AD1F969 (tour), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD6AD1F969 FOREIGN KEY (tour) REFERENCES tour (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE passenger');
    }
}
