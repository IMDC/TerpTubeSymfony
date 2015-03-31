<?php

namespace IMDC\TerpTubeBundle\Component\HttpFoundation\File;

use Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;

class UploadedFile extends BaseUploadedFile
{
    private $mimeType;

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

    /**
     * Use 'file' command every time
     * @return string|void
     */
    public function getMimeType()
    {
        if ($this->mimeType === null) {
            $guesser = new FileBinaryMimeTypeGuesser();
            $this->mimeType = $guesser->guess($this->getRealPath());
        }

        return $this->mimeType;
    }
}
