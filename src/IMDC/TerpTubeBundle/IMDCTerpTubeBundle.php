<?php

namespace IMDC\TerpTubeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class IMDCTerpTubeBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
