<?php

namespace IMDC\TerpTubeBundle\Component\Serializer;

use IMDC\TerpTubeBundle\Component\Serializer\Exclusion\UserExclusionStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer as JMSSerializer;

class Serializer extends JMSSerializer
{
    public function serialize($data, $format, SerializationContext $context = null)
    {
        if (null === $context) {
            $context = new SerializationContext();
            $context->addExclusionStrategy(new UserExclusionStrategy());
        }

        return parent::serialize($data, $format, $context);
    }
}
