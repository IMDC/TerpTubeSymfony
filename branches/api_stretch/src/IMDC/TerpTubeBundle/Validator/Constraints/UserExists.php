<?php

namespace IMDC\TerpTubeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class UserExists extends Constraint
{
    public $singleMessage = 'user.exists.single';
    
    public $multipleMessage = 'user.exists.multiple';
    
    public $propertyPath = null;

    public function validatedBy()
    {
        return 'user_exists';
    }
}
