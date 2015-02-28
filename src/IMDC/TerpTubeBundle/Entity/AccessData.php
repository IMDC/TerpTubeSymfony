<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessData
 */
class AccessData
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $objectIdentifier;

    /**
     * @var array
     */
    private $data;


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
     * Set objectIdentifier
     *
     * @param string $objectIdentifier
     * @return AccessData
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;

        return $this;
    }

    /**
     * Get objectIdentifier
     *
     * @return string 
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return AccessData
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }
}
