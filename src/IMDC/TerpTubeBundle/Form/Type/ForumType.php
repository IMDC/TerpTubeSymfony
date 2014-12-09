<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ForumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $user = $options['user'];
        $group = $options['group'];

        $builder->add('titleMedia', 'media_chooser');

	    $builder->add('titleText', 'text', array(
            'label' => 'Title'
        ));

        $builder->add('accessType', 'access_type', array(
            'class' => 'IMDC\TerpTubeBundle\Entity\Forum'
        ));

        $queryBuilder = function (EntityRepository $repo) use ($user, $group) {
            //TODO filter all groups by ace instead of founder. user may not be founder of other groups, but may have an owner ace
            $membersCanAddForums = $group && $group->getMembersCanAddForums();
            $qb = $repo->createQueryBuilder('g')
                ->leftJoin('g.userFounder', 'u');

            if ($membersCanAddForums)
                $qb->leftJoin('g.members', 'm');

            $qb->where('u.id = :userId')
                ->setParameter('userId', $user->getId());

            if ($membersCanAddForums) {
                $qb->orWhere($qb->expr()->in('m.id', array(
                    $user->getId()
                )));
            }

            return $qb;
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
                'user',
                'group'))
            ->setAllowedTypes(array(
                'user' => 'Symfony\Component\Security\Core\User\UserInterface',
                'group' => 'IMDC\TerpTubeBundle\Entity\UserGroup'));
    }

	public function getName()
	{
		return 'forum';
	}
}
