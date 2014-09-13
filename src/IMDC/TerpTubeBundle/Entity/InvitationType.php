<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvitationType
 */
class InvitationType
{
    const TYPE_MENTOR = 1;
    const TYPE_MENTEE = 2;
    const TYPE_GROUP = 3;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;


    /**
     * Set id
     *
     * @param integer $id
     * @return InvitationType
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
     * Set name
     *
     * @param string $name
     * @return InvitationType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get name
     *
     * @return boolean
     */
    public function isMentor()
    {
        return $this->id == InvitationType::TYPE_MENTOR;
    }

    /**
     * Get name
     *
     * @return boolean
     */
    public function isMentee()
    {
        return $this->id == InvitationType::TYPE_MENTEE;
    }

    /**
     * Get name
     *
     * @return boolean
     */
    public function isGroup()
    {
        return $this->id == InvitationType::TYPE_GROUP;
    }
}
