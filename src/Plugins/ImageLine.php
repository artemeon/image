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
 * Implements an operation to draw a line
 */
class ImageLine extends ImageAbstractOperation
{
    private $intStartX;
    private $intStartY;
    private $intEndX;
    private $intEndY;
    private $arrColor;

    //$intStartX, $intStartY, $intEndX, $intEndY, $intColor

    /**
     * @param int $intStartX
     * @param int $intStartY
     * @param int $intEndX
     * @param int $intEndY
     * @param string $strColor
     */
    public function __construct($intStartX, $intStartY, $intEndX, $intEndY, $strColor = "#FFFFFF")
    {
        $this->intStartX = $intStartX;
        $this->intStartY = $intStartY;
        $this->arrColor = Image::parseColorRgb($strColor);
        $this->intEndX = $intEndX;
        $this->intEndY = $intEndY;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        $intColor = $this->allocateColor($objResource, $this->arrColor);
        return imageline($objResource, $this->intStartX, $this->intStartY, $this->intEndX, $this->intEndY, $intColor);
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        $arrValues = array(
            $this->intStartX,
            $this->intStartY,
            $this->intEndX,
            $this->intEndY,
        );
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}