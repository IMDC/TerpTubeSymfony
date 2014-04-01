<?php

namespace IMDC\TerpTubeBundle\Transcoding;

use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use IMDC\TerpTubeBundle\Utils\Utils;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;

class Transcoder {
	private $logger;
	private $ffmpeg;
	private $ffprobe;
	private $transcoder;
	public function __construct($logger, $transcoder, $ffmpegConfiguration) {
		$this->logger = $logger;
		$this->ffmpeg = FFMpeg::create ( $ffmpegConfiguration, $logger );
		$this->ffprobe = $this->ffmpeg->getFFProbe ();
		$this->transcoder = $transcoder;
	}
	
	/**
	 * Function that takes two file Paths and merges the files into 1 and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 *
	 * @param File $audioFilePath        	
	 * @param File $videoFilePath        	
	 * @return UploadedFile $file - The merged file. Needs to be moved to a permanent directory.
	 */
	public function mergeAudioVideo(File $audioFile, File $videoFile) {
		// Process video merging.
		try {
			$audioFilePath = $audioFile->getRealPath ();
			$videoFilePath = $videoFile->getRealPath ();
			
			$tempDir = '/tmp/terptube-recordings';
			$umask = umask ();
			umask ( 0000 );
			if (! file_exists ( $tempDir ))
				mkdir ( $tempDir );
			$tempFileName = tempnam ( $tempDir, "MergedVideo" );
			
			// //Will this fix the problem on the server with executing the command?
			// $audioFile->move($tempDir, $audioFile->getFilename());
			// $videoFile->move($tempDir, $videoFile->getFilename());
			
			// $audioFilePath = $tempDir . '/' . $audioFile->getFilename();
			// $videoFilePath = $tempDir . '/' . $videoFile->getFilename();
			
			umask ( $umask );
			$dir = getcwd ();
			chdir ( $tempDir );
			// Convert to webm
			$outputFileWebm = $tempFileName . '.webm';
			$this->logger->info ( "Merging " . $audioFilePath . " and " . $videoFilePath . " to: " . $outputFileWebm );
			
			$this->ffmpeg->getFFMpegDriver ()->command ( array (
					"-i",
					$audioFilePath,
					"-i",
					$videoFilePath,
					"-acodec",
					"libvorbis",
					"-vcodec",
					"copy",
					"-y",
					$outputFileWebm 
			) );
			chdir ( $dir );
			
			unlink ( $tempFileName );
			$this->logger->info ( "Transcoding complete!" );
			rename ( $outputFileWebm, $videoFilePath );
			// $uploadedFile =new UploadedFile($videoFilePath, "recording", "video/webm", filesize($videoFilePath), UPLOAD_ERR_OK, false);
			$isValid = $videoFile->isValid ();
			if ($isValid)
				$this->logger->info ( "Uploaded file valid " );
			else
				$this->logger->info ( "Uploaded file invalid " );
			// $uploadedFile->move('/tmp','test.webm');
		} catch ( Exception $e ) {
			$this->logger->error ( $e->getTraceAsString () );
		}
		return $videoFile;
	}
	
	/**
	 * Function that takes a webm or h264 file, start time and length and returns the resulting file, cut from the start time and having the duration of the new length.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 *
	 * @param String $inputFile   - absolute Path of the input File     	
	 * @param double $startTime
	 *        	- The new start time of the video
	 * @param double $endTime
	 *        	- The new end time of the video
	 * @return File $file - The resulting video file. Needs to be moved to a permanent directory.
	 */
	public function trimVideo($inputFile, $startTime, $endTime) {
		try {
			
			$duration = $endTime - $startTime;
			$startTimeFFMPEG = $this->parseSecondsToFFMPEGTime ( $startTime );
			$durationFFMPEG = $this->parseSecondsToFFMPEGTime ( $duration );
			
			$videoFilePath = $inputFile;
			
			$extension = substr($videoFilePath, strrpos($videoFilePath, ".")+1);
			$tempDir = '/tmp/terptube-transcoding';
			//FIXME assumes / as path separator
			$workingDir = $tempDir . '/' . $extension = substr($videoFilePath, strrpos($videoFilePath, "/")+1);
			$umask = umask ();
			umask ( 0000 );
			if (! file_exists ( $tempDir ))
				mkdir ( $tempDir );
			
			if (! file_exists ( $workingDir ))
				mkdir ( $workingDir );
			$tempFileName = tempnam ( $tempDir, $extension . "Video" );
			
			umask ( $umask );
			$dir = getcwd ();
			chdir ( $workingDir );
			// Convert to webm
			$outputFileWebm = $tempFileName . '.' .$extension;
			
			$this->logger->info ( "Trimming " . $videoFilePath . " to: " . $startTimeFFMPEG . " duration: " . $durationFFMPEG );
			$this->ffmpeg->getFFMpegDriver ()->command ( array (
					"-ss",
					$startTimeFFMPEG,
					"-t",
					$durationFFMPEG,
					"-i",
					$videoFilePath,
					"-acodec",
					"copy",
					"-vcodec",
					"copy",
					"-y",
					$outputFileWebm 
			) );
			
			chdir ( $dir );
			
			unlink ( $tempFileName );
			$this->logger->info ( "Transcoding complete!" );
			rename ( $outputFileWebm, $videoFilePath );
		} catch ( Exception $e ) {
			$this->logger->error ( $e->getTraceAsString () );
			return false;
		}
		return true;
	}
	
