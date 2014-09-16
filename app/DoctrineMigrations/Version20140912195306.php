<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Query\ResultSetMapping;
use IMDC\TerpTubeBundle\DataFixtures\ORM\InvitationTypes;
use IMDC\TerpTubeBundle\Entity\InvitationType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140912195306 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;
    private $invitations;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function preUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $this->invitations = $em->getConnection()->fetchAll("SELECT id, becomeMentor, becomeMentee FROM Invitation");
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE invitation_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Invitation ADD type_id INT DEFAULT NULL, ADD data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', DROP becomeMentor, DROP becomeMentee');
        $this->addSql('ALTER TABLE Invitation ADD CONSTRAINT FK_BE406272C54C8C93 FOREIGN KEY (type_id) REFERENCES invitation_type (id)');
        $this->addSql('CREATE INDEX IDX_BE406272C54C8C93 ON Invitation (type_id)');
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $types = new InvitationTypes();
        $types->load($em);

        foreach ($this->invitations as $invitation) {
            $typeId = $invitation['becomeMentor'] ? InvitationType::TYPE_MENTOR : InvitationType::TYPE_MENTEE;
            $em->getConnection()->executeUpdate("UPDATE Invitation SET type_id = ? WHERE id = ?", array($typeId, $invitation['id']));
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Invitation DROP FOREIGN KEY FK_BE406272C54C8C93');
        $this->addSql('DROP TABLE invitation_type');
        $this->addSql('DROP INDEX UNIQ_BE406272C54C8C93 ON Invitation');
        $this->addSql('ALTER TABLE Invitation ADD becomeMentor TINYINT(1) DEFAULT NULL, ADD becomeMentee TINYINT(1) DEFAULT NULL, DROP type_id, DROP data');
    }
}
