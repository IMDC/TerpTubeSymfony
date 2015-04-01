<?php

namespace IMDC\TerpTubeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class OwnsMedia extends Constraint
{
    public $message = 'User \'%user_name%\' does not own media \'%media_title%\'.';

    public function validatedBy()
    {
        return 'owns_media';
    }
}
