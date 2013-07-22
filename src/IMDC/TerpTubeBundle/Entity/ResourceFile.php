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
     * @var \IMDC\TerpTubeBundle\Entity\User
     */
    private $owner;


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
     * Set owner
     *
     * @param \IMDC\TerpTubeBundle\Entity\User $owner
     * @return ResourceFile
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
}