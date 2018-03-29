<?php
/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Artemeon\Image;

use Artemeon\Image\Plugins\ImageOperationInterface;

/**
 * Class to manipulate and output images.
 *
 * This class can be used to load or create an image, apply multiple operations, such as scaling and rotation,
 * and save the resulting image. By default the processed image will be cached and no processing will be
 * performed when a cached version is available.
 *
 * Example:
 * $image = new Image2();
 * $image->load("/files/images/samples/PA252134.JPG");
 *
 * // Scale and crop the image so it is exactly 800 * 600 pixels large.
 * $image->addOperation(new ImageScaleAndCrop(800, 600));
 *
 * // Render a text with 80% opacity.
 * $image->addOperation(new ImageText("Kajona", 300, 300, 40, "rgb(0,0,0,0.8)")
 *
 * // Apply the operations and send the image to the browser.
 * if (!$image->sendToBrowser()) {
 *     echo "Error processing image.";
 * }
 *
 * Custom operations can be added by implementing ImageOperationInterface. Most operations
 * should inherit from ImageAbstractOperation, which implements ImageOperationInterface
 * and provides common functionality.
 *
 * @package module_system
 */
class Image
{
    const FORMAT_PNG = "png";
    const FORMAT_JPG = "jpg";
    const FORMAT_GIF = "gif";

    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var bool
     */
    private $useCache;

    /**
     * @var bool
     */
    private $isUpToDate;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var string
     */
    private $originalPath;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $jpegQuality;

