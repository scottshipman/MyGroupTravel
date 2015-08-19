<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150819152423 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quote_version ADD headerBlock INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quote_version ADD CONSTRAINT FK_50F37B47B27BB2B0 FOREIGN KEY (headerBlock) REFERENCES content_block (id)');
        $this->addSql('CREATE INDEX IDX_50F37B47B27BB2B0 ON quote_version (headerBlock)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quote_version DROP FOREIGN KEY FK_50F37B47B27BB2B0');
        $this->addSql('DROP INDEX IDX_50F37B47B27BB2B0 ON quote_version');
        $this->addSql('ALTER TABLE quote_version DROP headerBlock');
    }
}
