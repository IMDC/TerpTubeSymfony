<?php
namespace IMDC\TerpTubeBundle\Transcoding;
use IMDC\TerpTubeBundle\Entity\ResourceFile;
use IMDC\TerpTubeBundle\Entity\Media;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use IMDC\TerpTubeBundle\Utils\Utils;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;
use Symfony\Component\Filesystem\Filesystem;
use AC\Transcoding\Exception\InvalidInputException;

class Transcoder
{

    private $logger;

    private $ffmpeg;

    private $ffprobe;

    private $transcoder;

    const TEMPORARY_DIRECTORY_TRANSCODING = '/tmp/terptube-transcoding';

    const TEMPORARY_DIRECTORY_RECORDING = '/tmp/terptube-recordings';

    const MAX_AUDIO_BR = 128000;

    const MIN_AUDIO_BR_VORBIS = 45000;

    const INVALID_AUDIO_VIDEO_ERROR = 'Invalid audio/video';

    public function __construct ($logger, $transcoder, $ffmpegConfiguration)
    {
        $this->logger = $logger;
        $this->ffmpeg = FFMpeg::create($ffmpegConfiguration, $logger);
        $this->ffprobe = $this->ffmpeg->getFFProbe();
        $this->transcoder = $transcoder;
        $this->fs = new Filesystem();
    }

    public function checkAudioFile (File $inputFile)
    {
        try
        {
            if ($this->ffprobe->streams($inputFile->getRealPath())
                ->audios()
                ->count() != 0)
                return true;
            else
                return false;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function checkVideoFile (File $inputFile)
    {
        try
        {
            if ($this->ffprobe->streams($inputFile->getRealPath())
                ->videos()
                ->count() != 0)
                return true;
            else
                return false;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Function that takes two file Paths and merges the files into 1 and returns the resulting file.
     * The returned file is a temporary file that needs to be moved after receiving it.
     *
     * @param File $audioFilePath            
     * @param File $videoFilePath            
     * @return UploadedFile $file - The merged file. Needs to be moved to a permanent directory.
     */
    public function mergeAudioVideo (File $audioFile, File $videoFile)
    {
        // Process video merging.
        $tempFileName = null;
        $outputFileWebm = null;
        try
        {
            if ($audioFile == null)
                return $videoFile;
            $audioFilePath = $audioFile->getRealPath();
            $videoFilePath = $videoFile->getRealPath();
            
            // $tempDir = '/tmp/terptube-recordings';
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_RECORDING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_RECORDING);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_RECORDING, "MergedVideo");
            
            // //Will this fix the problem on the server with executing the command?
            // $audioFile->move($tempDir, $audioFile->getFilename());
            // $videoFile->move($tempDir, $videoFile->getFilename());
            
            // $audioFilePath = $tempDir . '/' . $audioFile->getFilename();
            // $videoFilePath = $tempDir . '/' . $videoFile->getFilename();
            
            umask($umask);
            $dir = getcwd();
            chdir(Transcoder::TEMPORARY_DIRECTORY_RECORDING);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.webm';
            $this->logger->info("Merging " . $audioFilePath . " and " . $videoFilePath . " to: " . $outputFileWebm);
            
            $this->ffmpeg->getFFMpegDriver()->command(
                    array(
                            "-i",
                            $audioFilePath,
                            "-i",
                            $videoFilePath,
                            "-map",
                            "0:0",
                            "-map",
                            "1:0",
                            "-acodec",
                            "libvorbis",
                            "-vcodec",
                            "copy",
                            "-y",
                            '-ab',
                            '128k',
                            $outputFileWebm
                    ));
            chdir($dir);
            $this->fs->remove($tempFileName);
            $this->logger->info("Transcoding complete!");
            $this->fs->rename($outputFileWebm, $videoFilePath, true);
            
            // set correct permissions
            $old = umask(0);
            chmod($videoFilePath, 0664);
            umask($old);
            
            // $this->fs->rename ($videoFilePath, substr($videoFilePath, strrpos(0,$videoFilePath, ".")+1)."webm");
            // $uploadedFile =new UploadedFile($videoFilePath, "recording", "video/webm", filesize($videoFilePath),
            // UPLOAD_ERR_OK, false);
            $isValid = $videoFile->isValid();
            if ($isValid)
                $this->logger->info("Uploaded file valid ");
            else
                $this->logger->info("Uploaded file invalid ");
            // $uploadedFile->move('/tmp','test.webm');
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            return null;
        }
        return $videoFile;
    }

    public function createThumbnail ($mediaFilePath, $type)
    {
        $this->logger->info("Creating a thumbnail");
        $tempFileName = null;
        try
        {
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING, "thumbnail");
            
            $dir = getcwd();
            chdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            $outputFile = $tempFileName . '.png';
            
            if ($type == Media::TYPE_VIDEO)
            {
                
                $thumbnailTime = $this->parseSecondsToFFMPEGTime(1.0);
                
                $this->ffmpeg->getFFMpegDriver()->command(
                        array(
                                "-i",
                                $mediaFilePath,
                                "-ss",
                                $thumbnailTime,
                                "-filter:v",
                                "scale=-1:" . Media::THUMBNAIL_HEIGHT . ",scale=trunc(in_w/2)*2:trunc(in_h/2)*2",
                                "-vframes",
                                "1",
                                $outputFile
                        ));
            }
            else 
                if ($type == Media::TYPE_IMAGE)
                {
                    $this->ffmpeg->getFFMpegDriver()->command(
                            array(
                                    "-i",
                                    $mediaFilePath,
                                    "-filter:v",
                                    "scale=-1:" . Media::THUMBNAIL_HEIGHT . ",scale=trunc(in_w/2)*2:trunc(in_h/2)*2",
                                    "-vframes",
                                    "1",
                                    $outputFile
                            ));
                }
                else
                {
                    $outputFile = NULL;
                }
            if ($outputFile != NULL)
            {
                chmod($outputFile, 0664);
            }
            umask($umask);
            chdir($dir);
            
            $this->fs->remove($tempFileName);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFile))
                $this->fs->remove($outputFile);
            return null;
        }
        $this->logger->info("Transcoding complete!");
        
