<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaCollectionToIntArrayTransformer;
use IMDC\TerpTubeBundle\Form\DataTransformer\MediaToIntTransformer;
use IMDC\TerpTubeBundle\Validator\Constraints\OwnsMedia;
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
        //$builder->addModelTransformer(new MediaToIntTransformer($this->entityManager));
        $builder->addModelTransformer(new MediaCollectionToIntArrayTransformer($this->entityManager));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_file_select'] = $options['allow_file_select'];
        $view->vars['section'] = $options['section'];
        $view->vars['uploadForm'] = $this->formFactory->create(new MediaType())->createView();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'allow_file_select' => true,
            'section' => 'default', // determines title/alt/text translations to use for the given section
            'allow_add' => true,
            'allow_delete' => true,
            'type' => 'hidden',
            //'mapped' => false,
            'required' => false,
            'constraints' => array(
                new OwnsMedia()
            )
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
