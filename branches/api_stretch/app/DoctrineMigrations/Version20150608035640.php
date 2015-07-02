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

        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C3E558020');
        $this->addSql('DROP INDEX UNIQ_6A2CA10C3E558020 ON media');
        $this->addSql('ALTER TABLE media DROP meta_data');
        $this->addSql('ALTER TABLE meta_data DROP timeUploaded');
        $this->addSql('ALTER TABLE resource_file ADD meta_data INT DEFAULT NULL, ADD created DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_file ADD CONSTRAINT FK_83BF96AA3E558020 FOREIGN KEY (meta_data) REFERENCES meta_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83BF96AA3E558020 ON resource_file (meta_data)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE media ADD meta_data INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C3E558020 FOREIGN KEY (meta_data) REFERENCES meta_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10C3E558020 ON media (meta_data)');
        $this->addSql('ALTER TABLE meta_data ADD timeUploaded DATETIME NOT NULL');
        $this->addSql('ALTER TABLE resource_file DROP FOREIGN KEY FK_83BF96AA3E558020');
        $this->addSql('DROP INDEX UNIQ_83BF96AA3E558020 ON resource_file');
        $this->addSql('ALTER TABLE resource_file DROP meta_data, DROP created');
    }
}
