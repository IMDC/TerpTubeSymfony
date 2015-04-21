<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;
use IMDC\TerpTubeBundle\Component\HttpFoundation\File\UploadedFile;

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
     * @var string
     */
    private $webmExtension; //TODO delete

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var string
     */
    private $filename; //TODO delete. not used

    /**
     * Unmapped property to handle file uploads
     */
    private $file;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $media; //TODO delete. not used

    /**
     * @var string
     */
    private $name; //TODO delete. not used

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
     * Set webmExtension
     *
     * @param string $webmExtension
     * @return ResourceFile
     * @deprecated
     */
    public function setWebmExtension($webmExtension) //TODO delete
    {
        $this->webmExtension = $webmExtension;

        return $this;
    }

    /**
     * Get webmExtension
     *
     * @return string
     * @deprecated
     */
    public function getWebmExtension() //TODO delete
    {
        return $this->webmExtension;
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
     * Set filename
     *
     * @param string $filename
     * @return ResourceFile
     * @deprecated
     */
    public function setFilename($filename) //TODO delete. not used
    {
        $this->filename = $filename;

        return $this;
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

    /**
     * @return null|string
     * @deprecated
     */
    public function getWebPathWebm() //TODO delete
    {
        return null === $this->path
            ? null
            : static::UPLOAD_DIR . '/' . $this->id . '.' . $this->getWebmExtension();
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
        //$this->tempWebm = $this->getAbsolutePathWebm();

    }

    public function removeUpload()
    {
        if (file_exists($this->temp)) {
            unlink($this->temp);
        }
        /*if (file_exists($this->tempWebm)) {
            unlink($this->tempWebm);
        }*/
    }

    /**
     * @return null|string
     * @deprecated
     */
    public function getAbsolutePathWebm() //TODO delete
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->id . '.' . $this->getWebmExtension();
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire
     */
    public function refreshUpdated()
    {
        $this->setUpdated(new \DateTime('NOW'));
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ResourceFile
     * @deprecated
     */
    public function setName($name) //TODO delete. not used
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     * @deprecated
     */
    public function getName() //TODO delete. not used
    {
        return $this->name;
    }

    /**
     * Set media
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $media
     * @return ResourceFile
     * @deprecated
     */
    public function setMedia(\IMDC\TerpTubeBundle\Entity\Media $media = null) //TODO delete. not used
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media
     * @deprecated
     */
    public function getMedia() //TODO delete. not used
    {
        return $this->media;
    }

    /**
     * String description of a resource file
     */
    public function __toString()
    {
        return $this->getAbsolutePath();
    }
}
