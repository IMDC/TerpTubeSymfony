<?php

// src/Acme/StorbeBundle/Controller/DefaultController.php

namespace Acme\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Acme\StoreBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AcmeStoreBundle:Product:index.html.twig', array('name' => $name));
    }
	
    /**
     * @Route("/createproduct")
	 * 
	 */
	public function createAction() 
	{
		$product = new Product();
		$product->setName('A Foo Bar');
		$product->setPrice('19.99');
		$product->setDescription('Lorem ipsum dolor');
		
		$em = $this->getDoctrine()->getManager();
		$em->persist($product);
		$em->flush();
		
		return new Response('Created product id'.$product->getId());
	}
	
	/**
	 * @Route("/showproduct/{id}")
	 * @Template()
	 */
	public function showAction($id)
	{
		$products = array();
		
		$product = $this->getDoctrine()->getRepository('AcmeStoreBundle:Product')->find($id);
		
		if (!$product) {
			throw $this->createNotFoundException('No product found for id '.$id);
		}
		
		// ... do something, like pass the $product object into a template
		array_push($products, $product);
		
		return $this->render('AcmeStoreBundle:Product:index.html.twig', array('page_title' => $product->getName(), 'id' => $id, 'products' => $products));
	}
	
	/**
	 * @Route("/showall/")
	 * @Template()
	 */
	public function showallAction() 
	{	
		$em = $this->getDoctrine()->getManager();
		$products = $em->getRepository('AcmeStoreBundle:Product')->findAllOrderedByName();
		//$products = $em->getRepository('AcmeStoreBundle:Product')->findAll();
		
		if (!$products) {
			throw $this->createNotFoundException('No products were found, sorry');
		}
		
		return $this->render('AcmeStoreBundle:Product:showall.html.twig', array('products'=>$products, 'page_title'=>"We carry these products:"));
	}
}
