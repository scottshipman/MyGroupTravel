<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160105140106 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE dietary (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_52455173BEFE8DD (passenger), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emergency (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, emergencyName LONGTEXT DEFAULT NULL, emergencyRelationship LONGTEXT DEFAULT NULL, emergency_number VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', emergencyEmail LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_26DD111D3BEFE8DD (passenger), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medical (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, doctorName LONGTEXT DEFAULT NULL, doctor_number VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', conditions LONGTEXT DEFAULT NULL, medications LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_77DB075A3BEFE8DD (passenger), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE passport (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, passport_number LONGTEXT DEFAULT NULL, passport_first_name VARCHAR(255) DEFAULT NULL, passport_last_name VARCHAR(255) DEFAULT NULL, passport_nationality VARCHAR(255) DEFAULT NULL, date_of_issue DATE DEFAULT NULL, date_of_expiry DATE DEFAULT NULL, UNIQUE INDEX UNIQ_B5A26E083BEFE8DD (passenger), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dietary ADD CONSTRAINT FK_52455173BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE emergency ADD CONSTRAINT FK_26DD111D3BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE medical ADD CONSTRAINT FK_77DB075A3BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE passport ADD CONSTRAINT FK_B5A26E083BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE passenger ADD medical INT DEFAULT NULL, ADD dietary INT DEFAULT NULL, ADD passport INT DEFAULT NULL, ADD emergency INT DEFAULT NULL');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD77DB075A FOREIGN KEY (medical) REFERENCES medical (id)');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD5245517 FOREIGN KEY (dietary) REFERENCES dietary (id)');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DDB5A26E08 FOREIGN KEY (passport) REFERENCES passport (id)');
        $this->addSql('ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD26DD111D FOREIGN KEY (emergency) REFERENCES emergency (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD77DB075A ON passenger (medical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD5245517 ON passenger (dietary)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DDB5A26E08 ON passenger (passport)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BEFE8DD26DD111D ON passenger (emergency)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD5245517');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD26DD111D');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD77DB075A');
        $this->addSql('ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DDB5A26E08');
        $this->addSql('DROP TABLE dietary');
        $this->addSql('DROP TABLE emergency');
        $this->addSql('DROP TABLE medical');
        $this->addSql('DROP TABLE passport');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD77DB075A ON passenger');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD5245517 ON passenger');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DDB5A26E08 ON passenger');
        $this->addSql('DROP INDEX UNIQ_3BEFE8DD26DD111D ON passenger');
        $this->addSql('ALTER TABLE passenger DROP medical, DROP dietary, DROP passport, DROP emergency');
    }
}
