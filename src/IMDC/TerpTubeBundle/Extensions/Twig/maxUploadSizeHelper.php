<?php

namespace IMDC\TerpTubeBundle\Extensions\Twig;

class maxUploadSizeHelper extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
                'getPHPMaxUploadSize'  => new \Twig_Function_Method($this, 'getPHPMaxUploadSize'),
        );
    }


    public function getPHPMaxUploadSize()
    {
        $postmax   = ini_get('post_max_size');
        $uploadmax = ini_get('upload_max_filesize');
        
        return max($postmax, $uploadmax);
        
    }

    public function getName()
    {
        return 'PHPMaxUploadSizeHelper';
    }

}