    /**
     * @var array
     */
    private $operations;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @param string|null $cachePath
     */
    public function __construct($cachePath = null)
    {
        $this->operations = [];
        $this->jpegQuality = 100;
        $this->cachePath = $cachePath;
        $this->useCache = $cachePath !== null;
        $this->isUpToDate = false;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Destroys the resource
     */
    public function close()
    {
        if ($this->resource != null) {
            imagedestroy($this->resource);
        }
    }

    /**
     * Set whether caching is enabled (default) or disabled.
     *
     * @param bool $useCache
     * @param string|null $cachePath
     * @return void
     */
    public function setUseCache($useCache, $cachePath = null)
    {
        $this->useCache = $useCache;
        if ($cachePath !== null) {
            $this->cachePath = $cachePath;
        }
    }

    /**
     * Set the quality for JPEG pictures.
     *
     * This parameter applies only when saving JPEG images
     * and does not affect all other image processing.
     *
     * @param int $jpegQuality
     *
     * @return void
     */
    public function setJpegQuality($jpegQuality)
    {
        $this->jpegQuality = $jpegQuality;
    }

    /**
     * Create a new image with the given width and height.
     *
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    public function create($width, $height)
    {
        $this->originalPath = null;
        $this->isUpToDate = false;
        $this->width = $width;
        $this->height = $height;
        $this->operations = array();
    }

    /**
     * Use an existing image file.
     *
     * Returns false if the file does not exist.
     *
     * @param string $path
     *
     * @return bool
     */
    public function load($path)
    {
        $bitReturn = false;
        if (is_file($path)) {
            $this->isUpToDate = false;
            $this->originalPath = $path;
            $this->operations = [];
            $bitReturn = true;
        }

        return $bitReturn;
    }

    /**
     * Add an image operation.
     *
     * Image operations must implement ImageOperationInterface.
     *
     * @param ImageOperationInterface $operation
     *
     * @return void
     */
    public function addOperation(ImageOperationInterface $operation)
    {
        $this->operations[] = $operation;
        $this->isUpToDate = false;
    }

    /**
     * Save the image to a file.
     *
     * Calling this method will actually start the image processing,
     * if no cached image is available.
     *
     * @param string $path
     * @param string $format
     *
     * @return bool
     */
    public function save($path, $format = null)
    {
        if ($format == null) {
            $format = self::getFormatFromFilename($path);
        }

        if (!$this->isCached($format)) {
            if ($this->processImage($format)) {
                return $this->outputImage($format, $path);
            } else {
                return false;
            }
        } else {
            $strCacheFile = $this->getCachePath($format);
            if (!file_exists($path) || filemtime($strCacheFile) > filemtime($path)) {
                return copy($strCacheFile, $path);
            }

            return true;
        }
    }

    /**
     * Creates a base64 encoded string out of the current image.
     * May be used to embed the encoded image into a stream or a page.
     * Please be aware that the size will increase about 30% due to the encoding.
     *
     * @return string
     */
    public function getAsBase64Src()
    {
        $this->processImage(self::FORMAT_PNG);
        ob_start();
        imagepng($this->resource);
        $strContent = ob_get_contents();
        ob_end_clean();

        return "data:image/png;base64,".base64_encode($strContent);
    }


    /**
     * Create the image and send it directly to the browser.
     *
     * Calling this method will actually start the image processing,
     * if no cached image is available.
     *
     * @param string|null $format
     * @return bool
     */
    public function sendToBrowser($format = null)
    {
        if ($format == null && $this->originalPath != null) {
            $format = self::getFormatFromFilename($this->originalPath);
        }

        $contentType = null;
        switch ($format) {
            case self::FORMAT_PNG:
                $contentType = "image/jpeg";
                break;
            case self::FORMAT_JPG:
                $contentType = "image/png";
                break;
            case self::FORMAT_GIF:
                $contentType = "image/gif";
                break;
        }

        if ($contentType) {
            header("Content-Type: {$contentType}");
        }

        if (!$this->isCached($format)) {
            if ($this->processImage($format)) {
                return $this->outputImage($format);
            } else {
                return false;
            }
        } else {
            $strCacheFile = $this->getCachePath($format);
            $ptrFile = fopen($strCacheFile, 'rb');
            fpassthru($ptrFile);
            return fclose($ptrFile);
        }
    }

    /**
     * Create the image and return the GD image resource.
     *
     * This method is mainly meant to be used internally by image operations
     * working on multiple images.
     *
     * Calling this method will actually start the image processing,
     * if no cached image is available.
     *
     * @return resource
     */
    public function createGdResource()
    {
        $bitSuccess = false;

        if (!$this->isCached(self::FORMAT_PNG)) {
            $bitSuccess = $this->processImage(self::FORMAT_PNG);
        } else {
            $strCacheFile = $this->getCachePath(self::FORMAT_PNG);
            $this->resource = imagecreatefrompng($strCacheFile);
            imagealphablending($this->resource, false);
            imagesavealpha($this->resource, true);
        }

        return $this->resource;
    }

    /**
     * Return the image cache ID.
     *
     * The cache ID is not set until one of the image output method is called.
     *
     * @return mixed
     */
    public function getCacheId()
    {
        if (!$this->isUpToDate) {
            $this->createGdResource();
        }

        return $this->cacheId;
    }

    /**
     * @param string $format
     * @return bool
     */
    private function processImage($format)
    {
        if (!$this->isUpToDate) {
            $bitSuccess = $this->finalLoadOrCreate();

            if (!$bitSuccess || !$this->applyOperations()) {
                return false;
            }

            $this->saveCache($format);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function finalLoadOrCreate()
    {
        $bitReturn = false;

        if ($this->resource != null) {
            imagedestroy($this->resource);
        }

        // Load existing file
        if ($this->originalPath != null) {
            $strFormat = self::getFormatFromFilename($this->originalPath);
            $strAbsolutePath = is_file($this->originalPath) ? $this->originalPath : $this->originalPath;

            switch ($strFormat) {
                case self::FORMAT_PNG:
                    $this->resource = imagecreatefrompng($strAbsolutePath);
                    $bitReturn = true;
                    break;

                case self::FORMAT_JPG:
                    $this->resource = imagecreatefromjpeg($strAbsolutePath);
                    $bitReturn = true;
                    break;

                case self::FORMAT_GIF:
                    $this->resource = imagecreatefromgif($strAbsolutePath);
                    $bitReturn = true;
                    break;
            }
        } // Create new file in memory
        else {
            $this->resource = imagecreatetruecolor($this->width, $this->height);
            $bitReturn = true;
        }

        if ($bitReturn) {
            $this->updateImageResource();
        }

        return $bitReturn;
    }

    /**
     * @return bool
     */
    private function applyOperations()
    {
        $bitReturn = true;

        foreach ($this->operations as $objOperation) {
            $oldResource = $this->resource;
            $bitReturn &= $objOperation->render($this->resource);

            if ($oldResource != $this->resource) {
                imagedestroy($oldResource);
                $this->updateImageResource();
            }
        }

        return $bitReturn;
    }

    /**
     * @param string $format
     * @param string $path
     * @return bool
     */
    private function outputImage($format, $path = null)
    {
        switch ($format) {
            case self::FORMAT_PNG:
                return imagepng($this->resource, $path);

            case self::FORMAT_JPG:
                return imagejpeg($this->resource, $path, $this->jpegQuality);

            case self::FORMAT_GIF:
                return imagegif($this->resource, $path);

            default:
                return false;
        }
    }

    /**
     * @param string $format
     * @return bool
     */
    private function isCached($format)
    {
        if (!$this->useCache || $this->isUpToDate) {
            return false;
        }

        $this->initCacheId($format);
        $strCachePath = $this->getCachePath($format);

        if (file_exists($strCachePath)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $format
     * @return void
     */
    private function saveCache($format)
    {
        if ($this->useCache) {
            $strCachePath = $this->getCachePath($format);

            $this->outputImage($format, $strCachePath);
            $this->isUpToDate = true;
        }
    }

    /**
     * @param string $format
     * @return string
     */
    private function getCachePath($format)
    {
        return $this->cachePath."c".$this->cacheId.".".$format;
    }

    /**
     * @param string $format
     * @return void
     */
    private function initCacheId($format)
    {
        $arrayValues = array($this->width, $this->height, $format);

        if ($this->originalPath != null) {
            $arrayValues[] = $this->originalPath;
            $arrayValues[] = filemtime($this->originalPath);
        }

        $strCacheId = self::buildCacheId("init", $arrayValues);

        foreach ($this->operations as $objOperation) {
            $strOpCacheName = "_".substr(get_class($objOperation), 12);
            $strOpCacheValues = $objOperation->getCacheIdValues();
            $strCacheId .= self::buildCacheId($strOpCacheName, $strOpCacheValues);
        }

        //echo "DEBUG: Cache Id: " . $strCacheId . "\n";
        $this->cacheId = md5($strCacheId);
    }

    /**
     * @return void
     */
    private function updateImageResource()
    {
        $this->width = imagesx($this->resource);
        $this->height = imagesy($this->resource);
        imagealphablending($this->resource, false);
        imagesavealpha($this->resource, true);
    }

    /**
     * Parses a color string into an RGB or RGBA array.
     *
     * Allowed color strings:
     * * Hexadecimal RGB string: #rrggbb
     * * Hexadecimal RGBA string: #rrggbbaa
     * * Decimal RGB color (color values between 0 and 255): rgb(255, 0, 16)
     * * Decimal RGBA color (as above with alpha between 0.0 and 1.0): rgba(255,0,16,0.9)
     *
     * @param string $color Color string.
     * @return array|bool RGB or RGBA values.
     */
    public static function parseColorRgb($color)
    {
        // Hex RGB(A) value, e.g. #FF0000 or #FF000022
        if (preg_match("/#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})?/i", $color, $arrMatches)) {
            $intRed = hexdec($arrMatches[1]);
            $intGreen = hexdec($arrMatches[2]);
            $intBlue = hexdec($arrMatches[3]);
            $arrColor = array($intRed, $intGreen, $intBlue);

            if (isset($arrMatches[4])) {
                // alpha is a value between 0 and 127
                $intAlpha = (int)(hexdec($arrMatches[4]) / 2);
                $arrColor[] = $intAlpha;
            }

            return $arrColor;
        } // Decimal RGB, e.g. rgb(255, 0, 16)
        elseif (preg_match("/rgb\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*\\)/i", $color, $arrMatches)) {
            $intRed = min((int)$arrMatches[1], 255);
            $intGreen = min((int)$arrMatches[2], 255);
            $intBlue = min((int)$arrMatches[3], 255);
            $arrColor = array($intRed, $intGreen, $intBlue);
            return $arrColor;
        } // Decimal RGBA, e.g. rgba(255, 0, 16, 0.8)
        elseif (preg_match("/rgba\\(\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d{1,3})\\s*,\\s*(\\d+(\\.\\d+)?)\\s*\\)/i", $color, $arrMatches)) {
            $intRed = min((int)$arrMatches[1], 255);
            $intGreen = min((int)$arrMatches[2], 255);
            $intBlue = min((int)$arrMatches[3], 255);

            // alpha is a value between 0 and 127
            $intAlpha = (int)(min((float)$arrMatches[4], 1.0) * 127.0);

            $arrColor = array($intRed, $intGreen, $intBlue, $intAlpha);
            return $arrColor;
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $values
     *
     * @return string
     */
    private static function buildCacheId($name, array $values)
    {
        $strValues = implode(",", $values);
        return $name."(".$strValues.")";
    }

    /**
     * @param string $path
     * @return string
     */
    private static function getFormatFromFilename($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension == "jpeg") {
            $extension = "jpg";
        }

        return strtolower($extension);
    }
}