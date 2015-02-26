<?php
namespace IMDC\TerpTubeBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;

/**
 * A custom block to insert a link back to the main TerpTube homepage from the Admin side
 * 
 * @author paul
 *
 */
class LinkBoxService extends BaseBlockService
{
    public function getName()
    {
        return 'LinkBox';
    }
    
    function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            
        ));
    }
    
    function getDefaultSettings()
    {
        return array(
        	'content' => 'Insert your custom content here',
        );
    }
    
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
//         $formMapper
//         ->add('settings', 'sonata_type_immutable_array', array(
//             'keys' => array(
//                 array('url', 'url', array('required' => false)),
//                 array('title', 'text', array('required' => false)),
//             )
//         ))
        ;
    }
    
    function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
//         $errorElement
//         ->with('settings.url')
//         ->assertNotNull(array())
//         ->assertNotBlank()
//         ->end()
//         ->with('settings.title')
//         ->assertNotNull(array())
//         ->assertNotBlank()
//         ->assertMaxLength(array('limit' => 50))
//         ->end();
    }
    
    public function execute(BlockContextInterface $block, Response $response = null)
    {
        // merge settings
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());
    
        return $this->renderResponse('IMDCTerpTubeBundle:Block:linkbox.html.twig', array(
            'block'     => $block,
            'settings'  => $settings
        ), $response);
    }
}