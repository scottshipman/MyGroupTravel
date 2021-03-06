<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150818120225 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, organizer INT DEFAULT NULL, institution INT DEFAULT NULL, media INT DEFAULT NULL, name VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, setupComplete TINYINT(1) NOT NULL, destination VARCHAR(255) NOT NULL, salesAgent INT DEFAULT NULL, secondaryContact INT DEFAULT NULL, INDEX IDX_6B71CBF499D47173 (organizer), INDEX IDX_6B71CBF447F82E57 (salesAgent), INDEX IDX_6B71CBF4DE21469F (secondaryContact), INDEX IDX_6B71CBF43A9F98E5 (institution), INDEX IDX_6B71CBF46A2CA10C (media), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quote_version (id INT AUTO_INCREMENT NOT NULL, currency INT DEFAULT NULL, name VARCHAR(255) NOT NULL, version INT NOT NULL, quoteNumber VARCHAR(255) DEFAULT NULL, converted TINYINT(1) NOT NULL, deleted DATE DEFAULT NULL, locked TINYINT(1) NOT NULL, isTemplate TINYINT(1) NOT NULL, created DATE NOT NULL, ts DATETIME DEFAULT NULL, content LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', expiryDate DATE DEFAULT NULL, freePlaces INT DEFAULT NULL, payingPlaces INT DEFAULT NULL, departureDate DATE DEFAULT NULL, quoteDays INT DEFAULT NULL, quoteNights INT DEFAULT NULL, totalPrice DOUBLE PRECISION DEFAULT NULL, welcomeMsg LONGTEXT DEFAULT NULL, duration LONGTEXT DEFAULT NULL, pricePerson DOUBLE PRECISION DEFAULT NULL, returnDate DATE DEFAULT NULL, views INT NOT NULL, shareViews INT NOT NULL, boardBasis INT DEFAULT NULL, quoteReference INT DEFAULT NULL, tripStatus INT DEFAULT NULL, transportType INT DEFAULT NULL, INDEX IDX_50F37B47CBBC630B (boardBasis), INDEX IDX_50F37B47E8A82641 (quoteReference), INDEX IDX_50F37B472CDF0CF5 (tripStatus), INDEX IDX_50F37B47BCACAD82 (transportType), INDEX IDX_50F37B476956883F (currency), UNIQUE INDEX unique_quoteNumber (quoteNumber), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, media INT DEFAULT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, created DATE NOT NULL, deleted DATE DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, honorific VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A64796A2CA10C (media), UNIQUE INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, media INT DEFAULT NULL, name VARCHAR(100) NOT NULL, division VARCHAR(100) NOT NULL, primaryColor VARCHAR(32) NOT NULL, buttonColor VARCHAR(32) NOT NULL, hoverColor VARCHAR(32) NOT NULL, INDEX IDX_1C52F9586A2CA10C (media), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE board_basis (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transport (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trip_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, visible TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content_block (id INT AUTO_INCREMENT NOT NULL, layouttype INT DEFAULT NULL, locked TINYINT(1) NOT NULL, hidden TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, sortOrder INT DEFAULT NULL, doubleWidth TINYINT(1) NOT NULL, INDEX IDX_68D8C3F0578FFD90 (layouttype), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content_block_media (content_block_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_FE17CD6CBB5A68E3 (content_block_id), INDEX IDX_FE17CD6CEA9FDD75 (media_id), PRIMARY KEY(content_block_id, media_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE layout_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, className VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE institution (id INT AUTO_INCREMENT NOT NULL, media INT DEFAULT NULL, deleted DATE DEFAULT NULL, name VARCHAR(255) NOT NULL, address1 VARCHAR(255) DEFAULT NULL, address2 VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, county VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, post_code VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, INDEX IDX_3A9F98E56A2CA10C (media), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) NOT NULL, htmlSymbol VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, context VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, hashed_filename VARCHAR(255) DEFAULT NULL, filepath VARCHAR(255) DEFAULT NULL, relativepath VARCHAR(255) DEFAULT NULL, mimetype VARCHAR(255) DEFAULT NULL, filesize VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, class VARCHAR(255) NOT NULL, object INT NOT NULL, grants VARCHAR(255) NOT NULL, INDEX IDX_E04992AA8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF499D47173 FOREIGN KEY (organizer) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF447F82E57 FOREIGN KEY (salesAgent) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4DE21469F FOREIGN KEY (secondaryContact) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF43A9F98E5 FOREIGN KEY (institution) REFERENCES institution (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF46A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('ALTER TABLE quote_version ADD CONSTRAINT FK_50F37B47CBBC630B FOREIGN KEY (boardBasis) REFERENCES board_basis (id)');
        $this->addSql('ALTER TABLE quote_version ADD CONSTRAINT FK_50F37B47E8A82641 FOREIGN KEY (quoteReference) REFERENCES quote (id)');
        $this->addSql('ALTER TABLE quote_version ADD CONSTRAINT FK_50F37B472CDF0CF5 FOREIGN KEY (tripStatus) REFERENCES trip_status (id)');
        $this->addSql('ALTER TABLE quote_version ADD CONSTRAINT FK_50F37B47BCACAD82 FOREIGN KEY (transportType) REFERENCES transport (id)');
        $this->addSql('ALTER TABLE quote_version ADD CONSTRAINT FK_50F37B476956883F FOREIGN KEY (currency) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64796A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F9586A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('ALTER TABLE content_block ADD CONSTRAINT FK_68D8C3F0578FFD90 FOREIGN KEY (layouttype) REFERENCES layout_type (id)');
        $this->addSql('ALTER TABLE content_block_media ADD CONSTRAINT FK_FE17CD6CBB5A68E3 FOREIGN KEY (content_block_id) REFERENCES content_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_block_media ADD CONSTRAINT FK_FE17CD6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE institution ADD CONSTRAINT FK_3A9F98E56A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AA8D93D649 FOREIGN KEY (user) REFERENCES fos_user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE quote_version DROP FOREIGN KEY FK_50F37B47E8A82641');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF499D47173');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF447F82E57');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF4DE21469F');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AA8D93D649');
        $this->addSql('ALTER TABLE quote_version DROP FOREIGN KEY FK_50F37B47CBBC630B');
        $this->addSql('ALTER TABLE quote_version DROP FOREIGN KEY FK_50F37B47BCACAD82');
        $this->addSql('ALTER TABLE quote_version DROP FOREIGN KEY FK_50F37B472CDF0CF5');
        $this->addSql('ALTER TABLE content_block_media DROP FOREIGN KEY FK_FE17CD6CBB5A68E3');
        $this->addSql('ALTER TABLE content_block DROP FOREIGN KEY FK_68D8C3F0578FFD90');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF43A9F98E5');
        $this->addSql('ALTER TABLE quote_version DROP FOREIGN KEY FK_50F37B476956883F');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF46A2CA10C');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A64796A2CA10C');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F9586A2CA10C');
        $this->addSql('ALTER TABLE content_block_media DROP FOREIGN KEY FK_FE17CD6CEA9FDD75');
        $this->addSql('ALTER TABLE institution DROP FOREIGN KEY FK_3A9F98E56A2CA10C');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE quote_version');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE board_basis');
        $this->addSql('DROP TABLE transport');
        $this->addSql('DROP TABLE trip_status');
        $this->addSql('DROP TABLE content_block');
        $this->addSql('DROP TABLE content_block_media');
        $this->addSql('DROP TABLE layout_type');
        $this->addSql('DROP TABLE institution');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE permission');
    }
}
