<?php

namespace IMDC\TerpTubeBundle\Transcoding;

use IMDC\TerpTubeBundle\Entity\ResourceFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use IMDC\TerpTubeBundle\Utils\Utils;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

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
		$this->fs = new Filesystem();
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
			if ($audioFile == null)
				return $videoFile;
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
			
			$this->fs->remove ( $tempFileName );
			$this->logger->info ( "Transcoding complete!" );
			$this->fs->rename ( $outputFileWebm, $videoFilePath, true );
			
			//set correct permissions
			$old = umask ( 0 );
			chmod ( $videoFilePath, 0664 );
			umask ( $old );
			
// 			$this->fs->rename ($videoFilePath,  substr($videoFilePath, strrpos(0,$videoFilePath, ".")+1)."webm");
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
	 * @return true if transcoding succeeded, false otherwise.
	 */
	public function trimVideo($inputFile, $startTime, $endTime) {
		try {
			//This fails from a recording
			$duration = $endTime - $startTime;
			$startTimeFFMPEG = $this->parseSecondsToFFMPEGTime ( $startTime );
			$durationFFMPEG = $this->parseSecondsToFFMPEGTime ( $duration );
			
			$videoFilePath = $inputFile;
			
			$extension = substr($videoFilePath, strrpos($videoFilePath, ".")+1);
			$tempDir = '/tmp/terptube-transcoding';
			//FIXME assumes / as path separator
			$workingDir = $tempDir . '/' . substr($videoFilePath, strrpos($videoFilePath, "/")+1);
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
					"-i",
					$videoFilePath,
					"-ss",
					$startTimeFFMPEG,
					"-acodec",
					"copy",
					"-vcodec",
					"copy",
					"-t",
					$durationFFMPEG,
					"-y",
					$outputFileWebm 
			) );
			
			chdir ( $dir );
			
			$this->fs->remove ( $tempFileName );
			$this->logger->info ( "Transcoding complete!" );
			$old = umask(0);
			$this->fs->chmod($outputFileWebm, 0664);
			umask($old);
			$this->fs->rename ( $outputFileWebm, $videoFilePath, true );
			$this->fs->remove ( $workingDir );
			
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
		$this->fs->remove ( $workingDir );
		$this->fs->remove ( $tempFileName );
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
		$this->fs->remove ( $workingDir );
		$this->fs->remove ( $tempFileName );
		$this->logger->info ( "Transcoding complete!" );
		
		return new File ( $outputFileWebm );
	}
	
	/**
	 * Specifically for remuxing (video+audio) WebM files produced by Firefox, so that extra metadata like duration (used by Player.js) is available.
	 *
	 * @param File $filePath
	 * @return UploadedFile $file - The remuxed file.
	 */
	public function remuxWebM(File $file) {
		try {
			$filePath = $file->getRealPath ();
				
			$tempDir = '/tmp/terptube-recordings';
			$umask = umask ();
			umask ( 0000 );
			if (! file_exists ( $tempDir ))
				mkdir ( $tempDir );
			$tempFileName = tempnam ( $tempDir, "RemuxedFile" );
			
			umask ( $umask );
			$dir = getcwd ();
			chdir ( $tempDir );
			
			$outputFileWebm = $tempFileName . '.webm';
			$this->logger->info ( "Remuxing " . $filePath . " to: " . $outputFileWebm );
				
			$this->ffmpeg->getFFMpegDriver ()->command ( array (
					"-i",
					$filePath,
					"-c",
					"copy",
					"-y",
					$outputFileWebm
			) );
			chdir ( $dir );
				
			$this->fs->remove ( $tempFileName );
			$this->logger->info ( "Remuxing complete!" );
			$this->fs->rename ( $outputFileWebm, $filePath, true );
			
			$isValid = $file->isValid ();
			if ($isValid)
				$this->logger->info ( "Uploaded file valid " );
			else
				$this->logger->info ( "Uploaded file invalid " );
		} catch ( Exception $e ) {
			$this->logger->error ( $e->getTraceAsString () );
		}
		return $file;
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
