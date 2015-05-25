<?php

namespace IMDC\TerpTubeBundle\Consumer;

use IMDC\TerpTubeBundle\Entity\ResourceFile;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

interface MediaConsumerInterface extends ConsumerInterface
{
    public function updateMetaData(ResourceFile $resourceFile);
}
