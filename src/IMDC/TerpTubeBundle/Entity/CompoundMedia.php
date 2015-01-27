<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompoundMedia
 * @deprecated
 */
class CompoundMedia //TODO delete
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
    private $source;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $target;

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
     * @param \IMDC\TerpTubeBundle\Entity\Media $source
     * @return CompoundMedia
     */
    public function setSource(\IMDC\TerpTubeBundle\Entity\Media $source = null)
    {
        $this->source = $source;
    
        return $this;
    }

    /**
     * Get sourceID
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set targetID
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $targetID
     * @return CompoundMedia
     */
    public function setTarget(\IMDC\TerpTubeBundle\Entity\Media $target = null)
    {
        $this->target = $target;
    
        return $this;
    }

    /**
     * Get targetID
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getTarget()
    {
        return $this->target;
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
