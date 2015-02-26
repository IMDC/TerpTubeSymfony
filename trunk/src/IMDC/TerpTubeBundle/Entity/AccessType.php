<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AccessType
 * @package IMDC\TerpTubeBundle\Entity
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessType
{
    const TYPE_PUBLIC = 1;
    const TYPE_LINK_ONLY = 2;
    const TYPE_REGISTERED_USERS = 3;
    const TYPE_USERS = 4;
    const TYPE_FRIENDS = 5;
    const TYPE_GROUP = 6;
    const TYPE_PRIVATE = 7;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * Set id
     *
     * @param integer $id
     * @return AccessType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set description
     *
     * @param string $description
     * @return AccessType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->getDescription();
    }
}
