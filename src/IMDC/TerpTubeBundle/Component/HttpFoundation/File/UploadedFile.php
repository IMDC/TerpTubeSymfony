<?php

namespace IMDC\TerpTubeBundle\Component\HttpFoundation\File;

use IMDC\TerpTubeBundle\Utils\Utils;
use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;

class UploadedFile extends BaseUploadedFile
{
    public static function fromUploadedFile(BaseUploadedFile $uploadedFile)
    {
        $class = new \ReflectionClass($uploadedFile);
        $property = $class->getProperty("test");
        $property->setAccessible(true);

        return new static(
            $uploadedFile->getPathname(),
            $uploadedFile->getClientOriginalName(),
            $uploadedFile->getClientMimeType(),
            $uploadedFile->getClientSize(),
            $uploadedFile->getError(),
            $property->getValue($uploadedFile)
        );
    }

    public function getMimeType()
    {
        return Utils::getMimeType($this->getRealPath());
    }
}
