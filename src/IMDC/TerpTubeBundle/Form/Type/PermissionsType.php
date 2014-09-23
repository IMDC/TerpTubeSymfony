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
    private $permissions;
    
    public function __construct(Array $options)
    {
        $this->user = $options['user'];
        if (isset($options['permissions']))
            $this->permissions = $options['permissions'];
        
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        if ($this->permissions) {
            $builder->add('accessLevel', 'choice', array(
                    'choices' => array(Permissions::ACCESS_CREATOR => 'Private, only you can see it',
                                        //Permissions::ACCESS_CREATORS_FRIENDS => 'Members of your friends list',
                                        Permissions::ACCESS_WITH_LINK => 'Anyone with the link (unlisted)',
                                        Permissions::ACCESS_USER_LIST => 'Select specific users with access',
                                        Permissions::ACCESS_GROUP_LIST => 'Select specific groups with access',
                                        Permissions::ACCESS_REGISTERED_MEMBERS => 'Only registered members of the site',
                                        Permissions::ACCESS_PUBLIC => 'Public, anyone can see it'),
                    'multiple' => false,
                    'expanded' => true,
                    'data' => $this->permissions->getAccessLevel() // default choice
            ));
            $builder->add('userGroupsWithAccess', 'entity', array(
                'class' => 'IMDCTerpTubeBundle:UserGroup',
                'choices' => $this->permissions->getUserGroupsWithAccess(),
                'multiple' => true,
                'required' => false,
            ));
            $builder->add('usersWithAccess', 'text', array(
                'mapped' => false,
                'required' => false,
                'data' => $this->permissions->getUsersWithAccessAsUsernameString(),
            ));
        }
        else {
            $builder->add('accessLevel', 'choice', array(
                'choices' => array(Permissions::ACCESS_CREATOR => 'Private, only you can see it',
                    //Permissions::ACCESS_CREATORS_FRIENDS => 'Members of your friends list',
                    Permissions::ACCESS_WITH_LINK => 'Anyone with the link (unlisted)',
                    Permissions::ACCESS_USER_LIST => 'Select specific users with access',
                    Permissions::ACCESS_GROUP_LIST => 'Select specific groups with access',
                    Permissions::ACCESS_REGISTERED_MEMBERS => 'Only registered members of the site',
                    Permissions::ACCESS_PUBLIC => 'Public, anyone can see it'),
                'multiple' => false,
                'expanded' => true,
                'data' => Permissions::ACCESS_PUBLIC // default choice
            ));
            $builder->add('userGroupsWithAccess', 'entity', array(
                'class' => 'IMDCTerpTubeBundle:UserGroup',
                'choices' => $this->user->getUserGroups(),
                'multiple' => true,
                'required' => false,
                'attr' => array(
                    'style' => 'display: none;'
                )
            ));
            $builder->add('usersWithAccess', 'text', array(
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'style' => 'display: none;'
                )
            ));
        }
//             ->add('userFriendsWithAccess', 'entity', array(
//                 'class' => 'IMDCTerpTubeBundle:User',
//                 'choices' => $this->user->getFriendsList(),
//                 'multiple' => true,
//                 'required' => false,
//                 'mapped' => false,
//                  'attr' => array(
//                      'style' => 'display: none;'
//                  )
//             ))
            
            
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
