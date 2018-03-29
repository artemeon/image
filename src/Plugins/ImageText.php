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
 * Implements a text rendering operation.
 */
class ImageText extends ImageAbstractOperation
{
    private $strText;
    private $intX;
    private $intY;
    private $floatSize;
    private $arrColor;
    private $strFont;
    private $floatAngle;

    /**
     * @param string $strText
     * @param int $intX
     * @param int $intY
     * @param double $floatSize
     * @param string $strColor
     * @param string $strFont
     * @param float $floatAngle
     */
    public function __construct($strText, $intX, $intY, $floatSize, $strColor = "#000000", $strFont = "dejavusans.ttf", $floatAngle = 0.0)
    {
        $this->strText = $strText;
        $this->intX = $intX;
        $this->intY = $intY;
        $this->floatSize = $floatSize;
        $this->arrColor = Image::parseColorRgb($strColor);
        $this->strFont = $strFont;
        $this->floatAngle = $floatAngle;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        $strFontPath = __DIR__ . "/../../fonts/" . $this->strFont;

        if ($strFontPath !== false && is_file($strFontPath)) {
            $intColor = $this->allocateColor($objResource, $this->arrColor);
            $strText = html_entity_decode($this->strText, ENT_COMPAT, "UTF-8");
            imagealphablending($objResource, true);
            imagefttext($objResource, $this->floatSize, $this->floatAngle, $this->intX, $this->intY, $intColor, $strFontPath, $strText);
            imagealphablending($objResource, false);
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        $arrValues = array(
            md5($this->strText),
            $this->intX,
            $this->intY,
            $this->floatSize,
            $this->strFont,
            $this->floatAngle
        );
        $arrValues = array_merge($arrValues, $this->arrColor);
        return $arrValues;
    }
}