	/**
	 * Function that takes a file and converts it to WebM using the selected preset and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 *
	 * @param File $inputFile        	
	 * @param String $preset        	
	 * @return File $file - The converted file. Needs to be moved to a permanent directory.
	 */
	public function transcodeToWebM(File $inputFile, $preset) {
		$tempDir = '/tmp/terptube-transcoding';
		$workingDir = $tempDir . '/' . $inputFile->getFilename ();
		$umask = umask ();
		umask ( 0000 );
		if (! file_exists ( $tempDir ))
			mkdir ( $tempDir );
		
		if (! file_exists ( $workingDir ))
			mkdir ( $workingDir );
		$tempFileName = tempnam ( $tempDir, "WebMVideo" );
		
		umask ( $umask );
		$dir = getcwd ();
		chdir ( $workingDir );
		// Convert to webm
		$outputFileWebm = $tempFileName . '.webm';
		
		$this->logger->info ( "Transcoding " . $inputFile->getRealPath () . " to: " . $outputFileWebm );
		$this->transcoder->transcodeWithPreset ( $inputFile->getRealPath (), $preset, $outputFileWebm );
		
		chdir ( $dir );
		Utils::delTree ( $workingDir );
		unlink ( $tempFileName );
		$this->logger->info ( "Transcoding complete!" );
		
		return new File ( $outputFileWebm );
	}
	
	/**
	 * Function that takes a file and converts it to X264 using the selected preset and returns the resulting file.
	 * The returned file is a temporary file that needs to be moved after receiving it.
	 *
	 * @param File $inputFile        	
	 * @param String $preset        	
	 * @return File $file - The converted file. Needs to be moved to a permanent directory.
	 */
	public function transcodeToX264(File $inputFile, $preset) {
		$tempDir = '/tmp/terptube-transcoding';
		$workingDir = $tempDir . '/' . $inputFile->getFilename ();
		$umask = umask ();
		umask ( 0000 );
		if (! file_exists ( $tempDir ))
			mkdir ( $tempDir );
		
		if (! file_exists ( $workingDir ))
			mkdir ( $workingDir );
		$tempFileName = tempnam ( $tempDir, "X264Video" );
		
		umask ( $umask );
		$dir = getcwd ();
		chdir ( $workingDir );
		// Convert to webm
		$outputFileWebm = $tempFileName . '.mp4';
		
		$this->logger->info ( "Transcoding " . $inputFile->getRealPath () . " to: " . $outputFileWebm );
		$this->transcoder->transcodeWithPreset ( $inputFile->getRealPath (), $preset, $outputFileWebm );
		
		chdir ( $dir );
		Utils::delTree ( $workingDir );
		unlink ( $tempFileName );
		$this->logger->info ( "Transcoding complete!" );
		
		return new File ( $outputFileWebm );
	}
	public function getFFmpeg() {
		return $this->ffmpeg;
	}
	public function getFFprobe() {
		return $this->ffprobe;
	}
	
	/**
	 * convert from FFMPEG Duration to seconds
	 *
	 * @param
	 *        	time
	 *        	in the format HH:MM:SS.mmm
	 * @return time in seconds
	 */
	public function parseFFMPEGTimeToSeconds($time) {
		$array = preg_split ( ":|\.", $time );
		$timeMilliseconds = $array [0] * 60 * 60 * 1000;
		+ $array [1] * 60 * 1000 + $array [2] * 1000 + $array [3];
		return $timeMilliseconds / 1000.0;
	}
	
	/**
	 * convert from time in seconds to FFMPEG String
	 *
	 * @param
	 *        	time
	 *        	in seconds
	 * @return String representation of time in the format HH:MM:SS,mmm
	 */
	public function parseSecondsToFFMPEGTime($time) {
		$time = intval ( $time * 1000 );
		$mil = "" . ($time % 1000);
		$sec = "" . (($time / 1000) % 60);
		$min = "" . ((($time / 1000) / 60) % 60);
		$hrs = "" . ((($time / 1000) / 60) / 60) % 60;
		while ( strlen ( $mil ) < 3 )
			$mil = "0" . $mil;
		while ( strlen ( $sec ) < 2 )
			$sec = "0" . $sec;
		while ( strlen ( $min ) < 2 )
			$min = "0" . $min;
		while ( strlen ( $hrs ) < 2 )
			$hrs = "0" . $hrs;
		
		return $hrs . ":" . $min . ":" . $sec . "." . $mil;
	}
}
