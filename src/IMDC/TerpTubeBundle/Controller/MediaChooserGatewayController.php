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
use IMDC\TerpTubeBundle\Model\JSEntities;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class MediaChooserGatewayController extends Controller
{
	const TYPE_ALL = 0;
	const TYPE_UPLOAD_VIDEO = 6;
	const TYPE_UPLOAD_AUDIO = 1;
	const TYPE_UPLOAD_IMAGE = 2;
	const TYPE_UPLOAD_OTHER = 3;
	const TYPE_RECORD_VIDEO = 4;
	const TYPE_RECORD_AUDIO = 5;

	public function chooseMediaByTypeAction(Request $request, $type)
	{
		if (!$this->container->get('imdc_terptube.authentication_manager')->isAuthenticated($request))
		{
			return $this->redirect($this->generateUrl('fos_user_security_login'));
		}
		return MediaChooserGatewayController::chooseMedia($request, $type);
	}

	private function chooseMedia(Request $request, $type)
	{
		$response = $this->redirect($this->generateUrl('imdc_terp_tube_user_splash')); //Go home if bad type
		$prefix = "";
		if ($request->isXmlHttpRequest())
			$prefix = "ajax.";
		/*$path = array('url' => null,
				'_route' => $request->attributes->get('_route'),
				'_route_params' => $request->attributes->get('_route_params')
				);*/
		$path = array('url' => null);
		switch ($type)
		{
		case MediaChooserGatewayController::TYPE_ALL:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:gateway', $path);
			break;
		case MediaChooserGatewayController::TYPE_UPLOAD_VIDEO:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:addVideo', $path);
			break;
		case MediaChooserGatewayController::TYPE_UPLOAD_AUDIO:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:addAudio', $path);
			break;
		case MediaChooserGatewayController::TYPE_UPLOAD_IMAGE:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:addImage', $path);
			break;
		case MediaChooserGatewayController::TYPE_UPLOAD_OTHER:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:addOther', $path);
			break;
		case MediaChooserGatewayController::TYPE_RECORD_AUDIO:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:addAudioRecording', $path);
			break;
		case MediaChooserGatewayController::TYPE_RECORD_VIDEO:
			$response = $this->forward('IMDCTerpTubeBundle:AddFileGateway:addVideoRecording', $path);
			break;
		}
// 		if ($request->isXmlHttpRequest())
// 		{
// 			$content = $response->getContent();
// 			$return = array('page' => $content, 'finished' => false);
// 			$return = json_encode($return); // json encode the array
// 			return new Response($return, 200, array('Content-Type' => 'application/json'));
// 		}
		$this->get('session')->set('mediaChooseFinished', false);
		return $response;
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
			//$this->get('session')->getFlashBag()->add('info', 'Please log in first');
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
			//$this->get('session')->getFlashBag()->add('info', 'Please log in first');
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
		return $this->render($responseURL, array('mediaFile' => $media, 'media' => JSEntities::getMediaObject ( $media )));
	}

	public function recordMediaAction(Request $request, $url)
	{
		//FIXME add the recording stuff here
		// 		throw new NotImplementedException("Not yet implemented");
		$recorderConfiguration = $request->get("recorderConfiguration");
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
		$prefix = '';
		if ($request->isXmlHttpRequest())
			$prefix = "ajax.";
		return $this
				->render('IMDCTerpTubeBundle:MyFilesGateway:' . $prefix . 'recordVideo.html.twig',
						array("recorderConfiguration" => $recorderConfiguration));
	}

    public static function getUploadForms(Controller $controller) {
        $formAudio = $controller->createForm(new AudioMediaFormType(), new Media());
        $formVideo = $controller->createForm(new VideoMediaFormType(), new Media());
        $formImage = $controller->createForm(new ImageMediaFormType(), new Media());
        $formOther = $controller->createForm(new OtherMediaFormType(), new Media());

        return array(
            $formAudio->createView(),
            $formVideo->createView(),
            $formImage->createView(),
            $formOther->createView()
        );
    }
}
