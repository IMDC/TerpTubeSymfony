<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141123021612 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE Forum ADD mediaDisplayOrder LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE message ADD mediaDisplayOrder LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE post ADD mediaDisplayOrder LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE thread ADD mediaDisplayOrder LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE fos_group ADD mediaDisplayOrder LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $default = serialize(array());

        $em->getConnection()->executeUpdate("UPDATE Forum SET mediaDisplayOrder = ?", array($default));
        $em->getConnection()->executeUpdate("UPDATE message SET mediaDisplayOrder = ?", array($default));
        $em->getConnection()->executeUpdate("UPDATE post SET mediaDisplayOrder = ?", array($default));
        $em->getConnection()->executeUpdate("UPDATE thread SET mediaDisplayOrder = ?", array($default));
        $em->getConnection()->executeUpdate("UPDATE fos_group SET mediaDisplayOrder = ?", array($default));
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE Forum DROP mediaDisplayOrder');
        $this->addSql('ALTER TABLE fos_group DROP mediaDisplayOrder');
        $this->addSql('ALTER TABLE message DROP mediaDisplayOrder');
        $this->addSql('ALTER TABLE post DROP mediaDisplayOrder');
        $this->addSql('ALTER TABLE thread DROP mediaDisplayOrder');
    }
}
