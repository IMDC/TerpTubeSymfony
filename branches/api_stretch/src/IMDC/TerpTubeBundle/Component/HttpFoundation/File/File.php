<?php

namespace IMDC\TerpTubeBundle\Component\HttpFoundation\File;

use IMDC\TerpTubeBundle\Utils\Utils;
use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File extends BaseFile
{
    public static function fromFile(BaseFile $file)
    {
        return new static($file->getPathname());
    }

    public function getMimeType()
    {
        return Utils::getMimeType($this->getRealPath());
    }
}
