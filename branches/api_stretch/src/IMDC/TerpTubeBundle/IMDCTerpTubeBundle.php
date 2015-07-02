<?php

namespace IMDC\TerpTubeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IMDCTerpTubeBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
