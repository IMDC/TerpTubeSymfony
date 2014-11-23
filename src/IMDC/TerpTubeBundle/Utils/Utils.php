<?php

namespace IMDC\TerpTubeBundle\Utils;

class Utils
{
	public static function delTree($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file)
		{
			(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
	
	public static function getPHPMaxUploadSize()
	{
		$postmax   = ini_get('post_max_size');
		$uploadmax = ini_get('upload_max_filesize');
	
		return min(Utils::convertPHPSizeToBytes($postmax), Utils::convertPHPSizeToBytes($uploadmax));
	}
	
	private static function convertPHPSizeToBytes($sSize)
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

    public static function orderMedia($mediaCollection, array $displayOrder)
    {
        if (empty($displayOrder)) {
            return $mediaCollection;
        }

        $ordered = array();
        foreach ($mediaCollection as $media) {
            foreach ($displayOrder as $index => $mediaId) {
                if ($media->getId() == $mediaId) {
                    $ordered[$index] = $media;
                    break;
                }
            }
        }
        ksort($ordered);

        return $ordered;
    }
}
