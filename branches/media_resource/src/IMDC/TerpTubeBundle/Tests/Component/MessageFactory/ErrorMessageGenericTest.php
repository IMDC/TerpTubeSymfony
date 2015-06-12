<?php

namespace IMDC\TerpTubeBundle\Tests\Component\MessageFactory;

use IMDC\TerpTubeBundle\Component\MessageFactory\ErrorMessageGeneric;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @author maha zafar <maha.zafar@ryerson.ca>
 *
 */
class ErrorMessageGenericTest extends \PHPUnit_Framework_TestCase
{
	public function testErrorResponse()
	{
		$errCode = 25;
		$errMsg='Error404';
		
		/** @var $response Response */
		$response = ErrorMessageGeneric::ErrorResponse($errCode, $errMsg);
		$content = $response->getContent();
		
		$error = json_decode($content, true);
		
		$this->assertNotNull($error);
		
		//Assert that the test was correct
		$this->assertArrayHasKey('errorCode', $error);
		$this->assertArrayHasKey('errorMsg', $error);
		
		$this->assertEquals($errCode, $error['errorCode']);
		$this->assertEquals($errMsg, $error['errorMsg']);
		
		$headers = $response->headers;
		$val =  $headers->get('Content-Type');
		$this->assertEquals('application/json', $val);	
		
		//$this->assertNotNull($headers);
		
	}
}

