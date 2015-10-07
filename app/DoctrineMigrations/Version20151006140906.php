<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151006140906 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE content_block_media_wrapper (content_block_id INT NOT NULL, media_wrapper_id INT NOT NULL, INDEX IDX_7845F20FBB5A68E3 (content_block_id), INDEX IDX_7845F20FD1802297 (media_wrapper_id), PRIMARY KEY(content_block_id, media_wrapper_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mediawrapper (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, weight INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_wrapper_media (media_wrapper_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_C17F91CFD1802297 (media_wrapper_id), INDEX IDX_C17F91CFEA9FDD75 (media_id), PRIMARY KEY(media_wrapper_id, media_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content_block_media_wrapper ADD CONSTRAINT FK_7845F20FBB5A68E3 FOREIGN KEY (content_block_id) REFERENCES content_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_block_media_wrapper ADD CONSTRAINT FK_7845F20FD1802297 FOREIGN KEY (media_wrapper_id) REFERENCES mediawrapper (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_wrapper_media ADD CONSTRAINT FK_C17F91CFD1802297 FOREIGN KEY (media_wrapper_id) REFERENCES mediawrapper (id)');
        $this->addSql('ALTER TABLE media_wrapper_media ADD CONSTRAINT FK_C17F91CFEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('DROP TABLE content_block_media');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE content_block_media_wrapper DROP FOREIGN KEY FK_7845F20FD1802297');
        $this->addSql('ALTER TABLE media_wrapper_media DROP FOREIGN KEY FK_C17F91CFD1802297');
        $this->addSql('CREATE TABLE content_block_media (content_block_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_FE17CD6CBB5A68E3 (content_block_id), INDEX IDX_FE17CD6CEA9FDD75 (media_id), PRIMARY KEY(content_block_id, media_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content_block_media ADD CONSTRAINT FK_FE17CD6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_block_media ADD CONSTRAINT FK_FE17CD6CBB5A68E3 FOREIGN KEY (content_block_id) REFERENCES content_block (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE content_block_media_wrapper');
        $this->addSql('DROP TABLE mediawrapper');
        $this->addSql('DROP TABLE media_wrapper_media');
    }
}
