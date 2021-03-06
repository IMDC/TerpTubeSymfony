<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MetaData
 */
class MetaData
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $size;
    
    /**
     * @var integer
     */
    private $width;
    
    /**
     * @var integer
     */
    private $height;
    
    /**
     * @var double
     */
    private $duration;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->size = -1;
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
     * Set size
     *
     * @param integer $size
     * @return MetaData
     */
    public function setSize($size)
    {
        $this->size = $size;
    
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }
    


    /**
     * Set width
     *
     * @param integer $width
     * @return MetaData
     */
    public function setWidth($width)
    {
        $this->width = $width;
    
        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return MetaData
     */
    public function setHeight($height)
    {
        $this->height = $height;
    
        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set duration
     *
     * @param double $duration
     * @return MetaData
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    
        return $this;
    }

    /**
     * Get duration
     *
     * @return double 
     */
    public function getDuration()
    {
        return $this->duration;
    }
    
    public function __toString() 
    {
        return 'id: ' . $this->id;
    }
}
