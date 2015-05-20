<?php

namespace IMDC\TerpTubeBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\HttpFoundation\File\File;

interface MediaConsumerInterface extends ConsumerInterface
{
    public function updateMetaData();
}
