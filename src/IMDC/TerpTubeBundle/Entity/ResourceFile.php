<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResourceFile
 */
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
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $resource;


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
     * Set resource
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $resource
     * @return ResourceFile
     */
    public function setResource(\IMDC\TerpTubeBundle\Entity\Media $resource = null)
    {
        $this->resource = $resource;
    
        return $this;
    }

    /**
     * Get resource
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getResource()
    {
        return $this->resource;
    }
}