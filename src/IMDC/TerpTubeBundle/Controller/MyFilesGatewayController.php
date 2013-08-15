<?php

namespace IMDC\TerpTubeBundle\Controller;

use Symfony\Component\Intl\Exception\NotImplementedException;

use IMDC\TerpTubeBundle\Form\Type\OtherMediaFormType;

use IMDC\TerpTubeBundle\Form\Type\VideoMediaFormType;

use IMDC\TerpTubeBundle\Event\UploadEvent;

use IMDC\TerpTubeBundle\Entity\Media;

use IMDC\TerpTubeBundle\Form\Type\AudioMediaFormType;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use IMDC\TerpTubeBundle\Filter\FileFilter;

use IMDC\TerpTubeBundle\Form\Type\ImageMediaFormType;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Finder\Exception\AccessDeniedException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class MyFilesGatewayController extends Controller
{

	/**
	 * A gateway form for uploading/recording or selecting existing files
	 * 
	 * @param String $filter
	 * @param boolean $isAjax
	 * @param String $path
	 * @throws AccessDeniedException
	 * @throws NotFoundHttpException
	 * @throws BadRequestHttpException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function gatewayAction(Request $request)
	{
		$securityContext = $this->container->get('security.context');
	 	if(! $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in first'
                    );
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->getUser();
        $resourceFiles = $user->getResourceFiles();
        return $this->render('IMDCTerpTubeBundle:MyFilesGateway:index.html.twig', array('resourceFiles'=>$resourceFiles));
	}
	
}
