<?php

namespace IMDC\TerpTubeBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use IMDC\TerpTubeBundle\Entity\AccessType;
use IMDC\TerpTubeBundle\Security\Acl\AccessChoiceList;
use IMDC\TerpTubeBundle\Security\Acl\AccessDataToFormDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AccessTypeType
 * @package IMDC\TerpTubeBundle\Form\Type
 * @author Jamal Edey <jamal.edey@ryerson.ca>
 */
class AccessTypeType extends AbstractType
{
    private $entityManager;
    private $securityContext;

    public function __construct(EntityManager $entityManager, SecurityContext $securityContext)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $accessData = $options['access_data'];
        $choiceList = AccessChoiceList::fromEntityManager($this->entityManager, $options['class'], $this->securityContext);

        if (count($choiceList->getChoices()) > 0) {
            $builder->add(
                $builder
                    ->create('type', 'choice', array(
                        'choice_list' => $choiceList,
                        'expanded' => true,
                        'label' => false))
                    ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                        $accessType = $event->getData();
                        if (!$accessType) {
                            $accessType = $this->entityManager->find('IMDCTerpTubeBundle:AccessType', AccessType::TYPE_PUBLIC);
                        }

                        $event->setData($accessType);
                    })
            );
        }

        $builder->add('data', new AccessDataType($this->entityManager), array(
            'label' => false
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($accessData) {
            // convert data to into this form's data
            $accessType = $event->getData();
            if ($accessType instanceof AccessType) {
                $transformer = new AccessDataToFormDataTransformer($accessType, $this->entityManager);
                $data = $transformer->transform($accessData);

                $event->setData(array(
                    'type' => $accessType, // access type
                    'data' => $data
                ));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            // set data to what the parent form expects after this form's data has been set (FormEvents::PRE_SUBMIT)
            $event->setData($event->getForm()->get('type')->getData()); // access type
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'access_data' => null))
            ->setRequired(array(
                'class'))
            ->setAllowedTypes(array(
                'access_data' => array('null', 'IMDC\TerpTubeBundle\Entity\AccessData')))
            ->setAllowedValues(array(
                'class' => array(
                    'IMDC\TerpTubeBundle\Entity\Forum',
                    'IMDC\TerpTubeBundle\Entity\Thread')));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'access_type';
    }
}
