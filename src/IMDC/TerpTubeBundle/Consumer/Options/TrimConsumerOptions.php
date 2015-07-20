<?php

namespace IMDC\TerpTubeBundle\Consumer\Options;

class TrimConsumerOptions extends AbstractMediaConsumerOptions
{
    /**
     * @var float
     */
    public $startTime;

    /**
     * @var float
     */
    public $endTime;

    /**
     * @var float
     */
    public $currentDuration;
}
