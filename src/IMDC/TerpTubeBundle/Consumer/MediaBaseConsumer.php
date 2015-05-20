<?php

namespace IMDC\TerpTubeBundle\Consumer;

use FFMpeg\FFProbe\DataMapping\StreamCollection;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Monolog\Logger;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class MediaBaseConsumer extends ContainerAware implements MediaConsumerInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var
     */
    protected $doctrine;

    /**
     * @var Transcoder
     */
    protected $transcoder;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var Media
     */
    protected $media;

    public function __construct($logger, $doctrine, $transcoder)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->transcoder = $transcoder;
        $this->fs = new Filesystem();
    }

    protected function checkForPendingOperations($mediaId)
    {
        $em = $this->doctrine->getManager();
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if ($media->getPendingOperations() != null && count($media->getPendingOperations()) > 0)
            return true;
        else
            return false;
    }

    protected function executePendingOperations($mediaId)
    {
        $em = $this->doctrine->getManager();
        $media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        $pendingOperations = $media->getPendingOperations();
        foreach ($pendingOperations as $pendingOperation) {
            $operation = explode(",", $pendingOperation);
            $operationType = $operation [0];
            if ($operationType == "trim") {
                $operationMediaType = $operation [1];
                $resource = $media->getResource();
                if ($operationMediaType == "mp4") {
                    $inputFile = $resource->getAbsolutePath();
                } else if ($operationMediaType == "webm") {
                    //$inputFile = $resource->getAbsolutePathWebm ();
                    $inputFile = $resource->getAbsolutePath();
                }
                $startTime = $operation [2];
                $endTime = $operation [3];
                $this->transcoder->trimVideo($inputFile, $startTime, $endTime);
                $this->logger->info("Transcoding operation " . $pendingOperation . " completed!");
            } else {
                $this->logger->error("Unknown operation " . $pendingOperation . "!");
            }
        }
        // FIXME may have a race condition if pending operations are updated elsewhere
        $pendingOperations = array();
        $media->setPendingOperations($pendingOperations);
        $em->flush();
    }

    public function updateMetaData()
    {
        $mediaType = $this->media->getType();

        switch ($mediaType) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                $file = new File($this->media->getResource()->getAbsolutePath());
                $ffprobe = $this->transcoder->getFFprobe();
                $format = $ffprobe->format($file->getRealPath());

                $duration = $format->has('duration') ? $format->get('duration') : 0;
                $fileSize = filesize($file->getRealPath());

                $metaData = $this->media->getMetaData();
                $metaData->setDuration($duration);
                $metaData->setSize($fileSize);

                if ($mediaType == Media::TYPE_VIDEO) {
                    /** @var $streams StreamCollection */
                    $streams = $ffprobe->streams($file->getRealPath());

                    $firstVideo = $streams->videos()->first();
                    $videoWidth = $firstVideo->get('width');
                    $videoHeight = $firstVideo->get('height');

                    $metaData->setWidth($videoWidth);
                    $metaData->setHeight($videoHeight);
                }

                break;
        }
    }

    public function execute(AMQPMessage $msg)
    {
        $message = unserialize($msg->body);
        if (empty($message))
            return true;

        $mediaId = $message['media_id'];
        $em = $this->doctrine->getManager();

        $this->media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if (empty($this->media)) {
            // Can happen if media is deleted before transcoding can be executed
            $this->logger->info(sprintf("Media with ID=%d does not exist and cannot be transcoded!", $mediaId));
            return true;
        }

        $mediaType = $this->media->getType();

        switch ($mediaType) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                try {
                    /** @var $streams StreamCollection */
                    $streams = $this->transcoder->getFFprobe()->streams($this->media->getResource()->getAbsolutePath());
                    if (($mediaType == Media::TYPE_VIDEO && $streams->videos()->count() == 0)
                        || ($mediaType == Media::TYPE_AUDIO && $streams->audios()->count() == 0)
                    ) {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    // TODO need to send an event to alert the user that this is an invalid video/audio
                    $this->media = null; // null it so that child classes don't attempt any work
                    // not a video so don't hold up the queue
                    $this->logger->error(sprintf("Error. Not a valid video/audio file %d!", $this->media->getId()));
                    return true;
                }

                break;
        }

        return true;
    }
}
