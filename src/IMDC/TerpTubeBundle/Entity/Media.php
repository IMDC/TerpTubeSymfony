<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 */
class Media
{
	const TYPE_IMAGE = 0;
	const TYPE_VIDEO = 1;
	const TYPE_AUDIO = 2;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\MetaData
     */
    private $metaData;

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
     * @var \IMDC\TerpTubeBundle\Entity\ResourceFile
     */
    private $resourceObject;


    /**
     * Set resourceObject
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $resourceObject
     * @return Media
     */
    public function setResourceObject(\IMDC\TerpTubeBundle\Entity\ResourceFile $resourceObject = null)
    {
        $this->resourceObject = $resourceObject;
    
        return $this;
    }
    

    /**
     * Get resourceObject
     *
     * @return \IMDC\TerpTubeBundle\Entity\ResourceFile 
     */
    public function getResourceObject()
    {
        return $this->resourceObject;
    }
    /**
     * @var \IMDC\TerpTubeBundle\Entity\ResourceFile
     */
    private $resource;


    /**
     * Set resource
     *
     * @param \IMDC\TerpTubeBundle\Entity\ResourceFile $resource
     * @return Media
     */
    public function setResource(\IMDC\TerpTubeBundle\Entity\ResourceFile $resource = null)
    {
        $this->resource = $resource;
    
        return $this;
    }

    /**
     * Get resource
     *
     * @return \IMDC\TerpTubeBundle\Entity\ResourceFile 
     */
    public function getResource()
    {
        return $this->resource;
    }
}