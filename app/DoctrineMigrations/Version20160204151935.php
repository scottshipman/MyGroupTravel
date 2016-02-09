<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160204151935 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, tour INT DEFAULT NULL, passenger INT DEFAULT NULL, value DOUBLE PRECISION NOT NULL, date DATE NOT NULL, note LONGTEXT NOT NULL, INDEX IDX_6D28840D6AD1F969 (tour), INDEX IDX_6D28840D3BEFE8DD (passenger), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D6AD1F969 FOREIGN KEY (tour) REFERENCES tour (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D3BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE emergency CHANGE emergency_number emergency_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE medical CHANGE doctor_number doctor_number VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment');
        $this->addSql('ALTER TABLE emergency CHANGE emergency_number emergency_number VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\'');
        $this->addSql('ALTER TABLE medical CHANGE doctor_number doctor_number VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\'');
    }
}
