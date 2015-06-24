<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Consumer\Options\StatusConsumerOptions;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class StatusConsumer implements ConsumerInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var StatusConsumerOptions
     */
    protected $message;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function execute(AMQPMessage $msg)
    {
        $this->message = StatusConsumerOptions::unpack($msg->body);
        if (empty($this->message))
            return self::MSG_REJECT;

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $sql = "INSERT INTO entity_status SET status = :status, who = :who, what = :what, identifier = :identifier, timestamp = :timestamp ON DUPLICATE KEY UPDATE status = :status, who = :who, what = :what, identifier = :identifier, timestamp = :timestamp";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue('status', $this->message->status);
        $stmt->bindValue('who', $this->message->who);
        $stmt->bindValue('what', $this->message->what);
        $stmt->bindValue('identifier', $this->message->identifier);
        $stmt->bindValue('timestamp', time());
        $stmt->execute();

        $sql = "DELETE FROM entity_status WHERE timestamp < :timestamp";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue('timestamp', time() - (6 * 3600)); // 6 hours old
        $stmt->execute();

        return self::MSG_ACK;
    }
}
