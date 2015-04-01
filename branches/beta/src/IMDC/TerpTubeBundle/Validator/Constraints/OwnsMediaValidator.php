<?php

namespace IMDC\TerpTubeBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OwnsMediaValidator extends ConstraintValidator
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        $collection = !is_array($value)
            ? array($value)
            : $value;
        $user = $this->securityContext->getToken()->getUser();

        foreach ($collection as $media) {
            if (!$user->getResourceFiles()->contains($media)) {
                $this->context->addViolation(
                    $constraint->message,
                    array(
                        '%user_name%' => $user->getUsername(),
                        '%media_title%' => $media->getTitle()
                    )
                );
            }
        }
    }
}
