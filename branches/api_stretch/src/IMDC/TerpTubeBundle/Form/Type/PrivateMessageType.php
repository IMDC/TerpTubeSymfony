<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use IMDC\TerpTubeBundle\Form\DataTransformer\UsersToStringsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Count;
use IMDC\TerpTubeBundle\Validator\Constraints\UserExists;

class PrivateMessageType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$entityManager = $options ['em'];
		$transformer = new UsersToStringsTransformer ( $entityManager );
		
		$builder->add ( 'attachedMedia', 'media_chooser' );
		
		$builder->add ( $builder->create ( 'recipients', 'text', array (
				'label' => 'Recipients (comma separated list of usernames)',
				'constraints' => array (
						new Count ( array (
								'min' => 1,
								'max' => 999,
								'minMessage' => 'At least {{ limit }} recipient must be specified.',
								'maxMessage' => 'At most {{ limit }} recipients can be specified.' 
						) ),
						new UserExists() 
				) 
		) )->addModelTransformer ( $transformer ) );//->addEventSubscriber (new UserValidMessageListener ( $entityManager ) );
		
		$builder->add ( 'subject', 'text', array (
				'required' => true 
		) );
		
		$builder->add ( 'content', 'textarea', array (
				'attr' => array (
						'class' => 'autosize' 
				) 
		) );
	}
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults ( array (
				'data_class' => 'IMDC\TerpTubeBundle\Entity\Message' 
		) )->setRequired ( array (
				'em' 
		) )->setAllowedTypes ( array (
				'em' => 'Doctrine\Common\Persistence\ObjectManager' 
		) );
	}
	public function getName()
	{
		return 'PrivateMessageForm';
	}
}