<?php

namespace IMDC\TerpTubeBundle\Component\MessageFactory;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author maha zafar <maha.zafar@ryerson.ca>
 */
class ErrorMessageGeneric
{
	/**
	 * Error Response for all Forum object related controller
	 * @return \Symfony\Component\HttpFoundation\Response 
	 */
	public static function ErrorResponse($errorCode, $errorMsg)
	{
		return new Response(json_encode(array(
				'errorCode' => $errorCode,
				'errorMsg' => $errorMsg,
		)), 200, array(
				'Content-Type' => 'application/json'
		));
		
	}
}
