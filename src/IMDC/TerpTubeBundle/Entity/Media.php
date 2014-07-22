<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\Common\EventManager;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 */
class Media
{
	const TYPE_IMAGE = 0;
	const TYPE_VIDEO = 1;
	const TYPE_AUDIO = 2;
	const TYPE_OTHER = 9;
	
	const READY_NO = 0;
	const READY_WEBM = 1;
	const READY_MPEG = 2;
	const READY_YES = 3;
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
     * @var \IMDC\TerpTubeBundle\Entity\ResourceFile
     */
    private $resource;
    
    /**
     * @var string
     */
    private $title;
    
    /**
     * @var integer
     */
    private $isReady = 0;

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
    
    public function __toString()
    {
        return (string) $this->getTitle();
        
//         switch ($this->type) {
//             case 0: // image
//                 return $this->resource->getWebPath();
//             break;
            
//             case 1: // video
//                 return $this->resource->getWebPathWebm();
//             break;

//             case 2: // audio
//                 return $this->resource->getWebPath();
//             break;
            
//             default:
//                 return $this->resource->getWebPath();
//             break;
//         }
    }
    /**
     * @var array
     */
    private $pendingOperations;


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
}
