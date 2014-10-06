<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use IMDC\TerpTubeBundle\Entity\AccessType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Schema\Schema;
use IMDC\TerpTubeBundle\DataFixtures\ORM\AccessTypes;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141003231527 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('ALTER TABLE permissions_usergroups DROP FOREIGN KEY FK_F37943269C3E4F87');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C839C3E4F87');
        $this->addSql('ALTER TABLE users_permissions DROP FOREIGN KEY FK_DA58F09D9C3E4F87');
        $this->addSql('CREATE TABLE access_type (id INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE Permissions');
        $this->addSql('DROP TABLE permissions_usergroups');
        $this->addSql('DROP TABLE users_permissions');
        $this->addSql('ALTER TABLE Forum DROP INDEX UNIQ_44EA91C9FE54D947, ADD INDEX IDX_44EA91C9FE54D947 (group_id)');
        $this->addSql('ALTER TABLE Forum ADD access_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Forum ADD CONSTRAINT FK_44EA91C9D695686 FOREIGN KEY (access_type_id) REFERENCES access_type (id)');
        $this->addSql('CREATE INDEX IDX_44EA91C9D695686 ON Forum (access_type_id)');
        $this->addSql('DROP INDEX UNIQ_31204C839C3E4F87 ON thread');
        $this->addSql('ALTER TABLE thread DROP COLUMN permissions_id');
        $this->addSql('ALTER TABLE thread ADD access_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83D695686 FOREIGN KEY (access_type_id) REFERENCES access_type (id)');
        $this->addSql('CREATE INDEX IDX_31204C83D695686 ON thread (access_type_id)');
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $types = new AccessTypes();
        $types->load($em);

        $em->getConnection()->executeUpdate('UPDATE Forum SET access_type_id = ?, group_id = NULL', array(AccessType::TYPE_PUBLIC));
        $em->getConnection()->executeUpdate('UPDATE thread SET access_type_id = ?', array(AccessType::TYPE_PUBLIC));
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Forum DROP FOREIGN KEY FK_44EA91C9D695686');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83D695686');
        $this->addSql('CREATE TABLE Permissions (id INT AUTO_INCREMENT NOT NULL, accessLevel SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permissions_usergroups (permissions_id INT NOT NULL, usergroup_id INT NOT NULL, UNIQUE INDEX UNIQ_F3794326D2112630 (usergroup_id), INDEX IDX_F37943269C3E4F87 (permissions_id), PRIMARY KEY(permissions_id, usergroup_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_permissions (permissions_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DA58F09D9C3E4F87 (permissions_id), INDEX IDX_DA58F09DA76ED395 (user_id), PRIMARY KEY(permissions_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE permissions_usergroups ADD CONSTRAINT FK_F37943269C3E4F87 FOREIGN KEY (permissions_id) REFERENCES Permissions (id)');
        $this->addSql('ALTER TABLE permissions_usergroups ADD CONSTRAINT FK_F3794326D2112630 FOREIGN KEY (usergroup_id) REFERENCES fos_group (id)');
        $this->addSql('ALTER TABLE users_permissions ADD CONSTRAINT FK_DA58F09D9C3E4F87 FOREIGN KEY (permissions_id) REFERENCES Permissions (id)');
        $this->addSql('ALTER TABLE users_permissions ADD CONSTRAINT FK_DA58F09DA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('DROP TABLE access_type');
        $this->addSql('ALTER TABLE Forum DROP INDEX IDX_44EA91C9FE54D947, ADD UNIQUE INDEX UNIQ_44EA91C9FE54D947 (group_id)');
        $this->addSql('DROP INDEX IDX_44EA91C9D695686 ON Forum');
        $this->addSql('ALTER TABLE Forum DROP access_type_id');
        $this->addSql('DROP INDEX IDX_31204C83D695686 ON thread');
        $this->addSql('ALTER TABLE thread CHANGE access_type_id permissions_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C839C3E4F87 FOREIGN KEY (permissions_id) REFERENCES Permissions (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31204C839C3E4F87 ON thread (permissions_id)');
    }
}
