<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\File as IMDCFile;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\UploadedFile as IMDCUploadedFile;
use IMDC\TerpTubeBundle\Transcoding\Transcoder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResourceFile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     *
     * @var \IMDC\TerpTubeBundle\Entity\MetaData
     */
    private $metaData;

    /**
     * Unmapped property to handle file uploads
     * @var UploadedFile|File
     */
    private $file;

    /**
     * @var string
     */
    private $temp;

    /**
     * @var string
     */
    private $webRootPath;

    /**
     * @var string
     */
    private $uploadPath;

    public function __construct($config)
    {
        $this->webRootPath = $config['web_root_path'];
        $this->uploadPath = $config['upload_path'];
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
     * Set path
     *
     * @param string $path
     * @return ResourceFile
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ResourceFile
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return ResourceFile
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set metaData
     *
     * @param \IMDC\TerpTubeBundle\Entity\MetaData $metaData
     * @return ResourceFile
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

    public function getFilename()
    {
        return $this->id . '.' . $this->path;
    }

    /**
     * @return mixed
     */
    public function getWebRootPath()
    {
        return $this->webRootPath;
    }

    /**
     * @param mixed $webRootPath
     */
    public function setWebRootPath($webRootPath)
    {
        $this->webRootPath = $webRootPath;
    }

    /**
     * @return mixed
     */
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * @param mixed $uploadPath
     */
    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadPath() . '/' . $this->getFilename();
    }

    public function getUploadRootPath()
    {
        // the absolute directory path where uploaded documents should be saved
        return $this->getWebRootPath() . '/' . $this->getUploadPath();
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootPath() . '/' . $this->getFilename();
    }

    /**
     * Sets file. **From cookbook**
     *
     * @param UploadedFile|File $file
     * @return ResourceFile
     */
    public function setFile($file = null)
    {
        if (!($file instanceof UploadedFile || $file instanceof File))
            $this->file = null;

        if ($file instanceof UploadedFile) {
            $this->file = IMDCUploadedFile::fromUploadedFile($file);
        } else if ($file instanceof File) {
            $this->file = IMDCFile::fromFile($file);
        }

        // check if we have an old image path
        if (is_file($this->getAbsolutePath())) {
            // store the old name to delete after the update
            $this->temp = $this->getAbsolutePath();
        } else {
            $this->path = 'initial';
        }

        return $this;
    }

    /**
     * Get file.
     *
     * @return UploadedFile|File
     */
    public function getFile()
    {
        return $this->file;
    }

    public function preUpload()
    {
        $this->setUpdated(new \DateTime('now'));

        if (null === $this->getFile() || $this->path !== 'initial')
            return;

        $this->path = $this->getFile()->guessExtension();
    }

    /**
     * Dispatches an uploaded event after the file is uploaded and passes the object as an argument.
     */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile())
            return;

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->temp);
            // clear the temp image path
            $this->temp = null;
        }

        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        $this->getFile()->move(
            $this->getUploadRootPath(),
            $this->getFilename()
        );

        $this->setFile(null);
    }

    public function storeFilenameForRemove()
    {
        $this->temp = $this->getAbsolutePath();
    }

    public function removeUpload()
    {
        if (file_exists($this->temp))
            unlink($this->temp);
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire.
     * only called from Admin\ResourceFileAdmin::manageFileUpload
     */
    public function refreshUpdated() //TODO delete?
    {
        $this->setUpdated(new \DateTime('NOW'));
    }

    public function updateMetaData($mediaType, Transcoder $transcoder)
    {
        $metaData = $this->getMetaData();
        if ($metaData == null) {
            $metaData = new MetaData();
            $this->setMetaData($metaData);
        }

        if (!is_file($this->getAbsolutePath()))
            return; //TODO throw exception?

        $metaData->setSize(filesize($this->getAbsolutePath()));

        switch ($mediaType) {
            case Media::TYPE_VIDEO:
            case Media::TYPE_AUDIO:
                $file = new File($this->getAbsolutePath());
                $ffprobe = $transcoder->getFFprobe();
                $format = $ffprobe->format($file->getRealPath());

                $duration = $format->has('duration') ? $format->get('duration') : 0;
                $metaData->setDuration($duration);

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
            case Media::TYPE_IMAGE:
                $imageSize = getimagesize($this->getAbsolutePath());

                $metaData->setWidth($imageSize[0]);
                $metaData->setHeight($imageSize[1]);

                break;
        }
    }

    public static function fromFile($file, $config)
    {
        $resource = new self($config);
        $resource->setFile($file);
        $resource->setCreated(new \DateTime());

        return $resource;
    }

    /**
     * String description of a resource file
     */
    public function __toString()
    {
        return $this->getFilename();
    }
}
