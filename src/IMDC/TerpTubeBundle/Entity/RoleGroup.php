<?php
// src/IMDC/TerpTubeBundle/Entity/RoleGroup.php

namespace IMDC\TerpTubeBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;

class RoleGroup extends BaseGroup
{
    
    /**
     * @var integer
     */
    protected $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
