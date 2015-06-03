<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Media
 */
class Media implements MediaInterface
{

    const TYPE_IMAGE = 0;

    const TYPE_VIDEO = 1;

    const TYPE_AUDIO = 2;

    const TYPE_OTHER = 9;

    const READY_NO = 0;

    const READY_WEBM = 1;

    const READY_MP4 = 2;

    const READY_YES = 3;

    const THUMBNAIL_HEIGHT = 240;

    /**
     *
     * @var integer
     */
    private $id;

    /**
     *
     * @var integer
     */
    private $type;

    /**
     *
     * @var string
     */
    private $title;

    /**
     *
     * @var integer
     */
    private $isReady = 0;

    /**
     *
     * @var array
     */
    private $pendingOperations;

    /**
     *
     * @var string
     */
    private $thumbnailPath;

    /**
     *
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $owner;

    /**
     *
     * @var \IMDC\TerpTubeBundle\Entity\MetaData
     */
    private $metaData;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\ResourceFile
     */
    private $sourceResource;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $resources;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Media
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Media
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set isReady
     *
     * @param integer $isReady
     * @return Media
     */
    public function setIsReady($isReady)
    {
        $this->isReady = $isReady;

        return $this;
    }

    /**
     * Get isReady
     *
     * @return integer
     */
    public function getIsReady()
    {
        return $this->isReady;
    }

    /**
     * Set pendingOperations
     *
     * @param array $pendingOperations
     * @return Media
     */
    public function setPendingOperations($pendingOperations)
    {
        $this->pendingOperations = $pendingOperations;

        return $this;
    }

    /**
     * Get pendingOperations
     *
     * @return array
     */
    public function getPendingOperations()
    {
        return $this->pendingOperations;
    }

    /**
     * Set thumbnailPath
     *
     * @param string $thumbnailPath
     * @return Media
     */
    public function setThumbnailPath($thumbnailPath)
    {
        $this->thumbnailPath = $thumbnailPath;

        return $this;
    }

    /**
     * Get tumbnailPath
     *
     * @return string
     */
    public function getThumbnailPath()
    {
        if ($this->thumbnailPath != NULL)
            return $this->getThumbnailDir() . "/" . $this->thumbnailPath;
        else
            return NULL;
    }

    /**
     * Set metaData
     *
     * @param \IMDC\TerpTubeBundle\Entity\MetaData $metaData
     * @return Media
     */
    public function setMetaData(\IMDC\TerpTubeBundle\Entity\MetaData $metaData = null)
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * Get metaData
     *
     * @return \IMDC\TerpTubeBundle\Entity\MetaData
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Set owner
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $owner
     * @return Media
     */
    public function setOwner(\IMDC\TerpTubeBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \IMDC\TerpTubeBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set sourceResource
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $sourceResource
     * @return Media
     */
    public function setSourceResource(\IMDC\TerpTubeBundle\Entity\ResourceFile $sourceResource = null)
    {
        $this->sourceResource = $sourceResource;

        return $this;
    }

    /**
     * Get sourceResource
     *
     * @return \IMDC\TerpTubeBundle\Entity\ResourceFile
     */
    public function getSourceResource()
    {
        return $this->sourceResource;
    }

    /**
     * Add resources
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $resources
     * @return Media
     */
    public function addResource(\IMDC\TerpTubeBundle\Entity\ResourceFile $resources)
    {
        $this->resources[] = $resources;

        return $this;
    }

    /**
     * Remove resources
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $resources
     */
    public function removeResource(\IMDC\TerpTubeBundle\Entity\ResourceFile $resources)
    {
        $this->resources->removeElement($resources);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->resources;
    }

    public function getThumbnailDir()
    {
        return 'uploads/media/thumbnails';
    }

    public function getThumbnailRootDir()
    {
        return __DIR__ . '/../../../../web/' . $this->getThumbnailDir();
    }

    /**
     * @ORM\PreRemove
     */
    public function removeThumbnail()
    {
        // Add your code here
        try {
            $fs = new Filesystem();
            $fs->remove($this->getThumbnailPath());
            $this->setThumbnailPath(NULL);
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
        }
    }

    public function isInterpretation()
    {
        return false;
    }

    public function __toString()
    {
        return (string)$this->getTitle();

        // switch ($this->type) {
        // case 0: // image
        // return $this->resource->getWebPath();
        // break;

        // case 1: // video
        // return $this->resource->getWebPathWebm();
        // break;

        // case 2: // audio
        // return $this->resource->getWebPath();
        // break;

        // default:
        // return $this->resource->getWebPath();
        // break;
        // }
    }
}