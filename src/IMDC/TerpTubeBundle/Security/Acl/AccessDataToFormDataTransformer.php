<?php

namespace IMDC\TerpTubeBundle\Security\Acl;

use IMDC\TerpTubeBundle\Entity\AccessData;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Form\DataTransformer\UserCollectionToIntArrayTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;

class AccessDataToFormDataTransformer implements DataTransformerInterface
{
    private $accessType;
    private $om;
    private $accessData;

    public function __construct(AccessType $accessType, ObjectManager $om, AccessData $accessData = null)
    {
        $this->accessType = $accessType;
        $this->om = $om;
        $this->accessData = $accessData;
    }

    /**
     * @param AccessData $accessData
     * @return array
     */
    public function transform($accessData)
    {
        $data = array();

        if (empty($accessData)) {
            return $data;
        }

        if ($this->accessType->getId() == AccessType::TYPE_USERS) {
            $transformer = new UserCollectionToIntArrayTransformer($this->om);
            $data['users'] = $transformer->reverseTransform($accessData->getData());
        }

        return $data;
    }

    /**
     * @param array|FormInterface $data
     * @return AccessData
     */
    public function reverseTransform($data)
    {
        if (empty($this->accessData)) {
            throw new \InvalidArgumentException('$accessData cannot be empty.');
        }

        if (empty($data)) {
            return $this->accessData;
        }

        $dataArray = array();
        if ($data instanceof FormInterface) {
            $dataArray['users'] = $data->get('users')->getData();
        }

        if ($this->accessType->getId() == AccessType::TYPE_USERS) {
            $transformer = new UserCollectionToIntArrayTransformer($this->om);
            $this->accessData->setData($transformer->transform($dataArray['users']));
        } else {
            $this->accessData->setData(array()); // no data to set so clear it
        }

        return $this->accessData;
    }
}
