<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Language
 */
class Language
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $proficiency;


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
     * @param string $type
     * @return Language
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set proficiency
     *
     * @param integer $proficiency
     * @return Language
     */
    public function setProficiency($proficiency)
    {
        $this->proficiency = $proficiency;
    
        return $this;
    }

    /**
     * Get proficiency
     *
     * @return integer 
     */
    public function getProficiency()
    {
        return $this->proficiency;
    }
}