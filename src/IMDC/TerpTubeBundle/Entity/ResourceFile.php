<?php

namespace IMDC\TerpTubeBundle\Entity;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Doctrine\Common\EventManager;

use Doctrine\ORM\Mapping as ORM;
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
    private $filename;


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
     * Set filename
     *
     * @param string $filename
     * @return ResourceFile
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }
    /**
     * @var string
     */
    private $path;


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
    
    public function getWebPath()
    {
    	return null === $this->path
    	? null
    	: $this->getUploadDir().'/'.$this->id.'.'.$this->path;
    }
    
    protected function getUploadRootDir()
    {
    	// the absolute directory path where uploaded
    	// documents should be saved
    	return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }
    
    protected function getUploadDir()
    {
    	// get rid of the __DIR__ so it doesn't screw up
    	// when displaying uploaded doc/image in the view.
    	return 'uploads/media';
    }
    
    private $file;
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
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
    	if (null !== $this->getFile()) {
    		$this->path = $this->getFile()->guessExtension();
    	}
    }
    
    /**
     * Dispatches an uploaded event after the file is uploaded and passes the object as an argument.
     */
    public function upload()
    {
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
    			$this->id.'.'.$this->getFile()->guessExtension()
    	);
    
    	$this->setFile(null);
    	
    }
    
    public function storeFilenameForRemove()
    {
    	$this->temp = $this->getAbsolutePath();
    }
    
    public function removeUpload()
    {
    	if (isset($this->temp)) {
    		unlink($this->temp);
    	}
    }
    
    public function getAbsolutePath()
    {
    	return null === $this->path
    	? null
    	: $this->getUploadRootDir().'/'.$this->id.'.'.$this->path;
    }
    /**
     * @var string
     */
    private $name;


    /**
     * Set name
     *
     * @param string $name
     * @return ResourceFile
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $media;


    /**
     * Set media
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $media
     * @return ResourceFile
     */
    public function setMedia(\IMDC\TerpTubeBundle\Entity\Media $media = null)
    {
        $this->media = $media;
    
        return $this;
    }

    /**
     * Get media
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getMedia()
    {
        return $this->media;
    }
}