<?php

namespace IMDC\TerpTubeBundle\Tests\Consumer;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Consumer\Options\StatusConsumerOptions;
use IMDC\TerpTubeBundle\Consumer\StatusConsumer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class StatusConsumerTest
 * @package IMDC\TerpTubeBundle\Tests\Consumer
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class StatusConsumerTest extends WebTestCase
{
    /**
     * @var ConsumerInterface
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    public function testInstantiate()
    {
        $consumer = $this->getStatusConsumer();

        $this->assertNotNull($consumer);
        $this->assertInstanceOf('\IMDC\TerpTubeBundle\Consumer\StatusConsumer', $consumer);
    }

    public function testExecute()
    {
        $opts = new StatusConsumerOptions();
        $opts->status = 'done';
        $opts->who = get_class($this);
        $opts->what = 'Test';
        $opts->identifier = 'test';
        $serialized = $opts->pack();

        $conn = $this->entityManager->getConnection();

        // get current number of old statuses
        $getOldNum = function () use ($conn) {
            $sql = "SELECT * FROM entity_status WHERE timestamp < :timestamp";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('timestamp', time() - (6 * 3600)); // see StatusConsumer::execute
            $stmt->execute();
            return $stmt->rowCount();
        };

        $oldNum = $getOldNum();

        if ($oldNum == 0) {
            // insert an old status
            $sql = "INSERT INTO entity_status SET status = :status, who = :who, what = :what, identifier = :identifier, timestamp = :timestamp ON DUPLICATE KEY UPDATE status = :status, who = :who, what = :what, identifier = :identifier, timestamp = :timestamp";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('status', $opts->status);
            $stmt->bindValue('who', $opts->who);
            $stmt->bindValue('what', $opts->what);
            $stmt->bindValue('identifier', $opts->identifier);
            $stmt->bindValue('timestamp', time() - (7 * 3600));
            $stmt->execute();
        }

        $consumer = $this->getStatusConsumer();
        $result = $consumer->execute(new AMQPMessage($serialized));

        $this->assertEquals(ConsumerInterface::MSG_ACK, $result);
        $this->assertEquals(0, $getOldNum());
    }

    /**
     * @return StatusConsumer
     */
    private function getStatusConsumer()
    {
        $doctrine = $this->container->get('doctrine');

        return new StatusConsumer($doctrine);
    }
}
