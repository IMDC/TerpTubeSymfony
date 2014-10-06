<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use IMDC\TerpTubeBundle\Form\DataTransformer\MediaToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ForumFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
	{
        //TODO move to own form type
        $em = $options['em'];
        $transformer = new MediaToIdTransformer($em);

        $builder->add(
            $builder
                ->create('mediatextarea', 'hidden', array(
                    'mapped' => false))
                ->addModelTransformer($transformer)
        );

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
        $resolver
            ->setDefaults(array(
                'data_class' => 'IMDC\TerpTubeBundle\Entity\Forum'))
            ->setRequired(array(
                'em'))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager'));
    }

	public function getName()
	{
		return 'ForumForm';
	}
}
