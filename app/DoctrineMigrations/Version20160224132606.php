<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160224132606 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_task_override');
        $this->addSql('ALTER TABLE quote_version DROP description');
        $this->addSql('ALTER TABLE tour ADD registrations INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, tour INT DEFAULT NULL, value DOUBLE PRECISION NOT NULL, date DATE NOT NULL, note LONGTEXT NOT NULL, INDEX IDX_6D28840D6AD1F969 (tour), INDEX IDX_6D28840D3BEFE8DD (passenger), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_task_override (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, value DOUBLE PRECISION NOT NULL, paymentTaskSource INT DEFAULT NULL, INDEX IDX_3FC7E5643BEFE8DD (passenger), INDEX IDX_3FC7E5645452001C (paymentTaskSource), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D3BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D6AD1F969 FOREIGN KEY (tour) REFERENCES tour (id)');
        $this->addSql('ALTER TABLE payment_task_override ADD CONSTRAINT FK_3FC7E5645452001C FOREIGN KEY (paymentTaskSource) REFERENCES payment_task (id)');
        $this->addSql('ALTER TABLE payment_task_override ADD CONSTRAINT FK_3FC7E5643BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE quote_version ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tour DROP registrations');
    }
}
