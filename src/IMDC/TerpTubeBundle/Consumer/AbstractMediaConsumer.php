<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use FFMpeg\FFProbe\DataMapping\StreamCollection;
use IMDC\TerpTubeBundle\Consumer\Options\AbstractMediaConsumerOptions;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractMediaConsumer implements MediaConsumerInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var Transcoder
     */
    protected $transcoder;

    /**
     * @var AbstractMediaConsumerOptions
     */
    protected $message;

    /**
     * @var Producer
     */
    protected $entityStatusProducer;

    /**
     * @var Media
     */
    protected $media;

    public function __construct($logger, $doctrine, $transcoder, $entityStatusProducer)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->transcoder = $transcoder;
        $this->entityStatusProducer = $entityStatusProducer;
    }

    public function execute(AMQPMessage $msg)
    {
        $this->message = AbstractMediaConsumerOptions::unpack($msg->body);
        if (empty($this->message))
            return self::MSG_REJECT;

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($this->message->mediaId);
        if (empty($this->media)) {
            // Can happen if media is deleted before transcoding can be executed
            $this->logger->info(sprintf("Media with ID=%d does not exist and cannot be transcoded!", $this->message->mediaId));
            return self::MSG_REJECT;
        }

        $mediaType = $this->media->getType();
        switch ($mediaType) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                try {
                    // for video/audio files, the source file may be deleted after transcoding
                    // so check for it
                    $sourceResource = $this->media->getSourceResource();
                    if (!file_exists($sourceResource->getAbsolutePath()))
                        throw new \Exception('file not found'); // TODO make me better

                    // break for placeholder files
                    // placeholders are used for media that need preprocessing
                    if ($sourceResource->getPath() === 'placeholder')
                        break;

                    /** @var $streams StreamCollection */
                    $streams = $this->transcoder->getFFprobe()->streams($sourceResource->getAbsolutePath());
                    if (($mediaType == Media::TYPE_VIDEO && $streams->videos()->count() == 0)
                        || ($mediaType == Media::TYPE_AUDIO && $streams->audios()->count() == 0)
                    ) {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    // TODO need to send an event to alert the user that this is an invalid video/audio
                    // not a video so don't hold up the queue
                    $this->logger->error(sprintf("Error. Not a valid video/audio file %d!", $this->media->getId()));
                    $this->media = null; // null it so that child classes don't attempt any work
                    return self::MSG_REJECT;
                }

                break;
        }

        return self::MSG_ACK;
    }
}
