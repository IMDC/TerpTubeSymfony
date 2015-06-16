<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File as IMDCFile;
use IMDC\TerpTubeBundle\Entity\MediaStateConst;
use IMDC\TerpTubeBundle\Transcoding\ContainerConst;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;

class MultiplexConsumer extends AbstractMediaConsumer
{
    /**
     * @var Producer
     */
    protected $transcodeProducer;

    public function __construct($logger, $doctrine, $transcoder, $entityStatusProducer, $transcodeProducer)
    {
        parent::__construct($logger, $doctrine, $transcoder, $entityStatusProducer);

        $this->transcodeProducer = $transcodeProducer;
    }

    public function execute(AMQPMessage $msg)
    {
        // extracts media from message
        // $msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        $result = parent::execute($msg);

        if (empty($this->media))
            return $result;

        if (!($this->message instanceof MultiplexConsumerOptions)) {
            $this->logger->error("no multiplex options specified");
            return self::MSG_REJECT;
        }
        /** @var MultiplexConsumerOptions $multiplexOpts */
        $multiplexOpts = MultiplexConsumerOptions::unpack($msg->body);

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media->setState(MediaStateConst::PROCESSING);
        $em->persist($this->media);
        $em->flush();

        // if not already done
        if ($this->media->getSourceResource()->getPath() !== 'placeholder') {
            $this->logger->error("expected source resource to be a placeholder.");
            return self::MSG_REJECT;
        }

        try {
            $resourceFile = null;

            switch ($multiplexOpts->operation) {
                case MultiplexOperation::MUX:
                    $resourceFile = $this->transcoder->mergeAudioVideo($multiplexOpts->audio, $multiplexOpts->video);
                    break;
                case MultiplexOperation::REMUX:
                    $resourceFile = $this->transcoder->remuxWebM($multiplexOpts->audio);
                    break;
                default:
                    throw new \Exception("unknown multiplex operation");
            }

            if ($resourceFile == null)
                throw new \Exception("Could not transcode the video for some reason");

            $em = $this->doctrine->getManager();

            $resourceFile = $this->media->getSourceResource()
                ->setFile(IMDCFile::fromFile($resourceFile))
                ->setPath($resourceFile->getExtension());
            $em->persist($resourceFile);
            $em->flush();

            $resourceFile->updateMetaData($this->media->getType(), $this->transcoder);
            $this->media->createThumbnail($this->transcoder);
            $em->persist($this->media);
            $em->flush();
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            return self::MSG_REJECT;
        }

        // queue for transcode
        $opts = new TranscodeConsumerOptions();
        $opts->mediaId = $this->media->getId();
        $opts->container = ContainerConst::WEBM;
        $opts->preset = 'ffmpeg.webm_720p_video';
        $this->transcodeProducer->publish($opts->pack());

        $opts->container = ContainerConst::MP4;
        $opts->preset = 'ffmpeg.x264_720p_video';
        $this->transcodeProducer->publish($opts->pack());

        $opts = new StatusConsumerOptions();
        $opts->status = 'done';
        $opts->who = get_class($this);
        $opts->what = get_class($this->media);
        $opts->identifier = $this->media->getId();
        $this->entityStatusProducer->publish($opts->pack());

        return self::MSG_ACK;
    }
}
