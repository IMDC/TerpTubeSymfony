<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserGroupManageSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $builder->add('mentors', 'checkbox', array(
            'label' => false,
            'required' => false
        ));

        $builder->add('mentees', 'checkbox', array(
            'label' => false,
            'required' => false
        ));

        $builder->add('friends', 'checkbox', array(
            'label' => false,
            'required' => false
        ));

        $builder->add('username', 'text', array(
            'label' => false,
            'required' => false,
            'attr' => array(
                'placeholder' => 'Search by username'
            )
        ));
    }

    public function getName()
    {
        return 'ugm_search';
    }
}
