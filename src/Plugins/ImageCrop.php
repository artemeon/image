<?php
/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Artemeon\Image\Plugins;

/**
 * Implements an image scaling operation.
 * The scaling retains the aspect ration.
 */
class ImageCrop extends ImageAbstractOperation
{
    private $intX;
    private $intY;
    private $intWidth;
    private $intHeight;

    /**
     * @param int $intX
     * @param int $intY
     * @param int $intWidth
     * @param int $intHeight
     */
    public function __construct($intX, $intY, $intWidth, $intHeight)
    {
        $this->intX = $intX < 0 ? 0 : (int)$intX;
        $this->intY = $intY < 0 ? 0 : (int)$intY;
        $this->intWidth = (int)$intWidth;
        $this->intHeight = (int)$intHeight;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        // Crop the image
        $objCroppedResource = $this->createImageResource($this->intWidth, $this->intHeight);
        $bitSuccess = imagecopy($objCroppedResource, $objResource,
            0, 0, // Destination X, Y
            $this->intX, $this->intY, // Source X, Y
            $this->intWidth, $this->intHeight);

        if (!$bitSuccess) {
            imagedestroy($objCroppedResource);
            return false;
        }

        $objResource = $objCroppedResource;
        return true;
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        return array($this->intX, $this->intY, $this->intWidth, $this->intHeight);
    }
}