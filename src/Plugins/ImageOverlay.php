<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

namespace Artemeon\Image\Plugins;

use Artemeon\Image\Image;

/**
 */
class ImageOverlay extends ImageAbstractOperation
{
    /**
     * @var Image
     */
    private $objImage;
    private $intX;
    private $intY;
    private $bitAlphaBlending;

    /**
     * @param Image $objImage
     * @param int $intX
     * @param int $intY
     * @param bool $bitAlphaBlending
     */
    public function __construct(Image $objImage, $intX, $intY, $bitAlphaBlending = true)
    {
        $this->objImage = $objImage;
        $this->intX = $intX;
        $this->intY = $intY;
        $this->bitAlphaBlending = $bitAlphaBlending;
    }

    /**
     * @param resource &$objResource
     *
     * @return bool
     */
    public function render(&$objResource)
    {
        $objOverlayResource = $this->objImage->createGdResource();
        $intOverlayWidth = imagesx($objOverlayResource);
        $intOverlayHeight = imagesy($objOverlayResource);

        imagealphablending($objResource, $this->bitAlphaBlending);
        imagealphablending($objOverlayResource, $this->bitAlphaBlending);

        $bitSuccess = imagecopy($objResource, $objOverlayResource, $this->intX, $this->intY, 0, 0, $intOverlayWidth,
            $intOverlayHeight);

        imagealphablending($objResource, false);
        imagealphablending($objOverlayResource, false);

        return $bitSuccess;
    }

    /**
     * @return array
     */
    public function getCacheIdValues()
    {
        $arrValues = array(
            $this->objImage->getCacheId(),
            $this->intX,
            $this->intY,
            $this->bitAlphaBlending
        );
        return $arrValues;
    }
}