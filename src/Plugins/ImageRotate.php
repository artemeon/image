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

use Artemeon\Image\Image;

/**
 * Implements an image rotation operation.
 */
class ImageRotate extends ImageAbstractOperation
{
    private $floatAngle;
    private $arrColor;

    /**
     * @param double $floatAngle
     * @param string $strColor
     */
    public function __construct($floatAngle, $strColor = "#000000")
    {
        $this->floatAngle = $floatAngle;
        $this->arrColor = Image::parseColorRgb($strColor);
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        $intColor = $this->allocateColor($objResource, $this->arrColor);

        if ($intColor === null) {
            return false;
        }

        imagealphablending($objResource, true);
        $objResource = imagerotate($objResource, $this->floatAngle, $intColor);
        return true;
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        $arrValues = array($this->floatAngle);
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}