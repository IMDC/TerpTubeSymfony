<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Interpretation extends Media
{
    /**
     * @var string
     */
    private $sourceStartTime;

    /**
     * @var \IMDC\TerpTubeBundle\Entity\Media
     */
    private $source;


    /**
     * Set sourceStartTime
     *
     * @param string $sourceStartTime
     * @return Interpretation
     */
    public function setSourceStartTime($sourceStartTime)
    {
        $this->sourceStartTime = $sourceStartTime;

        return $this;
    }

    /**
     * Get sourceStartTime
     *
     * @return string 
     */
    public function getSourceStartTime()
    {
        return $this->sourceStartTime;
    }

    /**
     * Set source
     *
     * @param \IMDC\TerpTubeBundle\Entity\Media $source
     * @return Interpretation
     */
    public function setSource(\IMDC\TerpTubeBundle\Entity\Media $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \IMDC\TerpTubeBundle\Entity\Media 
     */
    public function getSource()
    {
        return $this->source;
    }

    public function isInterpretation()
    {
        return true;
    }
}
