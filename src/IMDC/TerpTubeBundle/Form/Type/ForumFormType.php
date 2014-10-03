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
        $em = $options['em'];
        $transformer = new MediaToIdTransformer($em);
        $groupId = $options['groupId'];

        $builder->add(
            $builder
                ->create('mediatextarea', 'hidden', array(
                    'mapped' => false))
                ->addModelTransformer($transformer)
        );

	    $builder->add('titleText', 'text', array(
            'label' => 'Title'
        ));

        $options = array(
            'class' => 'IMDCTerpTubeBundle:UserGroup',
            'empty_value' => 'Non-Group Associated Forum',
            'required' => false
        );

        if ($groupId) {
            $group = $em->getRepository('IMDCTerpTubeBundle:UserGroup')->find($groupId);
            if ($group) {
                $options['data'] = $em->getReference('IMDCTerpTubeBundle:UserGroup', $group->getId());
            }
        }

        $builder->add('group', 'entity', $options);
	}

	public function getName()
	{
		return 'ForumForm';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver
            ->setDefaults(array(
                'data_class' => 'IMDC\TerpTubeBundle\Entity\Forum',
                'groupId' => null))
            ->setRequired(array(
                'em'))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager'));
	}
}
