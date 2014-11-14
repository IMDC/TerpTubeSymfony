<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaCollectionToIdArrayTransformer;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MediaChooserType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class MediaChooserType extends AbstractType
{
    private $entityManager;
    private $formFactory;

    public function __construct(EntityManager $entityManager, FormFactory $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->addModelTransformer(new MediaToIdTransformer($this->entityManager));
        $builder->addModelTransformer(new MediaCollectionToIdArrayTransformer($this->entityManager));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['uploadForm'] = $this->formFactory->create(new MediaType())->createView();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'allow_add' => true,
            'allow_delete' => true,
            'type' => 'hidden',
            'mapped' => false,
            'required' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media_chooser';
    }
}
