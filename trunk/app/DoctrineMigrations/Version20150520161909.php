<?php

namespace IMDC\TerpTubeBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520161909 extends AbstractMigration implements ContainerAwareInterface
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

        $this->addSql('CREATE TABLE media_resources (media_id INT NOT NULL, resource_id INT NOT NULL, INDEX IDX_CA065820EA9FDD75 (media_id), UNIQUE INDEX UNIQ_CA06582089329D25 (resource_id), PRIMARY KEY(media_id, resource_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media_resources ADD CONSTRAINT FK_CA065820EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE media_resources ADD CONSTRAINT FK_CA06582089329D25 FOREIGN KEY (resource_id) REFERENCES resource_file (id)');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C89329D25');
        $this->addSql('DROP INDEX UNIQ_6A2CA10C89329D25 ON media');
        $this->addSql('ALTER TABLE media CHANGE resource_id source_resource_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CB54DCD85 FOREIGN KEY (source_resource_id) REFERENCES resource_file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10CB54DCD85 ON media (source_resource_id)');
    }

    public function postUp(Schema $schema)
    {
        /** @var $em EntityManager */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $conn = $em->getConnection();

        $stmt = $conn->query('SELECT * FROM media');
        while ($row = $stmt->fetch()) {
            $mediaType = $row['type'];

            if ($mediaType != Media::TYPE_VIDEO && $mediaType != Media::TYPE_AUDIO)
                continue;

            $mediaId = $row['id'];
            $resourceId = $row['source_resource_id'];

            $conn->insert('media_resources', array(
                'media_id' => $mediaId,
                'resource_id' => $resourceId
            ));

            $conn->update('media', array('source_resource_id' => null), array('id' => $mediaId));
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE media_resources');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10CB54DCD85');
        $this->addSql('DROP INDEX UNIQ_6A2CA10CB54DCD85 ON media');
        $this->addSql('ALTER TABLE media CHANGE source_resource_id resource_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C89329D25 FOREIGN KEY (resource_id) REFERENCES resource_file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2CA10C89329D25 ON media (resource_id)');
    }
}
