<?php

namespace InstagramAPI;

class Utils
{
    /**
     * Name of the detected ffmpeg executable, or FALSE if none found.
     *
     * @var string|bool|null
     */
    public static $ffmpegBin = null;

    /**
     * Name of the detected ffprobe executable, or FALSE if none found.
     *
     * @var string|bool|null
     */
    public static $ffprobeBin = null;

    /**
     * @return string
     */
    public static function generateUploadId()
    {
        return number_format(round(microtime(true) * 1000), 0, '', '');
    }

    /**
     * Generates user breadcrumb for use when posting a comment.
     *
     * @return string
     */
    public static function generateUserBreadcrumb(
        $size)
    {
        $key = 'iN4$aGr0m';
        $date = (int) (microtime(true) * 1000);

        // typing time
        $term = rand(2, 3) * 1000 + $size * rand(15, 20) * 100;

        // android EditText change event occur count
        $text_change_event_count = round($size / rand(2, 3));
        if ($text_change_event_count == 0) {
            $text_change_event_count = 1;
        }

        // generate typing data
        $data = $size.' '.$term.' '.$text_change_event_count.' '.$date;

        return base64_encode(hash_hmac('sha256', $data, $key, true))."\n".base64_encode($data)."\n";
    }

    /**
     * Check for ffmpeg/avconv dependencies.
     *
     * @return string|bool Name of the library if present, otherwise FALSE.
     */
    public static function checkFFMPEG()
    {
        // We only resolve this once per session and then cache the result.
        if (self::$ffmpegBin === null) {
            @exec('ffmpeg -version 2>&1', $output, $statusCode);
            if ($statusCode === 0) {
                self::$ffmpegBin = 'ffmpeg';
            } else {
                @exec('avconv -version 2>&1', $output, $statusCode);
                if ($statusCode === 0) {
                    self::$ffmpegBin = 'avconv';
                } else {
                    self::$ffmpegBin = false; // Nothing found!
                }
            }
        }

        return self::$ffmpegBin;
    }

    /**
     * Check for ffprobe dependency.
     *
     * @return string|bool Name of the library if present, otherwise FALSE.
     */
    public static function checkFFPROBE()
    {
        // We only resolve this once per session and then cache the result.
        if (self::$ffprobeBin === null) {
            @exec('ffprobe -version 2>&1', $output, $statusCode);
            if ($statusCode === 0) {
                self::$ffprobeBin = 'ffprobe';
            } else {
                self::$ffprobeBin = false; // Nothing found!
            }
        }

        return self::$ffprobeBin;
    }

