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

abstract class ImageAbstractOperation implements ImageOperationInterface
{
    /**
     * @param int $intWidth
     * @param int $intHeight
     *
     * @return resource
     */
    protected static function createImageResource($intWidth, $intHeight)
    {
        $objResource = imagecreatetruecolor($intWidth, $intHeight);
        imagealphablending($objResource, false); //crashes font-rendering, so set true before rendering fonts
        imagesavealpha($objResource, true);
        return $objResource;
    }

    /**
     * @param resource $objResource
     * @param array $arrColor
     *
     * @return int
     */
    protected function allocateColor($objResource, $arrColor)
    {
        $intColor = null;

        if (sizeof($arrColor) == 3) {
            $intColor = imagecolorallocate($objResource, $arrColor[0], $arrColor[1], $arrColor[2]);
        } elseif (sizeof($arrColor) == 4) {
            $intColor = imagecolorallocatealpha($objResource, $arrColor[0], $arrColor[1], $arrColor[2], $arrColor[3]);
        }

        return $intColor;
    }
}