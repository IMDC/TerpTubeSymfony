<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ForumFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $user = $options['user'];

        $builder->add('mediatextarea', 'media_chooser');

	    $builder->add('titleText', 'text', array(
            'label' => 'Title'
        ));

        $builder->add('accessType', 'access_type', array(
            'class' => 'IMDC\TerpTubeBundle\Entity\Forum'
        ));

        $queryBuilder = function (EntityRepository $repo) use ($user) {
            //TODO filter all groups by ace instead of founder. user may not be founder of other groups, but may have an owner ace
            return $repo->createQueryBuilder('g')
                ->leftJoin('g.userFounder', 'u')
                ->where('u.id = :userId')
                ->setParameter('userId', $user->getId());
        };
        $attr = array('style' => 'display: none;');
        $builder->add('group', 'entity', array(
            'class' => 'IMDCTerpTubeBundle:UserGroup',
            'query_builder' => $queryBuilder,
            'empty_value' => 'Choose a Group',
            'label' => 'My Groups',
            'label_attr' => $attr,
            'required' => false,
            'attr' => $attr
        ));
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'IMDC\TerpTubeBundle\Entity\Forum'))
            ->setRequired(array(
                'user'))
            ->setAllowedTypes(array(
                'user' => 'Symfony\Component\Security\Core\User\UserInterface'));
    }

	public function getName()
	{
		return 'ForumForm';
	}
}
