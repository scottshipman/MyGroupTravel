<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151116115448 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE brand ADD mediaEmail INT DEFAULT NULL');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958B831FCB8 FOREIGN KEY (mediaEmail) REFERENCES media (id)');
        $this->addSql('CREATE INDEX IDX_1C52F958B831FCB8 ON brand (mediaEmail)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F958B831FCB8');
        $this->addSql('DROP INDEX IDX_1C52F958B831FCB8 ON brand');
        $this->addSql('ALTER TABLE brand DROP mediaEmail');
    }
}
