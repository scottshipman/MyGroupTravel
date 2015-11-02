<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151102133121 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD8D93D649');
        $this->addSql('DROP INDEX IDX_3BEFE8DD8D93D649 ON passenger');
        $this->addSql('ALTER TABLE passenger DROP user');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE passenger ADD user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_3BEFE8DD8D93D649 ON passenger (user)');
    }
}
