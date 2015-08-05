<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150805164456 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quote_version ADD duration LONGTEXT DEFAULT NULL, DROP maxPax, DROP minPax, DROP signupDeadline');
        $this->addSql('ALTER TABLE content_block ADD media INT DEFAULT NULL');
        $this->addSql('ALTER TABLE content_block ADD CONSTRAINT FK_68D8C3F06A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('CREATE INDEX IDX_68D8C3F06A2CA10C ON content_block (media)');
        $this->addSql('ALTER TABLE institution DROP localAuthority, DROP code, DROP type, DROP websiteAddress, CHANGE address2 address2 VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE content_block DROP FOREIGN KEY FK_68D8C3F06A2CA10C');
        $this->addSql('DROP INDEX IDX_68D8C3F06A2CA10C ON content_block');
        $this->addSql('ALTER TABLE content_block DROP media');
        $this->addSql('ALTER TABLE institution ADD localAuthority VARCHAR(255) DEFAULT NULL, ADD code INT DEFAULT NULL, ADD type VARCHAR(255) DEFAULT NULL, ADD websiteAddress VARCHAR(255) DEFAULT NULL, CHANGE address2 address2 VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE quote_version ADD maxPax INT DEFAULT NULL, ADD minPax INT DEFAULT NULL, ADD signupDeadline DATE DEFAULT NULL, DROP duration');
    }
}
