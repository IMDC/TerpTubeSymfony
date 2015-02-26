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
        
        return min(convertPHPSizeToBytes($postmax), convertPHPSizeToBytes($uploadmax));
    }
    
    private function convertPHPSizeToBytes($sSize)
    {
    	if ( is_numeric( $sSize) ) {
    		return $sSize;
    	}
    	$sSuffix = substr($sSize, -1);
    	$iValue = substr($sSize, 0, -1);
    	switch(strtoupper($sSuffix)){
    		case 'P':
    			$iValue *= 1024;
    		case 'T':
    			$iValue *= 1024;
    		case 'G':
    			$iValue *= 1024;
    		case 'M':
    			$iValue *= 1024;
    		case 'K':
    			$iValue *= 1024;
    			break;
    	}
    	return $iValue;
    }
    

    public function getName()
    {
        return 'PHPMaxUploadSizeHelper';
    }

}