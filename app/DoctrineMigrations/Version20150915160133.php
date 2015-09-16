<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150915160133 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tour (id INT AUTO_INCREMENT NOT NULL, organizer INT DEFAULT NULL, institution INT DEFAULT NULL, currency INT DEFAULT NULL, name VARCHAR(255) NOT NULL, version INT NOT NULL, quoteNumber VARCHAR(255) DEFAULT NULL, deleted DATE DEFAULT NULL, locked TINYINT(1) NOT NULL, created DATE NOT NULL, ts DATETIME DEFAULT NULL, destination VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', expiryDate DATE DEFAULT NULL, freePlaces INT DEFAULT NULL, payingPlaces INT DEFAULT NULL, departureDate DATE DEFAULT NULL, totalPrice DOUBLE PRECISION DEFAULT NULL, welcomeMsg LONGTEXT DEFAULT NULL, duration LONGTEXT DEFAULT NULL, pricePerson DOUBLE PRECISION DEFAULT NULL, pricePersonPublic DOUBLE PRECISION DEFAULT NULL, returnDate DATE DEFAULT NULL, views INT NOT NULL, shareViews INT NOT NULL, salesAgent INT DEFAULT NULL, secondaryContact INT DEFAULT NULL, boardBasis INT DEFAULT NULL, headerBlock INT DEFAULT NULL, quoteReference INT DEFAULT NULL, quoteVersionReference INT DEFAULT NULL, tripStatus INT DEFAULT NULL, transportType INT DEFAULT NULL, INDEX IDX_6AD1F96999D47173 (organizer), INDEX IDX_6AD1F96947F82E57 (salesAgent), INDEX IDX_6AD1F969DE21469F (secondaryContact), INDEX IDX_6AD1F9693A9F98E5 (institution), INDEX IDX_6AD1F969CBBC630B (boardBasis), INDEX IDX_6AD1F969B27BB2B0 (headerBlock), INDEX IDX_6AD1F969E8A82641 (quoteReference), INDEX IDX_6AD1F969EA2FA139 (quoteVersionReference), INDEX IDX_6AD1F9692CDF0CF5 (tripStatus), INDEX IDX_6AD1F969BCACAD82 (transportType), INDEX IDX_6AD1F9696956883F (currency), UNIQUE INDEX unique_quoteNumber (quoteNumber), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F96999D47173 FOREIGN KEY (organizer) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F96947F82E57 FOREIGN KEY (salesAgent) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969DE21469F FOREIGN KEY (secondaryContact) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F9693A9F98E5 FOREIGN KEY (institution) REFERENCES institution (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969CBBC630B FOREIGN KEY (boardBasis) REFERENCES board_basis (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969B27BB2B0 FOREIGN KEY (headerBlock) REFERENCES content_block (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969E8A82641 FOREIGN KEY (quoteReference) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969EA2FA139 FOREIGN KEY (quoteVersionReference) REFERENCES quote_version (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F9692CDF0CF5 FOREIGN KEY (tripStatus) REFERENCES trip_status (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F969BCACAD82 FOREIGN KEY (transportType) REFERENCES transport (id)');
        $this->addSql('ALTER TABLE tour ADD CONSTRAINT FK_6AD1F9696956883F FOREIGN KEY (currency) REFERENCES currency (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tour');
    }
}
