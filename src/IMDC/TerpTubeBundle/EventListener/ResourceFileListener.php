<?php

namespace IMDC\TerpTubeBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use IMDC\TerpTubeBundle\Entity\ResourceFile;

class ResourceFileListener
{
    /**
     * @var array
     */
    private $resourceFileConfig;

    public function __construct($resourceFileConfig)
    {
        $this->resourceFileConfig = $resourceFileConfig;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ResourceFile) {
            $entity->setWebRootPath($this->resourceFileConfig['web_root_path']);
            $entity->setUploadPath($this->resourceFileConfig['upload_path']);
        }
    }
}
