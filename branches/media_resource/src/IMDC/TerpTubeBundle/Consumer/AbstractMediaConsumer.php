<?php

namespace IMDC\TerpTubeBundle\Consumer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use FFMpeg\FFProbe\DataMapping\StreamCollection;
use IMDC\TerpTubeBundle\Entity\Media;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Monolog\Logger;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

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
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var ConsumerOptions
     */
    protected $message;

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
        $media = $this->entityManager->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        if ($media->getPendingOperations() != null && count($media->getPendingOperations()) > 0)
            return true;
        else
            return false;
    }

    protected function executePendingOperations($mediaId)
    {
        /** @var $media Media */
        $media = $this->entityManager->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);
        $pendingOperations = $media->getPendingOperations();
        foreach ($pendingOperations as $pendingOperation) {
            $operation = explode(",", $pendingOperation);
            $operationType = $operation [0];
            if ($operationType == "trim") {
                /*$operationMediaType = $operation [1];
                if ($operationMediaType == "mp4") {
                    $inputFile = $resource->getAbsolutePath();
                } else if ($operationMediaType == "webm") {
                    $inputFile = $resource->getAbsolutePath();
                }*/
                // regardless of $operationMediaType
                foreach ($media->getResources() as $resource) {
                    $inputFile = $resource->getAbsolutePath();
                    $startTime = $operation [2];
                    $endTime = $operation [3];
                    $this->transcoder->trimVideo($inputFile, $startTime, $endTime);
                }
                $this->logger->info("Transcoding operation " . $pendingOperation . " completed!");
            } else {
                $this->logger->error("Unknown operation " . $pendingOperation . "!");
            }
        }
        // FIXME may have a race condition if pending operations are updated elsewhere
        $pendingOperations = array();
        $media->setPendingOperations($pendingOperations);
        $this->entityManager->flush();
    }

    public function updateMetaData(ResourceFile $resourceFile)
    {
        $mediaType = $this->media->getType();

        switch ($mediaType) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                $file = new File($resourceFile->getAbsolutePath());
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

    protected function createResource(File $file)
    {
        // Correct the permissions to 664
        $old = umask(0);
        chmod($file->getRealPath(), 0664);
        umask($old);

        $resource = ResourceFile::fromFile($file);
        // explicitly set the extension to that of the transcoded file (ext won't be guessed)
        $resource->setPath($file->getExtension());

        // make it immediately usable
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->persist($resource);
        $em->flush();

        $this->updateMetaData($resource);
        $em->persist($resource);
        $em->flush();
        //$em->close();

        return $resource;
    }

    public function execute(AMQPMessage $msg)
    {
        $this->message = ConsumerOptions::unpack($msg->body);
        if (empty($this->message))
            return true;

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $this->media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($this->message->mediaId);
        if (empty($this->media)) {
            // Can happen if media is deleted before transcoding can be executed
            $this->logger->info(sprintf("Media with ID=%d does not exist and cannot be transcoded!", $this->message->mediaId));
            return true;
        }
        //$em->close();

        //TODO add state/status check to determine if ready?
        /*$transcodingType = $this->media->getIsReady();
        if (/*($transcodingType != Media::READY_MP4 &&
                $transcodingType != Media::READY_NO &&
                $transcodingType != Media::READY_WEBM) ||*
            $transcodingType == Media::READY_YES
        ) {
            // Already Transcoded should not be here
            $this->logger->error("Should not be in this place of transcoding when everything is already completed!");
            return true;
        }*/

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
                    // placeholders are used for pending transcodes that need preprocessing
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
                    return true;
                }

                break;
        }

        return true;
    }
}
