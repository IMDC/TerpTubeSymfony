<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;

class ResourceFile
{
    const UPLOAD_DIR = 'uploads/media'; //TODO move to app config

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
    private $updated;

    /**
     * Unmapped property to handle file uploads
     */
    private $file;

    /**
     * @var string
     */
    private $temp;

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

    public function getFilename()
    {
        return $this->id . '.' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : static::UPLOAD_DIR . '/' . $this->getFilename();
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        //TODO move to app config
        return __DIR__ . '/../../../../web/' . static::UPLOAD_DIR;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->getFilename();
    }

    /**
     * Sets file. **From cookbook**
     *
     * @param BaseUploadedFile $file
     * @return ResourceFile
     */
    public function setFile(BaseUploadedFile $file = null)
    {
        $this->file = null === $file
            ? null
            : UploadedFile::fromUploadedFile($file);

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
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function preUpload()
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->path = $this->getFile()->guessExtension();
    }

    /**
     * Dispatches an uploaded event after the file is uploaded and passes the object as an argument.
     */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

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
            $this->getUploadRootDir(),
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
     * Updates the hash value to force the preUpdate and postUpdate events to fire
     */
    public function refreshUpdated()
    {
        $this->setUpdated(new \DateTime('NOW'));
    }

    /**
     * String description of a resource file
     */
    public function __toString()
    {
        return $this->getFilename();
    }
}
