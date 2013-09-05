<?php
namespace IMDC\TerpTubeBundle\Transcoding;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use IMDC\TerpTubeBundle\Utils\Utils;

use FFMpeg\FFProbe;

use FFMpeg\FFMpeg;

class Transcoder 
{
	private $logger;
	private $ffmpeg;

	public function __construct($logger)
	{
		$this->logger = $logger;
		$this->ffmpeg = FFMpeg::create();
	}

	/**
	 * Function that takes to file Paths and merges the files into 1 and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 * @param File $audioFilePath
	 * @param File $videoFilePath
	 * @return UploadedFile $file - The merged file. Needs to be moved to a permanent directory.
	 */
	public function mergeAudioVideo(File $audioFile, File $videoFile)
	{
		//Process video merging.
		
		$audioFilePath = $audioFile->getRealPath();
		$videoFilePath = $videoFile->getRealPath();
		
		$tempDir = '/tmp/terptube-recordings';
		$umask = umask();
		umask(0000);
		if (!file_exists($tempDir))
			mkdir($tempDir);
		$tempFileName = tempnam($tempDir, "MergedVideo");
		
		umask($umask);
		$dir = getcwd();
		chdir($tempDir);
		//Convert to webm
		$outputFileWebm = $tempFileName. '.webm';
		$this->logger->info("Merging " . $audioFilePath ." and " . $videoFilePath ." to: " . $outputFileWebm);
		
		$this->ffmpeg->getFFMpegDriver()->command(array("-i", $audioFilePath, "-i", $videoFilePath, "-acodec", "libvorbis", "-vcodec", "copy", "-y",$outputFileWebm ));
		chdir($dir);
		
		unlink($tempFileName);
		$this->logger->info("Transcoding complete!");
		rename($outputFileWebm,$videoFilePath);
		//$uploadedFile =new UploadedFile($videoFilePath, "recording", "video/webm", filesize($videoFilePath), UPLOAD_ERR_OK, false); 
		$isValid = $videoFile->isValid();
		if ($isValid)
			$this->logger->info("Uploaded file valid " );
		else
			$this->logger->info("Uploaded file invalid " );
		//$uploadedFile->move('/tmp','test.webm');
		return $videoFile;
	}
}