        return $outputFile;
    }

    public function removeFirstFrame (File $videoFile)
    {
        // Process video merging.
        $this->logger->info("Removing first frame");
        
        $tempFileName = null;
        $outputFileWebm = null;
        try
        {
            $videoFilePath = $videoFile->getRealPath();
            
            // $tempDir = '/tmp/terptube-recordings';
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_RECORDING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_RECORDING);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_RECORDING, "MergedVideo");
            
            // //Will this fix the problem on the server with executing the command?
            // $audioFile->move($tempDir, $audioFile->getFilename());
            // $videoFile->move($tempDir, $videoFile->getFilename());
            
            // $audioFilePath = $tempDir . '/' . $audioFile->getFilename();
            // $videoFilePath = $tempDir . '/' . $videoFile->getFilename();
            
            umask($umask);
            $dir = getcwd();
            chdir(Transcoder::TEMPORARY_DIRECTORY_RECORDING);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.webm';
            $this->logger->info("Removing first frame from " . $videoFilePath . " to: " . $outputFileWebm);
            // $videoRate = $this->ffprobe->streams ( $videoFilePath )->videos ()->first ()->get ( 'avg_frame_rate' );
            // Assume the video rate is 25fps
            $videoRate = 25;
            $this->logger->info("Frame rate: " . $videoRate);
            $videoStartTime = 1 / $videoRate;
            $this->ffmpeg->getFFMpegDriver()->command(
                    array(
                            "-i",
                            $videoFilePath,
                            "-ss",
                            $videoStartTime,
                            "-acodec",
                            "copy",
                            "-vcodec",
                            "copy",
                            "-y",
                            $outputFileWebm
                    ));
            chdir($dir);
            
            $this->fs->remove($tempFileName);
            $this->logger->info("Transcoding complete!");
            $this->fs->rename($outputFileWebm, $videoFilePath, true);
            
            // set correct permissions
            $old = umask(0);
            chmod($videoFilePath, 0664);
            umask($old);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            return null;
        }
        return $videoFile;
    }

    /**
     * Function that takes a webm or h264 file, start time and length and returns the resulting file, cut from the start
     * time and having the duration of the new length.
     * The returned file is a temporary file that needs to be moved after receiving it.
     *
     * @param String $inputFile
     *            - absolute Path of the input File
     * @param double $startTime
     *            - The new start time of the video
     * @param double $endTime
     *            - The new end time of the video
     * @return true if transcoding succeeded, false otherwise.
     */
    public function trimVideo ($inputFile, $startTime, $endTime)
    {
        $tempFileName = null;
        $outputFileWebm = null;
        $workingDir = null;
        try
        {
            // This fails from a recording
            $duration = $endTime - $startTime;
            $startTimeFFMPEG = $this->parseSecondsToFFMPEGTime($startTime);
            $durationFFMPEG = $this->parseSecondsToFFMPEGTime($duration);
            
            $videoFilePath = $inputFile;
            
            $extension = substr($videoFilePath, strrpos($videoFilePath, ".") + 1);
            // $tempDir = '/tmp/terptube-transcoding';
            // FIXME assumes / as path separator
            $workingDir = Transcoder::TEMPORARY_DIRECTORY_TRANSCODING . '/' .
                     substr($videoFilePath, strrpos($videoFilePath, "/") + 1);
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            
            if (! file_exists($workingDir))
                mkdir($workingDir);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING, $extension . "Video");
            
            umask($umask);
            $dir = getcwd();
            chdir($workingDir);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.' . $extension;
            
            $this->logger->info(
                    "Trimming " . $videoFilePath . " to: " . $startTimeFFMPEG . " duration: " . $durationFFMPEG);
            $this->ffmpeg->getFFMpegDriver()->command(
                    array(
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
                    ));
            
            chdir($dir);
            
            $this->fs->remove($tempFileName);
            $this->logger->info("Transcoding complete!");
            $old = umask(0);
            $this->fs->chmod($outputFileWebm, 0664);
            umask($old);
            $this->fs->rename($outputFileWebm, $videoFilePath, true);
            $this->fs->remove($workingDir);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            if ($this->fs->exists($workingDir))
                $this->fs->remove($workingDir);
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
    public function transcodeToWebM (File $inputFile, $preset)
    {
        $tempFileName = null;
        $outputFileWebm = null;
        $workingDir = null;
        try
        {
            // $tempDir = '/tmp/terptube-transcoding';
            $workingDir = Transcoder::TEMPORARY_DIRECTORY_TRANSCODING . '/' . $inputFile->getFilename();
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            
            if (! file_exists($workingDir))
                mkdir($workingDir);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING, "WebMVideo");
            
            umask($umask);
            $dir = getcwd();
            chdir($workingDir);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.webm';
            
            $actualPreset = $this->transcoder->getPreset($preset);
            $audioBitRate = Transcoder::MAX_AUDIO_BR;
            $hasAudio = false;
            if ($this->ffprobe->streams($inputFile->getRealPath())
                ->audios()
                ->count() != 0)
            {
                $hasAudio = true;
                if ($this->ffprobe->streams($inputFile->getRealPath())
                    ->audios()
                    ->first()
                    ->has('bit_rate'))
                    $audioBitRate = min($audioBitRate, 
                            intval(
                                    $this->ffprobe->streams($inputFile->getRealPath())
                                        ->audios()
                                        ->first()
                                        ->get('bit_rate')));
                $audioBitRate = max($audioBitRate, Transcoder::MIN_AUDIO_BR_VORBIS);
            }
            
            $acodec = NULL;
            $ab = NULL;
            if (! $hasAudio)
            {
                $acodec = $actualPreset->get("-acodec");
                $ab = $actualPreset->get("-ab");
                $actualPreset->remove("-acodec");
                $actualPreset->remove("-ab");
                $actualPreset->set("-an", NULL);
            }
            else
            {
                $ab = $actualPreset->get("-ab");
                $actualPreset->set("-ab", "" . intval($audioBitRate / 1000) . "k");
            }
            
            $this->logger->info("Transcoding " . $inputFile->getRealPath() . " to: " . $outputFileWebm);
            $this->transcoder->transcodeWithPreset($inputFile->getRealPath(), $actualPreset, $outputFileWebm);
            
            // restore the current settings for the audio
            if (! $hasAudio)
            {
                $actualPreset->set("-ab", $ab);
                $actualPreset->set("-acodec", $acodec);
                $actualPreset->remove("-an");
            }
            else
            {
                $actualPreset->set("-ab", $ab);
            }
            
            chdir($dir);
            $this->fs->remove($workingDir);
            $this->fs->remove($tempFileName);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            if ($this->fs->exists($workingDir))
                $this->fs->remove($workingDir);
            return null;
        }
        $this->logger->info("Transcoding complete!");
        
        return new File($outputFileWebm);
    }

    /**
     * Function that takes an audio file and converts it to WebM using the selected preset and returns the resulting
     * file.
     * The returned file is a temporary file that needs to be moved after receiving it.
     *
     * @param File $inputFile            
     * @param String $preset            
     * @return File $file - The converted file. Needs to be moved to a permanent directory.
     */
    public function transcodeAudioToWebM (File $inputFile, $preset)
    {
        $tempFileName = null;
        $outputFileWebm = null;
        $workingDir = null;
        try
        {
            // $tempDir = '/tmp/terptube-transcoding';
            $workingDir = Transcoder::TEMPORARY_DIRECTORY_TRANSCODING . '/' . $inputFile->getFilename();
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            
            if (! file_exists($workingDir))
                mkdir($workingDir);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING, "WebMAudio");
            
            umask($umask);
            $dir = getcwd();
            chdir($workingDir);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.webm';
            
            $actualPreset = $this->transcoder->getPreset($preset);
            $audioBitRate = Transcoder::MAX_AUDIO_BR;
            $hasAudio = false;
            if ($this->ffprobe->streams($inputFile->getRealPath())
                ->audios()
                ->count() != 0)
            {
                $hasAudio = true;
                if ($this->ffprobe->streams($inputFile->getRealPath())
                    ->audios()
                    ->first()
                    ->has('bit_rate'))
                    $audioBitRate = min($audioBitRate, 
                            intval(
                                    $this->ffprobe->streams($inputFile->getRealPath())
                                        ->audios()
                                        ->first()
                                        ->get('bit_rate')));
                $audioBitRate = max($audioBitRate, Transcoder::MIN_AUDIO_BR_VORBIS);
            }
            
            $ab = NULL;
            if (! $hasAudio)
            {
                throw new InvalidInputException(sprintf("Not an audio file"));
            }
            else
            {
                $ab = $actualPreset->get("-ab");
                $actualPreset->set("-ab", "" . intval($audioBitRate / 1000) . "k");
            }
            
            $this->logger->info("Transcoding " . $inputFile->getRealPath() . " to: " . $outputFileWebm);
            $this->transcoder->transcodeWithPreset($inputFile->getRealPath(), $actualPreset, $outputFileWebm);
            
            $actualPreset->set("-ab", $ab);
            chdir($dir);
            $this->fs->remove($workingDir);
            $this->fs->remove($tempFileName);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            if ($this->fs->exists($workingDir))
                $this->fs->remove($workingDir);
            return null;
        }
        $this->logger->info("Transcoding complete!");
        
        return new File($outputFileWebm);
    }

    /**
     * Function that takes a file and converts it to X264 using the selected preset and returns the resulting file.
     * The returned file is a temporary file that needs to be moved after receiving it.
     *
     * @param File $inputFile            
     * @param String $preset            
     * @return File $file - The converted file. Needs to be moved to a permanent directory.
     */
    public function transcodeToX264 (File $inputFile, $preset)
    {
        // $tempDir = '/tmp/terptube-transcoding';
        $tempFileName = null;
        $outputFileWebm = null;
        $workingDir = null;
        try
        {
            $workingDir = Transcoder::TEMPORARY_DIRECTORY_TRANSCODING . '/' . $inputFile->getFilename();
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            
            if (! file_exists($workingDir))
                mkdir($workingDir);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING, "X264Video");
            
            umask($umask);
            $dir = getcwd();
            chdir($workingDir);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.mp4';
            
            $actualPreset = $this->transcoder->getPreset($preset);
            
            $audioBitRate = Transcoder::MAX_AUDIO_BR;
            $hasAudio = false;
            if ($this->ffprobe->streams($inputFile->getRealPath())
                ->audios()
                ->count() != 0)
            {
                $hasAudio = true;
                if ($this->ffprobe->streams($inputFile->getRealPath())
                    ->audios()
                    ->first()
                    ->has('bit_rate'))
                    $audioBitRate = min($audioBitRate, 
                            intval(
                                    $this->ffprobe->streams($inputFile->getRealPath())
                                        ->audios()
                                        ->first()
                                        ->get('bit_rate')));
            }
            
            // get the current settings for the audio
            $acodec = NULL;
            $ab = NULL;
            if (! $hasAudio)
            {
                $acodec = $actualPreset->get("-acodec");
                $ab = $actualPreset->get("-ab");
                $actualPreset->remove("-acodec");
                $actualPreset->remove("-ab");
                $actualPreset->set("-an", NULL);
            }
            else
            {
                $ab = $actualPreset->get("-ab");
                $actualPreset->set("-ab", "" . intval($audioBitRate / 1000) . "k");
            }
            $this->logger->info("Transcoding " . $inputFile->getRealPath() . " to: " . $outputFileWebm);
            $this->transcoder->transcodeWithPreset($inputFile->getRealPath(), $actualPreset, $outputFileWebm);
            // restore the current settings for the audio
            if (! $hasAudio)
            {
                $actualPreset->set("-ab", $ab);
                $actualPreset->set("-acodec", $acodec);
                $actualPreset->remove("-an");
            }
            else
            {
                $actualPreset->set("-ab", $ab);
            }
            chdir($dir);
            $this->fs->remove($workingDir);
            $this->fs->remove($tempFileName);
        }
        catch (\Exception $e)
        {
            $this->logger->error("____________________");
            $this->logger->error($e->getCode());
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            if ($this->fs->exists($workingDir))
                $this->fs->remove($workingDir);
            return null;
        }
        $this->logger->info("Transcoding complete!");
        
        return new File($outputFileWebm);
    }

    /**
     * Function that takes an audio file and converts it to X264 using the selected preset and returns the resulting
     * file.
     * The returned file is a temporary file that needs to be moved after receiving it.
     *
     * @param File $inputFile            
     * @param String $preset            
     * @return File $file - The converted file. Needs to be moved to a permanent directory.
     */
    public function transcodeAudioToX264 (File $inputFile, $preset)
    {
        // $tempDir = '/tmp/terptube-transcoding';
        $tempFileName = null;
        $outputFileWebm = null;
        $workingDir = null;
        try
        {
            $workingDir = Transcoder::TEMPORARY_DIRECTORY_TRANSCODING . '/' . $inputFile->getFilename();
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING);
            
            if (! file_exists($workingDir))
                mkdir($workingDir);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_TRANSCODING, "X264Audio");
            
            umask($umask);
            $dir = getcwd();
            chdir($workingDir);
            // Convert to webm
            $outputFileWebm = $tempFileName . '.m4a';
            
            $actualPreset = $this->transcoder->getPreset($preset);
            $audioBitRate = Transcoder::MAX_AUDIO_BR;
            $hasAudio = false;
            if ($this->ffprobe->streams($inputFile->getRealPath())
                ->audios()
                ->count() != 0)
            {
                $hasAudio = true;
                if ($this->ffprobe->streams($inputFile->getRealPath())
                    ->audios()
                    ->first()
                    ->has('bit_rate'))
                    $audioBitRate = min($audioBitRate, 
                            intval(
                                    $this->ffprobe->streams($inputFile->getRealPath())
                                        ->audios()
                                        ->first()
                                        ->get('bit_rate')));
            }
            
            $ab = NULL;
            if (! $hasAudio)
            {
                throw new InvalidInputException(sprintf("Not an audio file"));
            }
            else
            {
                $ab = $actualPreset->get("-ab");
                $actualPreset->set("-ab", "" . intval($audioBitRate / 1000) . "k");
            }
            
            $this->logger->info("Transcoding " . $inputFile->getRealPath() . " to: " . $outputFileWebm);
            $this->transcoder->transcodeWithPreset($inputFile->getRealPath(), $preset, $outputFileWebm);
            
            $actualPreset->set("-ab", $ab);
            chdir($dir);
            $this->fs->remove($workingDir);
            $this->fs->remove($tempFileName);
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            if ($this->fs->exists($workingDir))
                $this->fs->remove($workingDir);
            return null;
        }
        $this->logger->info("Transcoding complete!");
        
        return new File($outputFileWebm);
    }

    /**
     * Specifically for remuxing (video+audio) WebM files produced by Firefox, so that extra metadata like duration
     * (used by Player.js) is available.
     *
     * @param File $filePath            
     * @return UploadedFile $file - The remuxed file.
     */
    public function remuxWebM (File $file)
    {
        $tempFileName = null;
        $outputFileWebm = null;
        try
        {
            $filePath = $file->getRealPath();
            
            // $tempDir = '/tmp/terptube-recordings';
            $umask = umask();
            umask(0000);
            if (! file_exists(Transcoder::TEMPORARY_DIRECTORY_RECORDING))
                mkdir(Transcoder::TEMPORARY_DIRECTORY_RECORDING);
            $tempFileName = tempnam(Transcoder::TEMPORARY_DIRECTORY_RECORDING, "RemuxedFile");
            
            umask($umask);
            $dir = getcwd();
            chdir(Transcoder::TEMPORARY_DIRECTORY_RECORDING);
            
            $outputFileWebm = $tempFileName . '.webm';
            $this->logger->info("Remuxing " . $filePath . " to: " . $outputFileWebm);
            
            $this->ffmpeg->getFFMpegDriver()->command(
                    array(
                            "-i",
                            $filePath,
                            "-c",
                            "copy",
                            "-y",
                            $outputFileWebm
                    ));
            chdir($dir);
            
            $this->fs->remove($tempFileName);
            $this->logger->info("Remuxing complete!");
            $this->fs->rename($outputFileWebm, $filePath, true);
            
            $isValid = $file->isValid();
            if ($isValid)
                $this->logger->info("Uploaded file valid ");
            else
                $this->logger->info("Uploaded file invalid ");
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getTraceAsString());
            if ($this->fs->exists($tempFileName))
                $this->fs->remove($tempFileName);
            if ($this->fs->exists($outputFileWebm))
                $this->fs->remove($outputFileWebm);
            return null;
        }
        return $file;
    }

    public function getFFmpeg ()
    {
        return $this->ffmpeg;
    }

    public function getFFprobe ()
    {
        return $this->ffprobe;
    }

    /**
     * convert from FFMPEG Duration to seconds
     *
     * @param
     *            time
     *            in the format HH:MM:SS.mmm
     * @return time in seconds
     */
    public function parseFFMPEGTimeToSeconds ($time)
    {
        $array = preg_split(":|\.", $time);
        $timeMilliseconds = $array[0] * 60 * 60 * 1000;
        + $array[1] * 60 * 1000 + $array[2] * 1000 + $array[3];
        return $timeMilliseconds / 1000.0;
    }

    /**
     * convert from time in seconds to FFMPEG String
     *
     * @param
     *            time
     *            in seconds
     * @return String representation of time in the format HH:MM:SS,mmm
     */
    public function parseSecondsToFFMPEGTime ($time)
    {
        $time = intval($time * 1000);
        $mil = "" . ($time % 1000);
        $sec = "" . (($time / 1000) % 60);
        $min = "" . ((($time / 1000) / 60) % 60);
        $hrs = "" . ((($time / 1000) / 60) / 60) % 60;
        while (strlen($mil) < 3)
            $mil = "0" . $mil;
        while (strlen($sec) < 2)
            $sec = "0" . $sec;
        while (strlen($min) < 2)
            $min = "0" . $min;
        while (strlen($hrs) < 2)
            $hrs = "0" . $hrs;
        
        return $hrs . ":" . $min . ":" . $sec . "." . $mil;
    }
}
