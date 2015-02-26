<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141209051113 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function preUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $conn = $em->getConnection();

        $existing = $conn->fetchAll('SELECT * FROM fos_user_group');

        $exists = function($userId, $groupId) use ($existing) {
            foreach ($existing as $exist) {
                if ($exist['user_id'] == $userId
                    && $exist['userGroup_id'] == $groupId)
                    return true;
            }
            return false;
        };

        $stmt = $conn->query('SELECT * FROM usergroup_members_users');
        while ($row = $stmt->fetch()) {
            $userId = $row['user_id'];
            $groupId = $row['usergroup_id'];

            if (!$exists($userId, $groupId)) {
                $conn->insert('fos_user_group', array(
                    'user_id' => $userId,
                    'userGroup_id' => $groupId
                ));
            }
        }
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE usergroup_members_users');
        $this->addSql('ALTER TABLE fos_user_group DROP FOREIGN KEY FK_583D1F3E2B674466');
        $this->addSql('DROP INDEX IDX_583D1F3E2B674466 ON fos_user_group');
        $this->addSql('ALTER TABLE fos_user_group DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE fos_user_group CHANGE usergroup_id group_id INT NOT NULL');
        $this->addSql('ALTER TABLE fos_user_group ADD CONSTRAINT FK_583D1F3EFE54D947 FOREIGN KEY (group_id) REFERENCES fos_group (id)');
        $this->addSql('CREATE INDEX IDX_583D1F3EFE54D947 ON fos_user_group (group_id)');
        $this->addSql('ALTER TABLE fos_user_group ADD PRIMARY KEY (user_id, group_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE usergroup_members_users (usergroup_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_E11BF7D9D2112630 (usergroup_id), INDEX IDX_E11BF7D9A76ED395 (user_id), PRIMARY KEY(usergroup_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usergroup_members_users ADD CONSTRAINT FK_E11BF7D9D2112630 FOREIGN KEY (usergroup_id) REFERENCES fos_group (id)');
        $this->addSql('ALTER TABLE usergroup_members_users ADD CONSTRAINT FK_E11BF7D9A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE fos_user_group DROP FOREIGN KEY FK_583D1F3EFE54D947');
        $this->addSql('DROP INDEX IDX_583D1F3EFE54D947 ON fos_user_group');
        $this->addSql('ALTER TABLE fos_user_group DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE fos_user_group CHANGE group_id userGroup_id INT NOT NULL');
        $this->addSql('ALTER TABLE fos_user_group ADD CONSTRAINT FK_583D1F3E2B674466 FOREIGN KEY (userGroup_id) REFERENCES fos_group (id)');
        $this->addSql('CREATE INDEX IDX_583D1F3E2B674466 ON fos_user_group (userGroup_id)');
        $this->addSql('ALTER TABLE fos_user_group ADD PRIMARY KEY (user_id, userGroup_id)');
    }
}
