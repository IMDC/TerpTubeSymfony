<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use IMDC\TerpTubeBundle\Entity\User;
use IMDC\TerpTubeBundle\Entity\Permissions;

class PermissionsType extends AbstractType
{
    
    private $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder
            ->add('accessLevel', 'choice', array(
                'choices' => array(Permissions::ACCESS_CREATOR => 'Private, only you can see it',
                                    //Permissions::ACCESS_CREATORS_FRIENDS => 'Members of your friends list',
                                    Permissions::ACCESS_WITH_LINK => 'Anyone with the link (unlisted)',
                                    Permissions::ACCESS_USER_LIST => 'Select specific users with access',
                                    Permissions::ACCESS_GROUP_LIST => 'Select specific groups with access',
                                    Permissions::ACCESS_REGISTERED_MEMBERS => 'Only registered members of the site',
                                    Permissions::ACCESS_PUBLIC => 'Public, anyone can see it'),
                'multiple' => false,
                'expanded' => true,
                'data' => Permissions::ACCESS_PUBLIC
            ))
//             ->add('userFriendsWithAccess', 'entity', array(
//                 'class' => 'IMDCTerpTubeBundle:User',
//                 'choices' => $this->user->getFriendsList(),
//                 'multiple' => true,
//                 'required' => false,
//                 'mapped' => false,
//             ))
            
            ->add('userGroupsWithAccess', 'entity', array(
                'class' => 'IMDCTerpTubeBundle:UserGroup',
                'choices' => $this->user->getUserGroups(),
                'multiple' => true,
                'required' => false,
            ))
            ->add('userListWithAccess', 'text', array(
                'mapped' => false,
                'required' => false,))
            ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IMDC\TerpTubeBundle\Entity\Permissions'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'imdc_terptubebundle_permissions';
    }
}
