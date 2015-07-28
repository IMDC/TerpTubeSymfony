<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityStatus
 */
class EntityStatus
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $who;

    /**
     * @var string
     */
    private $what;

    /**
     * @var string
     */
    private $timestamp;


    /**
     * Set identifier
     *
     * @param string $identifier
     * @return EntityStatus
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string 
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return EntityStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set who
     *
     * @param string $who
     * @return EntityStatus
     */
    public function setWho($who)
    {
        $this->who = $who;

        return $this;
    }

    /**
     * Get who
     *
     * @return string 
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * Set what
     *
     * @param string $what
     * @return EntityStatus
     */
    public function setWhat($what)
    {
        $this->what = $what;

        return $this;
    }

    /**
     * Get what
     *
     * @return string 
     */
    public function getWhat()
    {
        return $this->what;
    }

    /**
     * Set timestamp
     *
     * @param string $timestamp
     * @return EntityStatus
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return string 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
