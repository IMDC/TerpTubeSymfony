<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
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
    private $state;

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
        $this->state = MediaStateConst::UNPROCESSED;
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
     * Set state
     *
     * @param integer $state
     * @return Media
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
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

    public function createThumbnail(Transcoder $transcoder)
    {
        $sourceResource = $this->getSourceResource();
        $fs = new Filesystem();

        $thumbnailTempFile = $transcoder->createThumbnail($sourceResource->getAbsolutePath(), $this->getType());
        $thumbnailFile = $this->getThumbnailRootDir() . '/' . $sourceResource->getId() . '.png';
        $fs->rename($thumbnailTempFile, $thumbnailFile, true);
        $this->setThumbnailPath($sourceResource->getId() . '.png');
    }

    public function removeThumbnail()
    {
        $fs = new Filesystem();
        $fs->remove($this->getThumbnailPath());
        $this->setThumbnailPath(NULL);
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
