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
		if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add('notice', 'Please log in first');
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		$user = $this->getUser();
		$resourceFiles = $user->getResourceFiles();
		return $this
				->render('IMDCTerpTubeBundle:MyFilesGateway:index.html.twig', array('resourceFiles' => $resourceFiles));
	}

	/**
	 * An Ajax function that deletes a media with a specific media ID
	 * @param Request $request
	 * @param unknown_type $mediaId
	 */
	public function deleteMediaAction(Request $request, $mediaId)
	{
		$securityContext = $this->container->get('security.context');
		if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add('notice', 'Please log in first');
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		if (!$request->isXmlHttpRequest())
			throw new BadRequestHttpException('Only Ajax POST calls accepted');
		$user = $this->getUser();
		$em = $this->get('doctrine')->getManager();
		/** @var $media IMDC\TerpTubeBundle\Entity\Media */
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);

		if ($media->getOwner() != $user)
		{
			$return = array('responseCode' => 400, 'feedback' => 'Not the rightful owner of the file');
		}
		else
		{
			//FIXME need to figure out if video is being transcoded and interrupt it if so and clean up
			if ($media !== null)
			{
				$em->remove($media);
				$em->flush();
				$return = array('responseCode' => 200, 'feedback' => 'Successfully removed media!');
			}
			else
			{
				$return = array('responseCode' => 400, 'feedback' => 'Could not remove media, or media does not exist.');
			}
		}
		$return = json_encode($return); // json encode the array
		return new Response($return, 200, array('Content-Type' => 'application/json'));
	}

	/**
	 * An Ajax function that previews a media with a specific media ID
	 * @param Request $request
	 * @param unknown_type $mediaId
	 */
	public function previewMediaAction(Request $request, $mediaId)
	{
		$securityContext = $this->container->get('security.context');
		if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			$this->get('session')->getFlashBag()->add('notice', 'Please log in first');
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		if (!$request->isXmlHttpRequest())
			throw new BadRequestHttpException('Only Ajax POST calls accepted');
		$user = $this->getUser();
		$em = $this->get('doctrine')->getManager();
		/** @var $media IMDC\TerpTubeBundle\Entity\Media */
		$media = $em->getRepository('IMDCTerpTubeBundle:Media')->find($mediaId);

		$responseURL = "";
		if ($media !== null)
		{
			//FIXME Should check for file permissions before showing the media to the user
			switch ($media->getType())
			{
			case Media::TYPE_AUDIO:
				$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:previewAudio.html.twig';
				break;
			case Media::TYPE_VIDEO:
				$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:previewVideo.html.twig';
				break;
			case Media::TYPE_IMAGE:
				$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:previewImage.html.twig';
				break;
			case Media::TYPE_AUDIO:
				$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:previewAudio.html.twig';
				break;
			case Media::TYPE_OTHER:
				$responseURL = 'IMDCTerpTubeBundle:MyFilesGateway:previewOther.html.twig';
				break;
			}
		}
		else
		{
			throw new EntityNotFoundException("Cannot find media with that ID");
		}
		return $this->render($responseURL, array('mediaFile' => $media));
	}
	
	public function recordMediaAction(Request $request, $url)
	{
		//FIXME add the recording stuff here
		// 		throw new NotImplementedException("Not yet implemented");
	
		$user = $this->container->get('security.context')->getToken()->getUser();
		if (!is_object($user) || !$user instanceof UserInterface)
		{
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$userManager = $this->container->get('fos_user.user_manager');
		$userObject = $userManager->findUserByUsername($user->getUsername());
		if ($userObject == null)
		{
			throw new NotFoundHttpException("This user does not exist");
		}
		return $this->render('IMDCTerpTubeBundle:MyFilesGateway:recordVideo.html.twig');
	}

}
