<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160204152735 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment_task_override (id INT AUTO_INCREMENT NOT NULL, passenger INT DEFAULT NULL, value DOUBLE PRECISION NOT NULL, paymentTaskSource INT DEFAULT NULL, INDEX IDX_3FC7E5643BEFE8DD (passenger), INDEX IDX_3FC7E5645452001C (paymentTaskSource), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment_task_override ADD CONSTRAINT FK_3FC7E5643BEFE8DD FOREIGN KEY (passenger) REFERENCES passenger (id)');
        $this->addSql('ALTER TABLE payment_task_override ADD CONSTRAINT FK_3FC7E5645452001C FOREIGN KEY (paymentTaskSource) REFERENCES payment_task (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment_task_override');
    }
}
