<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150608035640 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE resource_file ADD meta_data INT DEFAULT NULL, ADD created DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_file ADD CONSTRAINT FK_83BF96AA3E558020 FOREIGN KEY (meta_data) REFERENCES meta_data (id)');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C3E558020');
        $this->addSql('DROP INDEX UNIQ_6A2CA10C3E558020 ON media');
        $this->addSql('UPDATE resource_file LEFT JOIN media_resources ON (resource_file.id = media_resources.resource_id) INNER JOIN media ON (media.id = media_resources.media_id) SET resource_file.meta_data = media.meta_data');
        $this->addSql('UPDATE resource_file INNER JOIN media ON (media.source_resource_id = resource_file.id) SET resource_file.meta_data = media.meta_data');
        $this->addSql('UPDATE resource_file INNER JOIN meta_data ON (resource_file.meta_data = meta_data.id) SET resource_file.created = meta_data.timeUploaded ');
//         $this->addSql('CREATE UNIQUE INDEX UNIQ_83BF96AA3E558020 ON resource_file (meta_data)');
        $this->addSql('ALTER TABLE media DROP meta_data');
        $this->addSql('ALTER TABLE meta_data DROP timeUploaded');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE media ADD meta_data INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C3E558020 FOREIGN KEY (meta_data) REFERENCES meta_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10C3E558020 ON media (meta_data)');
        $this->addSql('ALTER TABLE meta_data ADD timeUploaded DATETIME NOT NULL');
        $this->addSql('UPDATE meta_data INNER JOIN resource_file ON (resource_file.meta_data = meta_data.id) SET meta_data.timeUploaded = resource_file.created ');
        $this->addSql('UPDATE media LEFT JOIN media_resources ON (media.id = media_resources.media_id) INNER JOIN resource_file ON (resource_file.id = media_resources.resource_id) SET media.meta_data = resource_file.meta_data ');
        $this->addSql('UPDATE media INNER JOIN resource_file ON (media.source_resource_id = resource_file.id) SET media.meta_data = resource_file.meta_data ');
        $this->addSql('ALTER TABLE resource_file DROP FOREIGN KEY FK_83BF96AA3E558020');
//         $this->addSql('DROP INDEX UNIQ_83BF96AA3E558020 ON resource_file');
        $this->addSql('ALTER TABLE resource_file DROP meta_data, DROP created');
    }
}
