<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151016150857 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tour_payment_task_passenger (tour_id INT NOT NULL, payment_task_id INT NOT NULL, INDEX IDX_F5E41BCB15ED8D43 (tour_id), INDEX IDX_F5E41BCB94FF0F6C (payment_task_id), PRIMARY KEY(tour_id, payment_task_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tour_payment_task_passenger ADD CONSTRAINT FK_F5E41BCB15ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_payment_task_passenger ADD CONSTRAINT FK_F5E41BCB94FF0F6C FOREIGN KEY (payment_task_id) REFERENCES payment_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour ADD cash_payment TINYINT(1) NOT NULL, ADD cash_payment_description LONGTEXT DEFAULT NULL, ADD bank_transfer_payment TINYINT(1) NOT NULL, ADD bank_transfer_payment_description LONGTEXT DEFAULT NULL, ADD online_payment TINYINT(1) NOT NULL, ADD online_payment_description LONGTEXT DEFAULT NULL, ADD other_payment TINYINT(1) NOT NULL, ADD other_payment_description LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tour_payment_task_passenger');
        $this->addSql('ALTER TABLE tour DROP cash_payment, DROP cash_payment_description, DROP bank_transfer_payment, DROP bank_transfer_payment_description, DROP online_payment, DROP online_payment_description, DROP other_payment, DROP other_payment_description');
    }
}
