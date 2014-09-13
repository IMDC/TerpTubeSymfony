<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvitationType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creator')
            ->add('recipient')
            ->add('dateCreated')
            ->add('isAccepted')
            ->add('dateAccepted')
            ->add('isCancelled')
            ->add('dateCancelled')
            ->add('isDeclined')
            ->add('dateDeclined')
            //->add('becomeMentor')
            //->add('becomeMentee')
            ->add('type')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\Invitation'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'imdc_terptubebundle_invitation';
    }
}
