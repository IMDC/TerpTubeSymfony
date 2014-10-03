<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141002225840 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE Forum ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Forum ADD CONSTRAINT FK_44EA91C9FE54D947 FOREIGN KEY (group_id) REFERENCES fos_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_44EA91C9FE54D947 ON Forum (group_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE Forum DROP FOREIGN KEY FK_44EA91C9FE54D947');
        $this->addSql('DROP INDEX UNIQ_44EA91C9FE54D947 ON Forum');
        $this->addSql('ALTER TABLE Forum DROP group_id');
    }
}
