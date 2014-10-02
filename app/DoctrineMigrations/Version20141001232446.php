<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141001232446 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE fos_user_rolegroup DROP FOREIGN KEY FK_48754D6E36D19DC1');
        $this->addSql('DROP TABLE fos_rolegroup');
        $this->addSql('DROP TABLE fos_user_rolegroup');
        $this->addSql('UPDATE fos_group SET roles = \'N;\' WHERE roles = \'\'');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE fos_rolegroup (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_BC280BAB5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_rolegroup (user_id INT NOT NULL, rolegroup_id INT NOT NULL, INDEX IDX_48754D6EA76ED395 (user_id), INDEX IDX_48754D6E36D19DC1 (rolegroup_id), PRIMARY KEY(user_id, rolegroup_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fos_user_rolegroup ADD CONSTRAINT FK_48754D6E36D19DC1 FOREIGN KEY (rolegroup_id) REFERENCES fos_rolegroup (id)');
        $this->addSql('ALTER TABLE fos_user_rolegroup ADD CONSTRAINT FK_48754D6EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    }
}
