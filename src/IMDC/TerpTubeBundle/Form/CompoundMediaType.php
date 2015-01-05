<?php

namespace IMDC\TerpTubeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CompoundMediaType
 * @package IMDC\TerpTubeBundle\Form
 * @deprecated
 */
class CompoundMediaType extends AbstractType //TODO delete
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('targetStartTime')
            ->add('sourceID')
            ->add('targetID')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\CompoundMedia'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'imdc_terptubebundle_compoundmedia';
    }
}
