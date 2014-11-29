<?php 

namespace IMDC\TerpTubeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class ThreadType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext = null)
    {
        $this->securityContext = $securityContext;
    }

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        if ($options['canChooseForum']) {
            $user = $options['user'];
            $em = $options['em'];

            if ($this->securityContext === null || $user === null || $em === null) {
                throw new \InvalidArgumentException('securityContext/user/em cannot be null');
            }

            //TODO change to get forums where the user has permission to create threads
            $forums = $em->getRepository('IMDCTerpTubeBundle:Forum')->getViewableToUser($this->securityContext, $user);

            $builder->add('forum', 'entity', array(
                'class' => 'IMDCTerpTubeBundle:Forum',
                'choices' => $forums,
                'empty_value' => 'Choose a Forum',
                'label' => 'Forum',
                'mapped' => false
            ));
        }

        if ($options['canChooseMedia']) {
            $builder->add('mediaIncluded', 'media_chooser');
        }

        $builder->add('title', 'text', array(
            'label' => 'Text Title'
        ));

	    $builder->add('content', 'textarea', array(
            'label' => 'Supplemental Text Description',
            'required' => false,
            'attr' => array(
                'class' => 'autosize')
	    ));

        $builder->add('accessType', 'access_type', array(
            'class' => 'IMDC\TerpTubeBundle\Entity\Thread'
        ));
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'canChooseMedia' => true,
                'canChooseForum' => false,
                'data_class' => 'IMDC\TerpTubeBundle\Entity\Thread'))
            ->setOptional(array(
                'user',
                'em'))
            ->setAllowedTypes(array(
                'user' => 'Symfony\Component\Security\Core\User\UserInterface',
                'em' => 'Doctrine\Common\Persistence\ObjectManager'));
    }

	public function getName()
	{
		return 'thread';
	}
}
