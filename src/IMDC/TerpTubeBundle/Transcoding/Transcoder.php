<?php
namespace IMDC\TerpTubeBundle\Transcoding;
use IMDC\TerpTubeBundle\Entity\ResourceFile;

use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use IMDC\TerpTubeBundle\Utils\Utils;

use FFMpeg\FFProbe;

use FFMpeg\FFMpeg;

class Transcoder 
{
	private $logger;
	private $ffmpeg;
	private $transcoder;

	public function __construct($logger, $transcoder)
	{
		$this->logger = $logger;
		$this->ffmpeg = FFMpeg::create();
		$this->transcoder = $transcoder;
	}

	/**
	 * Function that takes two file Paths and merges the files into 1 and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 * @param File $audioFilePath
	 * @param File $videoFilePath
	 * @return UploadedFile $file - The merged file. Needs to be moved to a permanent directory.
	 */
	public function mergeAudioVideo(File $audioFile, File $videoFile)
	{
		//Process video merging.
		try
		{
		$audioFilePath = $audioFile->getRealPath();
		$videoFilePath = $videoFile->getRealPath();
		
		$tempDir = '/tmp/terptube-recordings';
		$umask = umask();
		umask(0000);
		if (!file_exists($tempDir))
			mkdir($tempDir);
		$tempFileName = tempnam($tempDir, "MergedVideo");
		
		//Will this fix the problem on the server with executing the command?
		$audioFile->move($tempDir, $audioFile->getFilename());
		$videoFile->move($tempDir, $videoFile->getFilename());
		
		$audioFilePath = $audioFile->getRealPath();
		$videoFilePath = $videoFile->getRealPath();
		
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
		}
		catch (Exception $e)
		{
			$this->logger->error($e->getTraceAsString());	
		}
		return $videoFile;
	}
	
	/**
	 * Function that takes a file and converts it to WebM using the selected preset and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 * @param File $inputFile
	 * @param String $preset
	 * @return File $file - The converted file. Needs to be moved to a permanent directory.
	 */
	public function transcodeToWebM(File $inputFile, $preset)
	{
		
		$tempDir = '/tmp/terptube-transcoding';
		$workingDir = $tempDir .'/'.$inputFile->getFilename();
		$umask = umask();
		umask(0000);
		if (!file_exists($tempDir))
			mkdir($tempDir);
		
		if (!file_exists($workingDir))
			mkdir($workingDir);
		$tempFileName = tempnam($tempDir, "WebMVideo");
		
		umask($umask);
		$dir = getcwd();
		chdir($workingDir);
		//Convert to webm
		$outputFileWebm = $tempFileName. '.webm';
		
		$this->logger->info("Transcoding " . $inputFile->getRealPath() ." to: " . $outputFileWebm);
		$this->transcoder->transcodeWithPreset($inputFile->getRealPath(), $preset, $outputFileWebm);
		
		chdir($dir);
		delTree($workingDir);
		unlink($tempFileName);
		$this->logger->info("Transcoding complete!");
		
		return new File($outputFileWebm);
	}
	
	/**
	 * Function that takes a file and converts it to X264 using the selected preset and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 * @param File $inputFile
	 * @param String $preset
	 * @return File $file - The converted file. Needs to be moved to a permanent directory.
	 */
	public function transcodeToX264(File $inputFile, $preset)
	{
	
		$tempDir = '/tmp/terptube-transcoding';
		$workingDir = $tempDir .'/'.$inputFile->getFilename();
		$umask = umask();
		umask(0000);
		if (!file_exists($tempDir))
			mkdir($tempDir);
	
		if (!file_exists($workingDir))
			mkdir($workingDir);
		$tempFileName = tempnam($tempDir, "X264Video");
	
		umask($umask);
		$dir = getcwd();
		chdir($workingDir);
		//Convert to webm
		$outputFileWebm = $tempFileName. '.mp4';
	
		$this->logger->info("Transcoding " . $inputFile->getRealPath() ." to: " . $outputFileWebm);
		$this->transcoder->transcodeWithPreset($inputFile->getRealPath(), $preset, $outputFileWebm);
	
		chdir($dir);
		Utils::delTree($workingDir);
		unlink($tempFileName);
		$this->logger->info("Transcoding complete!");
	
		return new File($outputFileWebm);
	}
}
