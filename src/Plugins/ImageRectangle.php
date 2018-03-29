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
 * Implements an operation to draw a rectangle
 */
class ImageRectangle extends ImageAbstractOperation
{
    private $intX;
    private $intY;
    private $intWidth;
    private $intHeight;
    private $arrColor;

    /**
     * @param int $intX
     * @param int $intY
     * @param int $intWidth
     * @param int $intHeight
     * @param string $strColor
     */
    public function __construct($intX, $intY, $intWidth, $intHeight, $strColor = "#FFFFFF")
    {
        $this->intX = $intX;
        $this->intY = $intY;
        $this->arrColor = Image::parseColorRgb($strColor);
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        $intColor = $this->allocateColor($objResource, $this->arrColor);
        return imagefilledrectangle($objResource, $this->intX, $this->intY, ($this->intX + $this->intWidth),
            ($this->intY + $this->intHeight), $intColor);
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        $arrValues = array(
            $this->intX,
            $this->intY,
            $this->intHeight,
            $this->intWidth,
        );
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}