<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MediaType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MediaType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new MediaToIdTransformer($this->entityManager));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'mapped' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media';
    }
}