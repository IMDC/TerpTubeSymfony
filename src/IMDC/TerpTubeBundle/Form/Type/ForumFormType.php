<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ForumFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $builder->add('mediatextarea', 'media');

	    $builder->add('titleText', 'text', array(
            'label' => 'Title'
        ));

        $builder->add('accessType', 'access_type', array(
            'class' => 'IMDC\TerpTubeBundle\Entity\Forum'
        ));

        $attr = array('style' => 'display: none;');
        $builder->add('group', 'entity', array(
            'class' => 'IMDCTerpTubeBundle:UserGroup',
            'empty_value' => 'Choose a Group',
            'required' => false,
            'label_attr' => $attr,
            'attr' => $attr
        ));
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\Forum'
        ));
    }

	public function getName()
	{
		return 'ForumForm';
	}
}