    /**
     * Get detailed information about a video file.
     *
     * This also validates that a file is actually a video, since FFmpeg will
     * fail to read details from badly broken / non-video files.
     *
     * @param string $videoFilename Path to the video file.
     *
     * @throws \InvalidArgumentException If the video file is missing.
     * @throws \RuntimeException         If FFmpeg isn't working properly.
     * @throws \Exception                In case of various processing errors.
     *
     * @return array Video codec name, float duration, int width and height.
     */
    public static function getVideoFileDetails(
        $videoFilename)
    {
        // The user must have FFprobe.
        $ffprobe = self::checkFFPROBE();
        if ($ffprobe === false) {
            throw new \RuntimeException('You must have FFprobe to analyze video details.');
        }

        // Check if input file exists.
        if (empty($videoFilename) || !is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        // Load with FFPROBE. Shows details as JSON and exits.
        $command = $ffprobe.' -v quiet -print_format json -show_format -show_streams '.escapeshellarg($videoFilename);
        $jsonInfo = @shell_exec($command);

        // Check for processing errors.
        if ($jsonInfo === null) {
            throw new \RuntimeException(sprintf('FFprobe failed to analyze your video file "%s".', $videoFilename));
        }

        // Attempt to decode the JSON.
        $probeResult = @json_decode($jsonInfo, true);
        if ($probeResult === null) {
            throw new \RuntimeException(sprintf('FFprobe gave us invalid JSON for "%s".', $videoFilename));
        }

        // Now analyze all streams to find the first video stream.
        // We ignore all audio and subtitle streams.
        $videoDetails = [];
        foreach ($probeResult['streams'] as $streamIdx => $streamInfo) {
            if ($streamInfo['codec_type'] == 'video') {
                $videoDetails['codec'] = $streamInfo['codec_name']; // string
                $videoDetails['width'] = intval($streamInfo['width'], 10);
                $videoDetails['height'] = intval($streamInfo['height'], 10);
                // NOTE: Duration is a float such as "230.138000".
                $videoDetails['duration'] = floatval($streamInfo['duration']);

                break; // Stop checking streams.
            }
        }

        // Make sure we have found format details.
        if (count($videoDetails) === 0) {
            throw new \RuntimeException(sprintf('FFprobe failed to detect any video format details. Is "%s" a valid video file?', $videoFilename));
        }

        return $videoDetails;
    }

    /**
     * Verifies that a piece of media follows Instagram's size/aspect rules.
     *
     * Currently all photos and videos everywhere have the exact same rules.
     * We bring in the up-to-date rules from the ImageAutoResizer class.
     *
     * @param string    $targetFeed    Target feed for this media ("timeline", "story" or "album").
     * @param string    $fileType      Whether the file is a "photofile" or "videofile".
     * @param string    $mediaFilename Filename to display to the user in case of error.
     * @param int|float $width         The media width.
     * @param int|float $height        The media height.
     *
     * @throws \InvalidArgumentException If Instagram won't allow this file.
     *
     * @see ImageAutoResizer
     */
    public static function throwIfIllegalMediaResolution(
        $targetFeed,
        $fileType,
        $mediaFilename,
        $width,
        $height)
    {
        // WARNING TO CONTRIBUTORS: $mediaFilename is for ERROR DISPLAY to
        // users. Do NOT use it to read from the hard disk!

        // Check Resolution.
        if ($fileType == 'photofile') {
            // Validate photo resolution.
            if ($width > ImageAutoResizer::MAX_WIDTH || $height > ImageAutoResizer::MAX_HEIGHT) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts photos with a maximum resolution up to %dx%d. Your file "%s" has a %dx%d resolution.',
                    ImageAutoResizer::MAX_WIDTH, ImageAutoResizer::MAX_HEIGHT, $mediaFilename, $width, $height
                ));
            }
        } else {
            // Validate video resolution. Instagram allows between 320px-1080px width.
            // NOTE: They have height-limits too, but we automatically enforce
            // those when validating the aspect ratio further down.
            if ($width < 320 || $width > 1080) {
                throw new \InvalidArgumentException(sprintf(
                    'Instagram only accepts videos that are between 320 and 1080 pixels wide. Your file "%s" is %d pixels wide.',
                    $mediaFilename, $width
                ));
            }
        }

        // Check Aspect Ratio.
        // NOTE: This Instagram rule is the same for both videos and photos.
        // See ImageAutoResizer for the latest up-to-date allowed ratios.
        $aspectRatio = $width / $height;
        if ($aspectRatio < ImageAutoResizer::MIN_RATIO || $aspectRatio > ImageAutoResizer::MAX_RATIO) {
            throw new \InvalidArgumentException(sprintf(
                'Instagram only accepts media with aspect ratios between %.2f and %.2f. Your file "%s" has a %.2f aspect ratio.',
                ImageAutoResizer::MIN_RATIO, ImageAutoResizer::MAX_RATIO, $mediaFilename, $aspectRatio
            ));
        }
    }


    /**
     * Verifies that a video's details follow Instagram's requirements.
     *
     * @param string $targetFeed    Target feed for this media ("timeline", "story" or "album").
     * @param string $videoFilename The video filename.
     * @param array  $videoDetails  An array created by getVideoFileDetails().
     *
     * @throws \InvalidArgumentException If Instagram won't allow this video.
     */
    public static function throwIfIllegalVideoDetails(
        $targetFeed,
        $videoFilename,
        array $videoDetails)
    {
        // Validate video length.
        // NOTE: Instagram has no disk size limit, but this length validation
        // also ensures we can only upload small files exactly as intended.
        if ($targetFeed == 'story') {
            // Instagram only allows 3-15 seconds for stories.
            if ($videoDetails['duration'] < 3 || $videoDetails['duration'] > 15) {
                throw new \InvalidArgumentException(sprintf('Instagram only accepts story videos that are between 3 and 15 seconds long. Your story video "%s" is %.3f seconds long.', $videoFilename, $videoDetails['duration']));
            }
        } else {
            // Validate video length. Instagram only allows 3-60 seconds.
            // SEE: https://help.instagram.com/270963803047681
            if ($videoDetails['duration'] < 3 || $videoDetails['duration'] > 60) {
                throw new \InvalidArgumentException(sprintf('Instagram only accepts videos that are between 3 and 60 seconds long. Your video "%s" is %.3f seconds long.', $videoFilename, $videoDetails['duration']));
            }
        }

        // Validate video resolution and aspect ratio.
        self::throwIfIllegalMediaResolution($targetFeed, 'videofile', $videoFilename, $videoDetails['width'], $videoDetails['height']);
    }

    /**
     * Generate a video icon/thumbnail from a video file.
     *
     * Automatically guarantees that the generated image follows Instagram's
     * allowed image specifications, so that there won't be any upload issues.
     *
     * @param string $videoFilename Path to the video file.
     *
     * @throws \InvalidArgumentException If the video file is missing.
     * @throws \RuntimeException         If FFmpeg isn't working properly.
     * @throws \Exception                In case of various processing errors.
     *
     * @return string The JPEG binary data for the generated thumbnail.
     */
    public static function createVideoIcon(
        $videoFilename)
    {
        // The user must have FFmpeg.
        $ffmpeg = self::checkFFMPEG();
        if ($ffmpeg === false) {
            throw new \RuntimeException('You must have FFmpeg to generate video thumbnails.');
        }

        // Check if input file exists.
        if (empty($videoFilename) || !is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        // Generate a temp thumbnail filename and delete if file already exists.
        $tmpFilename = sys_get_temp_dir().'/'.md5($videoFilename).'.jpg';
        if (is_file($tmpFilename)) {
            @unlink($tmpFilename);
        }

        try {
            // Capture a video preview snapshot to that file via FFMPEG.
            $command = $ffmpeg.' -i '.escapeshellarg($videoFilename).' -f singlejpeg -ss 00:00:01 -vframes 1 '.escapeshellarg($tmpFilename).' 2>&1';
            @exec($command, $output, $statusCode);

            // Check for processing errors.
            if ($statusCode !== 0) {
                throw new \RuntimeException('FFmpeg failed to generate a video thumbnail.');
            }

            // Automatically crop&resize the thumbnail to Instagram's requirements.
            $resizer = new ImageAutoResizer($tmpFilename);
            $jpegContents = file_get_contents($resizer->getFile()); // Process&get.
            $resizer->deleteFile();

            return $jpegContents;
        } finally {
            @unlink($tmpFilename);
        }
    }

    public static function formatBytes(
        $bytes,
        $precision = 2)
    {
        $units = ['B', 'kB', 'mB', 'gB', 'tB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).''.$units[$pow];
    }

    public static function colouredString(
        $string,
        $colour)
    {
        $colours['black'] = '0;30';
        $colours['dark_gray'] = '1;30';
        $colours['blue'] = '0;34';
        $colours['light_blue'] = '1;34';
        $colours['green'] = '0;32';
        $colours['light_green'] = '1;32';
        $colours['cyan'] = '0;36';
        $colours['light_cyan'] = '1;36';
        $colours['red'] = '0;31';
        $colours['light_red'] = '1;31';
        $colours['purple'] = '0;35';
        $colours['light_purple'] = '1;35';
        $colours['brown'] = '0;33';
        $colours['yellow'] = '1;33';
        $colours['light_gray'] = '0;37';
        $colours['white'] = '1;37';

        $colored_string = '';

        if (isset($colours[$colour])) {
            $colored_string .= "\033[".$colours[$colour].'m';
        }

        $colored_string .= $string."\033[0m";

        return $colored_string;
    }

    public static function getFilterCode(
        $filter)
    {
        $filters = [];
        $filters[0] = 'Normal';
        $filters[615] = 'Lark';
        $filters[614] = 'Reyes';
        $filters[613] = 'Juno';
        $filters[612] = 'Aden';
        $filters[608] = 'Perpetua';
        $filters[603] = 'Ludwig';
        $filters[605] = 'Slumber';
        $filters[616] = 'Crema';
        $filters[24] = 'Amaro';
        $filters[17] = 'Mayfair';
        $filters[23] = 'Rise';
        $filters[26] = 'Hudson';
        $filters[25] = 'Valencia';
        $filters[1] = 'X-Pro II';
        $filters[27] = 'Sierra';
        $filters[28] = 'Willow';
        $filters[2] = 'Lo-Fi';
        $filters[3] = 'Earlybird';
        $filters[22] = 'Brannan';
        $filters[10] = 'Inkwell';
        $filters[21] = 'Hefe';
        $filters[15] = 'Nashville';
        $filters[18] = 'Sutro';
        $filters[19] = 'Toaster';
        $filters[20] = 'Walden';
        $filters[14] = '1977';
        $filters[16] = 'Kelvin';
        $filters[-2] = 'OES';
        $filters[-1] = 'YUV';
        $filters[109] = 'Stinson';
        $filters[106] = 'Vesper';
        $filters[112] = 'Clarendon';
        $filters[118] = 'Maven';
        $filters[114] = 'Gingham';
        $filters[107] = 'Ginza';
        $filters[113] = 'Skyline';
        $filters[105] = 'Dogpatch';
        $filters[115] = 'Brooklyn';
        $filters[111] = 'Moon';
        $filters[117] = 'Helena';
        $filters[116] = 'Ashby';
        $filters[108] = 'Charmes';
        $filters[640] = 'BrightContrast';
        $filters[642] = 'CrazyColor';
        $filters[643] = 'SubtleColor';

        return array_search($filter, $filters);
    }
}
