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
 * Implements scaling and cropping an image to a fixed width and height.
 *
 * The image is scaled to the given size so that the aspect ratio does not change.
 */
class ImageScaleAndCrop extends ImageAbstractOperation
{
    private $intFixedWidth;
    private $intFixedHeight;

    /**
     * @param int $intFixedWidth
     * @param int $intFixedHeight
     */
    public function __construct($intFixedWidth, $intFixedHeight)
    {
        $this->intFixedWidth = $intFixedWidth;
        $this->intFixedHeight = $intFixedHeight;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        $intCurrentWidth = imagesx($objResource);
        $intCurrentHeight = imagesy($objResource);

        if ($this->intFixedWidth == null) {
            $this->intFixedWidth = $intCurrentWidth;
        }

        if ($this->intFixedHeight == null) {
            $this->intFixedHeight = $intCurrentHeight;
        }

        if ($this->intFixedWidth == $intCurrentWidth && $this->intFixedHeight == $intCurrentHeight) {
            return true;
        }

        $floatCurrentAspectRatio = (float)$intCurrentWidth / (float)$intCurrentHeight;
        $floatExpectedAspectRatio = (float)$this->intFixedWidth / (float)$this->intFixedHeight;

        $intSourceX = 0;
        $intSourceY = 0;
        $intSourceWidth = $intCurrentWidth;
        $intSourceHeight = $intCurrentHeight;

        if ($floatCurrentAspectRatio > $floatExpectedAspectRatio) {
            $intSourceWidth = $intSourceHeight * $floatExpectedAspectRatio;
            $intSourceX = ($intCurrentWidth - $intSourceWidth) / 2;
        } else {
            $intSourceHeight = $intSourceWidth / $floatExpectedAspectRatio;
            $intSourceY = ($intCurrentHeight - $intSourceHeight) / 2;
        }

        $objScaledResource = $this->createImageResource($this->intFixedWidth, $this->intFixedHeight);
        $bitSuccess = imagecopyresampled($objScaledResource, $objResource,
            0, 0, // Destination X, Y
            $intSourceX, $intSourceY, // Source X, Y
            $this->intFixedWidth, $this->intFixedHeight,
            $intSourceWidth, $intSourceHeight);

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
        return array($this->intFixedWidth, $this->intFixedHeight);
    }
}