<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150127075223 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE compound_media');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE compound_media (id INT AUTO_INCREMENT NOT NULL, target_id INT DEFAULT NULL, source_id INT DEFAULT NULL, type SMALLINT NOT NULL, targetStartTime NUMERIC(12, 2) NOT NULL, INDEX IDX_31CD0C60953C1C61 (source_id), INDEX IDX_31CD0C60158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compound_media ADD CONSTRAINT FK_31CD0C60158E0B66 FOREIGN KEY (target_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE compound_media ADD CONSTRAINT FK_31CD0C60953C1C61 FOREIGN KEY (source_id) REFERENCES media (id)');
    }
}
