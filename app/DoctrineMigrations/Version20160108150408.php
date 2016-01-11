<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160108150408 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE passenger ADD media INT DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD6A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD6A2CA10C ON passenger (media)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD6A2CA10C');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD6A2CA10C ON passenger');
        $this->addSql('ALTER TABLE passenger DROP media');
    }
}
