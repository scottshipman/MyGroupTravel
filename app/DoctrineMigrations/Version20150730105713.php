<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150730105713 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quote ADD secondaryContact INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4DE21469F FOREIGN KEY (secondaryContact) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_6B71CBF4DE21469F ON quote (secondaryContact)');
        $this->addSql('ALTER TABLE institution CHANGE address2 address2 VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF4DE21469F');
        $this->addSql('DROP INDEX IDX_6B71CBF4DE21469F ON quote');
        $this->addSql('ALTER TABLE quote DROP secondaryContact');
        $this->addSql('ALTER TABLE institution CHANGE address2 address2 VARCHAR(255) NOT NULL');
    }
}
