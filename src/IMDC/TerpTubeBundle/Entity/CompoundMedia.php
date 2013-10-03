<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompoundMedia
 */
class CompoundMedia
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $type;


    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $sourceID;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $targetID;


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
     * @return CompoundMedia
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
     * Set sourceID
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $sourceID
     * @return CompoundMedia
     */
    public function setSourceID(\IMDC\TerpTubeBundle\Entity\Media $sourceID = null)
    {
        $this->sourceID = $sourceID;
    
        return $this;
    }

    /**
     * Get sourceID
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getSourceID()
    {
        return $this->sourceID;
    }

    /**
     * Set targetID
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $targetID
     * @return CompoundMedia
     */
    public function setTargetID(\IMDC\TerpTubeBundle\Entity\Media $targetID = null)
    {
        $this->targetID = $targetID;
    
        return $this;
    }

    /**
     * Get targetID
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getTargetID()
    {
        return $this->targetID;
    }
    /**
     * @var integer
     */
    private $targetStartTime;


    /**
     * Set targetStartTime
     *
     * @param integer $targetStartTime
     * @return CompoundMedia
     */
    public function setTargetStartTime($targetStartTime)
    {
        $this->targetStartTime = $targetStartTime;
    
        return $this;
    }

    /**
     * Get targetStartTime
     *
     * @return integer 
     */
    public function getTargetStartTime()
    {
        return $this->targetStartTime;
    }
}