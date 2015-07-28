<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150612172936 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE media DROP pendingOperations, CHANGE isready state SMALLINT NOT NULL');

        // rather than translate the old consts to the new ones, just set everything to a ready state
        // resulting problem media could be handled manually
        $this->addSql('UPDATE media SET state = 2'); // MediaStateConst::READY
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE media ADD pendingOperations LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE state isReady SMALLINT NOT NULL');
        $this->addSql('UPDATE media SET isReady = 3'); // old Media::READY_YES
    }
}
