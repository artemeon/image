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
class ImageScale extends ImageAbstractOperation
{
    private $intMaxWidth;
    private $intMaxHeight;

    /**
     * @param int $intMaxWidth
     * @param int $intMaxHeight
     */
    public function __construct($intMaxWidth, $intMaxHeight)
    {
        $this->intMaxWidth = $intMaxWidth;
        $this->intMaxHeight = $intMaxHeight;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        if ($this->intMaxWidth == null && $this->intMaxHeight == null) {
            return true;
        }

        $intCurrentWidth = imagesx($objResource);
        $intCurrentHeight = imagesy($objResource);
        $floatCurrentAspectRatio = (float)$intCurrentWidth / (float)$intCurrentHeight;

        // If max width or max height are not set calculate them according to the aspect ratio
        if ($this->intMaxWidth == null) {
            $this->intMaxWidth = (int)($this->intMaxHeight * $floatCurrentAspectRatio);
        }

        if ($this->intMaxHeight == null) {
            $this->intMaxHeight = (int)($this->intMaxWidth / $floatCurrentAspectRatio);
        }

        // Image is smaller then the max. limits, nothing to do.
        if ($intCurrentWidth <= $this->intMaxWidth && $intCurrentHeight <= $this->intMaxHeight) {
            return true;
        }

        $floatExpectedAspectRatio = (float)$this->intMaxWidth / (float)$this->intMaxHeight;

        $intNewWidth = $this->intMaxWidth;
        $intNewHeight = $this->intMaxHeight;

        // Decide which side gets scaled
        if ($floatCurrentAspectRatio > $floatExpectedAspectRatio) {
            $intNewHeight = $intNewWidth / $floatCurrentAspectRatio;
        } else {
            $intNewWidth = $intNewHeight * $floatCurrentAspectRatio;
        }

        // Scale the image
        $objScaledResource = $this->createImageResource($intNewWidth, $intNewHeight);
        $bitSuccess = imagecopyresampled($objScaledResource, $objResource,
            0, 0, // Destination X, Y
            0, 0, // Source X, Y
            $intNewWidth, $intNewHeight,
            $intCurrentWidth, $intCurrentHeight);

        if (!$bitSuccess) {
            imagedestroy($objScaledResource);
            return false;
        }

        $objResource = $objScaledResource;
        return true;
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        return array($this->intMaxWidth, $this->intMaxHeight);
    }
}