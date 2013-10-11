<?php

namespace IMDC\TerpTubeBundle\Controller;
use Doctrine\ORM\EntityNotFoundException;

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

class MediaController extends Controller
{
	public function simultaneousPreviewAndRecordAction(Request $request, $mediaID, $url)
	{
		$recorderConfiguration = $request->get("recorderConfiguration");
		$user = $this->getUser();
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());

		$em = $this->container->get('doctrine')->getManager();
		$mediaFile = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaID);
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		return $this
				->render('IMDCTerpTubeBundle:MediaController:simultaneousPreviewAndRecord.html.twig',
						array("mediaFile" => $mediaFile));
	}

	public function compoundMediaPreviewAction(Request $request, $compoundMediaID, $url)
	{
		$recorderConfiguration = $request->get("recorderConfiguration");
		$user = $this->getUser();
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
	
		$em = $this->container->get('doctrine')->getManager();
		$mediaFile = $em->getRepository('IMDCTerpTubeBundle:CompoundMedia')->find($compoundMediaID);
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		return $this
		->render('IMDCTerpTubeBundle:MediaController:previewCompoundMedia.html.twig',
				array("compoundMedia" => $mediaFile));
	}
}
