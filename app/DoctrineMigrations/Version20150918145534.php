<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150918145534 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment_task (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value DOUBLE PRECISION NOT NULL, dueDate DATE NOT NULL, paidDate DATE DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tour_payment_task (tour_id INT NOT NULL, payment_task_id INT NOT NULL, INDEX IDX_71F0890915ED8D43 (tour_id), INDEX IDX_71F0890994FF0F6C (payment_task_id), PRIMARY KEY(tour_id, payment_task_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tour_payment_task ADD CONSTRAINT FK_71F0890915ED8D43 FOREIGN KEY (tour_id) REFERENCES tour (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour_payment_task ADD CONSTRAINT FK_71F0890994FF0F6C FOREIGN KEY (payment_task_id) REFERENCES payment_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tour DROP shareViews');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tour_payment_task DROP FOREIGN KEY FK_71F0890994FF0F6C');
        $this->addSql('DROP TABLE payment_task');
        $this->addSql('DROP TABLE tour_payment_task');
        $this->addSql('ALTER TABLE tour ADD shareViews INT NOT NULL');
    }
}
