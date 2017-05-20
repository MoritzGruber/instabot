<?php

namespace InstagramAPI;

/**
 * Automatic image resizer.
 *
 * Resizes and crops an image to match Instagram's requirements, if necessary.
 * You can also use this to force your image into different aspects, ie square.
 *
 * Usage:
 *
 * - Create an instance of the class with your image file and requirements.
 * - Call getFile() to get the path to an image matching the requirements. This
 *   will be the same as the input file if no processing was required.
 * - Optionally, call deleteFile() if you want to delete the temporary file
 *   ahead of time instead of automatically when PHP does its object garbage
 *   collection. This function is safe and won't delete the original input file.
 *
 * Remember to thank Abyr Valg for the brilliant image processing algorithm!
 *
 * @author Abyr Valg <valga.github@abyrga.ru>
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class ImageAutoResizer
{
    /**
     * Lowest allowed aspect ratio (4:5, meaning portrait).
     *
     * These are decided by Instagram. Not by us!
     *
     * @var float
     *
     * @see https://help.instagram.com/1469029763400082
     */
    const MIN_RATIO = 0.8;

    /**
     * Highest allowed aspect ratio (1.91:1, meaning landscape).
     *
     * These are decided by Instagram. Not by us!
     *
     * @var float
     */
    const MAX_RATIO = 1.91;

    /**
     * Maximum allowed image width.
     *
     * These are decided by Instagram. Not by us!
     *
     * @var int
     *
     * @see https://help.instagram.com/1631821640426723
     */
    const MAX_WIDTH = 1080;

    /**
     * Maximum allowed image height.
     *
     * This is derived from 1080 / 0.8 (tallest portrait aspect allowed).
     * Instagram enforces the width & aspect. Height is auto-derived from that.
     *
     * @var int
     */
    const MAX_HEIGHT = 1350;

    /**
     * Output JPEG quality.
     *
     * This value was chosen because 100 is very wasteful.
     *
     * @var int
     */
    const JPEG_QUALITY = 95;

    /** @var string Input file path. */
    protected $_inputFile;

    /** @var float|null Minimum allowed aspect ratio. */
    protected $_minAspectRatio;

    /** @var float|null Maximum allowed aspect ratio. */
    protected $_maxAspectRatio;

    /** @var int Crop focus position (-50 .. 50). */
    protected $_cropFocus;

    /** @var string Path to a tmp directory. */
    protected $_tmpPath;

    /** @var string Output file path. */
    protected $_outputFile;

    /** @var int Width of the original image. */
    protected $_width;

    /** @var int Height of the original image. */
    protected $_height;

    /** @var float Aspect ratio of the original image. */
    protected $_aspectRatio;

    /** @var int Type of the original image. */
    protected $_imageType;

    /** @var int|null Orientation of the original image. */
    protected $_imageOrientation;

    /** @var bool Rotated image flag. */
    protected $_isRotated;

    /** @var bool Horizontally flipped image flag (used for cropFocus auto-detection). */
    protected $_isHorFlipped;

    /** @var bool Vertically flipped image flag (used for cropFocus auto-detection). */
    protected $_isVerFlipped;

    /**
     * Constructor.
     *
     * @param string      $inputFile      Path to an input file.
     * @param int|null    $cropFocus      Crop focus position (-50 .. 50), uses
     *                                    intelligent guess if not set.
     * @param float|null  $minAspectRatio Minimum allowed aspect ratio, uses
     *                                    self::MIN_RATIO if not set.
     * @param float|null  $maxAspectRatio Maximum allowed aspect ratio, uses
     *                                    self::MAX_RATIO if not set.
     * @param string|null $tmpPath        Path to temp directory, uses system
     *                                    temp location if not set.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $inputFile,
        $cropFocus = null,
        $minAspectRatio = null,
        $maxAspectRatio = null,
        $tmpPath = null)
    {
        // Input file.
        if (!is_file($inputFile)) {
            throw new \InvalidArgumentException(sprintf('Input file "%s" doesn\'t exist.', $inputFile));
        }
        $this->_inputFile = $inputFile;

        // Crop focus.
        if ($cropFocus !== null && ($cropFocus < -50 || $cropFocus > 50)) {
            throw new \InvalidArgumentException('Crop focus must be between -50 and 50.');
        }
        $this->_cropFocus = $cropFocus;

        // Aspect ratios.
        if ($minAspectRatio !== null && ($minAspectRatio < self::MIN_RATIO || $minAspectRatio > self::MAX_RATIO)) {
            throw new \InvalidArgumentException(sprintf('Minimum aspect ratio should be between %.2f and %.2f.',
                self::MIN_RATIO, self::MAX_RATIO));
        } elseif ($minAspectRatio === null) {
            $minAspectRatio = self::MIN_RATIO;
        }
        if ($maxAspectRatio !== null && ($maxAspectRatio < self::MIN_RATIO || $maxAspectRatio > self::MAX_RATIO)) {
            throw new \InvalidArgumentException(sprintf('Maximum aspect ratio should be between %.2f and %.2f.',
                self::MIN_RATIO, self::MAX_RATIO));
        } elseif ($maxAspectRatio === null) {
            $maxAspectRatio = self::MAX_RATIO;
        }
        if ($minAspectRatio !== null && $maxAspectRatio !== null && $minAspectRatio > $maxAspectRatio) {
            throw new \InvalidArgumentException('Maximum aspect ratio must be greater or equal to minimum.');
        }
        $this->_minAspectRatio = $minAspectRatio;
        $this->_maxAspectRatio = $maxAspectRatio;

        // Temporary directory path.
        if ($tmpPath === null) {
            $tmpPath = sys_get_temp_dir();
        }
        if (!is_dir($tmpPath) || !is_writable($tmpPath)) {
            throw new \InvalidArgumentException(sprintf('Directory %s does not exist or is not writable.', $tmpPath));
        }
        $this->_tmpPath = realpath($tmpPath);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->deleteFile();
    }

    /**
     * Removes the output file if it exists and differs from input file.
     *
     * This function is safe and won't delete the original input file.
     *
     * Is automatically called when the class instance is destroyed by PHP.
     * But you can manually call it ahead of time if you want to force cleanup.
     *
     * Note that getFile() will still work afterwards, but will have to process
     * the image again to a new temp file if the input file required processing.
     *
     * @return bool
     */
    public function deleteFile()
    {
        // Only delete if outputfile exists and isn't the same as input file.
        if ($this->_outputFile !== null && $this->_outputFile != $this->_inputFile && is_file($this->_outputFile)) {
            $result = @unlink($this->_outputFile);
            $this->_outputFile = null; // Reset so getFile() will work again.
            return $result;
        }

        return true;
    }

    /**
     * Gets the path to an image file matching the requirements.
     *
     * The automatic processing is performed the first time that this function
     * is called. Which means that no CPU time is wasted if you never call this
     * function at all.
     *
     * Due to the processing, the first call to this function may take a moment.
     *
     * If the input file already fits all of the specifications, we simply
     * return the input path instead, without any need to re-process it.
     *
     * @return string The path to the image file.
     *
     * @see _shouldProcess() For the criteria that determines processing.
     */
    public function getFile()
    {
        if ($this->_outputFile === null) {
            if ($this->_shouldProcess()) {
                $this->_process();
            } else {
                $this->_outputFile = $this->_inputFile;
            }
        }

        return $this->_outputFile;
    }

    /**
     * Checks whether we should process the input file.
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    protected function _shouldProcess()
    {
        $info = @getimagesize($this->_inputFile);
        if ($info === false) {
            throw new \RuntimeException(sprintf('File "%s" is not an image.', $this->_inputFile));
        }

        // Get basic image info.
        list($this->_width, $this->_height, $this->_imageType) = $info;
        $this->_aspectRatio = $this->_width / $this->_height;
        $isJpeg = $this->_imageType == IMAGETYPE_JPEG;

        // Detect image orientation.
        $this->_imageOrientation = null;
        $this->_isRotated = false;
        $this->_isHorFlipped = false;
        $this->_isVerFlipped = false;
        if ($isJpeg && ($exif = @exif_read_data($this->_inputFile)) !== false) {
            if (isset($exif['Orientation'])) {
                $this->_imageOrientation = $exif['Orientation'];
                $this->_isRotated = in_array($this->_imageOrientation, [5, 6, 7, 8]);
                $this->_isHorFlipped = in_array($this->_imageOrientation, [2, 3, 6, 7]);
                $this->_isVerFlipped = in_array($this->_imageOrientation, [3, 4, 7, 8]);
            }
        }

        // If image is rotated, swap width and height.
        if ($this->_isRotated) {
            $width = $this->_width;
            $this->_width = $this->_height;
            $this->_height = $width;
            $this->_aspectRatio = 1 / $this->_aspectRatio;
        }

        // Process everything that's not already a JPEG file.
        if (!$isJpeg) {
            return true;
        }

        // Process if image requires reorientation.
        if ($this->_imageOrientation !== null && $this->_imageOrientation != 1) {
            return true;
        }

        // Process if any side > maximum allowed.
        if ($this->_width > self::MAX_WIDTH || $this->_height > self::MAX_HEIGHT) {
            return true;
        }

        // Process if aspect ratio < minimum allowed.
        if ($this->_minAspectRatio !== null && $this->_aspectRatio < $this->_minAspectRatio) {
            return true;
        }

        // Process if aspect ratio > maximum allowed.
        if ($this->_maxAspectRatio !== null && $this->_aspectRatio > $this->_maxAspectRatio) {
            return true;
        }

        // No need to do any processing.
        return false;
    }

    /**
     * Creates an empty temp file with a unique filename.
     *
     * @return string
     */
    protected function _makeTempFile()
    {
        return tempnam($this->_tmpPath, 'IMG');
    }

    /**
     * Wrapper for imagerotate function.
     *
     * @param resource $original
     * @param int      $angle
     * @param int      $bgColor
     * @param int|null $flip
     *
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _rotateResource(
        $original,
        $angle,
        $bgColor,
        $flip = null)
    {
        // Flip the image resource if needed. Does not create a new resource.
        if ($flip !== null) {
            if (imageflip($original, $flip) === false) {
                throw new \RuntimeException('Failed to flip image.');
            }
        }

        // Return original resource if no rotation is needed.
        if ($angle === 0) {
            return $original;
        }

        // Attempt to create a new, rotated image resource.
        $result = imagerotate($original, $angle, $bgColor);
        if ($result === false) {
            throw new \RuntimeException('Failed to rotate image.');
        }

        // Destroy the original resource since we'll return the new resource.
        @imagedestroy($original);

        return $result;
    }

    /**
     * @param resource $source
     * @param int      $src_x
     * @param int      $src_y
     * @param int      $src_w
     * @param int      $src_h
     * @param int      $dst_w
     * @param int      $dst_h
     *
     * @throws \RuntimeException
     */
    protected function _cropAndResize(
        $source,
        $src_x,
        $src_y,
        $src_w,
        $src_h,
        $dst_w,
        $dst_h)
    {
        $output = imagecreatetruecolor($dst_w, $dst_h);
        if ($output === false) {
            throw new \RuntimeException('Failed to create output image.');
        }
        try {
            // Create an output canvas with a white background.
            // NOTE: This is just to have a nice white background in the
            // resulting JPG if a transparent image was used as input.
            $white = imagecolorallocate($output, 255, 255, 255);
            if ($white === false) {
                throw new \RuntimeException('Failed to allocate color.');
            }
            if (imagefilledrectangle($output, 0, 0, $dst_w - 1, $dst_h - 1, $white) === false) {
                throw new \RuntimeException('Failed to fill image with default color.');
            }

            // Copy the resized (and resampled) image onto the new canvas.
            if (imagecopyresampled($output, $source, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) === false) {
                throw new \RuntimeException('Failed to resample image.');
            }

            // Handle image rotation.
            switch ($this->_imageOrientation) {
                case 2:
                    $output = $this->_rotateResource($output, 0, $white, IMG_FLIP_HORIZONTAL);
                    break;
                case 3:
                    $output = $this->_rotateResource($output, 0, $white, IMG_FLIP_BOTH);
                    break;
                case 4:
                    $output = $this->_rotateResource($output, 0, $white, IMG_FLIP_VERTICAL);
                    break;
                case 5:
                    $output = $this->_rotateResource($output, 90, $white, IMG_FLIP_HORIZONTAL);
                    break;
                case 6:
                    $output = $this->_rotateResource($output, -90, $white);
                    break;
                case 7:
                    $output = $this->_rotateResource($output, -90, $white, IMG_FLIP_HORIZONTAL);
                    break;
                case 8:
                    $output = $this->_rotateResource($output, 90, $white);
                    break;
            }

            // Write the result to disk.
            $tempFile = null;
            try {
                $tempFile = $this->_makeTempFile();
                if (imagejpeg($output, $tempFile, self::JPEG_QUALITY) === false) {
                    throw new \RuntimeException('Failed to create JPEG image file.');
                }
                $this->_outputFile = $tempFile;
            } catch (\Exception $e) {
                $this->_outputFile = null;
                if ($tempFile !== null && is_file($tempFile)) {
                    @unlink($tempFile);
                }
                throw $e; // Re-throw.
            }
        } finally {
            @imagedestroy($output);
        }
    }

    /**
     * @param resource $resource
     *
     * @throws \RuntimeException
     */
    protected function _processResource(
        $resource)
    {
        $x1 = $y1 = 0;
        $x2 = $width = $this->_width;
        $y2 = $height = $this->_height;

        // Check aspect ratio and crop if needed.
        if ($this->_minAspectRatio !== null && $this->_aspectRatio < $this->_minAspectRatio) {
            // We need to make an image "wider" in any case, so floor is used intentionally.
            $height = floor($this->_width / $this->_minAspectRatio);
            $aspectRatio = $this->_minAspectRatio;

            // Crop vertical images from top by default, to keep faces, etc.
            if ($this->_cropFocus === null) {
                $cropFocus = -50;
            } else {
                $cropFocus = $this->_cropFocus;
            }

            // Apply fix for flipped images.
            if ($this->_isVerFlipped) {
                $cropFocus = -$cropFocus;
            }

            // Calculate difference and divide it by cropFocus.
            $diff = $this->_height - $height;
            $y1 = round($diff * (50 + $cropFocus) / 100);
            $y2 = $y2 - ($diff - $y1);
        } elseif ($this->_maxAspectRatio !== null && $this->_aspectRatio > $this->_maxAspectRatio) {
            // We need to make an image "narrower".
            $width = floor($this->_height * $this->_maxAspectRatio);
            $aspectRatio = $this->_maxAspectRatio;

            // Crop horizontal images from center by default.
            if ($this->_cropFocus === null) {
                $cropFocus = 0;
            } else {
                $cropFocus = $this->_cropFocus;
            }

            // Apply fix for flipped images.
            if ($this->_isHorFlipped) {
                $cropFocus = -$cropFocus;
            }

            // Calculate difference and divide it by cropFocus.
            $diff = $this->_width - $width;
            $x1 = round($diff * (50 + $cropFocus) / 100);
            $x2 = $x2 - ($diff - $x1);
        } else {
            $aspectRatio = $this->_aspectRatio;
        }

        // Calculate final image dimensions at the desired aspect ratio.
        if ($aspectRatio == 1) {
            // Square.
            // NOTE: Our square will be the size of the shortest side, or the
            // maximum allowed image width by Instagram, whichever is smallest.
            $squareWidth = $width < $height ? $width : $height;
            if ($squareWidth > self::MAX_WIDTH) {
                $squareWidth = self::MAX_WIDTH;
            }
            $width = $height = $squareWidth;
        } else {
            // If > 1: Landscape (wider than tall). Limit by width.
            // If < 1: Portrait (taller than wide). Limit by width.
            // NOTE: Maximum "allowed" height is 1350, which is EXACTLY what you
            // get with a maxwidth of 1080 / 0.8 (4:5 aspect ratio). Instagram
            // enforces width & aspect ratio, which in turn auto-decides height.
            if ($width > self::MAX_WIDTH) {
                $width = self::MAX_WIDTH;
            }
            $height = floor($width / $aspectRatio);
        }

        // Do the crop & resize (coordinates are swapped for rotated images).
        if (!$this->_isRotated) {
            $this->_cropAndResize($resource, $x1, $y1, $x2 - $x1, $y2 - $y1, $width, $height);
        } else {
            $this->_cropAndResize($resource, $y1, $x1, $y2 - $y1, $x2 - $x1, $height, $width);
        }
    }

    /**
     * @throws \RuntimeException
     */
    protected function _process()
    {
        // Read the correct input file format.
        switch ($this->_imageType) {
            case IMAGETYPE_JPEG:
                $resource = imagecreatefromjpeg($this->_inputFile);
                break;
            case IMAGETYPE_PNG:
                $resource = imagecreatefrompng($this->_inputFile);
                break;
            case IMAGETYPE_GIF:
                $resource = imagecreatefromgif($this->_inputFile);
                break;
            default:
                throw new \RuntimeException('Unsupported image type.');
        }
        if ($resource === false) {
            throw new \RuntimeException('Failed to load image.');
        }

        // Attempt to process the input file.
        try {
            $this->_processResource($resource);
        } finally {
            @imagedestroy($resource);
        }
    }
}
