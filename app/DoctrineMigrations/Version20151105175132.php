<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151105175132 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_passenger ON passenger');
        $this->addSql('ALTER TABLE passenger ADD f_name VARCHAR(255) DEFAULT NULL, ADD l_name VARCHAR(255) DEFAULT NULL, DROP first_name, DROP last_name');
        $this->addSql('CREATE UNIQUE INDEX unique_passenger ON passenger (f_name, l_name, dob, tour)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('DROP INDEX unique_passenger ON passenger');
        $this->addSql('ALTER TABLE passenger ADD first_name VARCHAR(255) DEFAULT NULL, ADD last_name VARCHAR(255) DEFAULT NULL, DROP f_name, DROP l_name');
        $this->addSql('CREATE UNIQUE INDEX unique_passenger ON passenger (first_name, last_name, dob, tour)');
    }
}
