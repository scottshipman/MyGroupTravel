<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150818100728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE content_block_media (content_block_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_FE17CD6CBB5A68E3 (content_block_id), INDEX IDX_FE17CD6CEA9FDD75 (media_id), PRIMARY KEY(content_block_id, media_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content_block_media ADD CONSTRAINT FK_FE17CD6CBB5A68E3 FOREIGN KEY (content_block_id) REFERENCES content_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_block_media ADD CONSTRAINT FK_FE17CD6CEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_block DROP FOREIGN KEY FK_68D8C3F06A2CA10C');
        $this->addSql('DROP INDEX IDX_68D8C3F06A2CA10C ON content_block');
        $this->addSql('ALTER TABLE content_block DROP media, CHANGE layoutType layouttype INT DEFAULT NULL, CHANGE sortOrder sortOrder INT DEFAULT NULL');
        $this->addSql('ALTER TABLE content_block ADD CONSTRAINT FK_68D8C3F0578FFD90 FOREIGN KEY (layouttype) REFERENCES layout_type (id)');
        $this->addSql('CREATE INDEX IDX_68D8C3F0578FFD90 ON content_block (layouttype)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE content_block_media');
        $this->addSql('ALTER TABLE content_block DROP FOREIGN KEY FK_68D8C3F0578FFD90');
        $this->addSql('DROP INDEX IDX_68D8C3F0578FFD90 ON content_block');
        $this->addSql('ALTER TABLE content_block ADD media INT DEFAULT NULL, CHANGE layouttype layoutType INT NOT NULL, CHANGE sortOrder sortOrder INT NOT NULL');
        $this->addSql('ALTER TABLE content_block ADD CONSTRAINT FK_68D8C3F06A2CA10C FOREIGN KEY (media) REFERENCES media (id)');
        $this->addSql('CREATE INDEX IDX_68D8C3F06A2CA10C ON content_block (media)');
    }
}
