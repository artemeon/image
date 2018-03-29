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
 * Interface ImageOperationInterface
 * Each image-operation plugin has to implement this interface
 *
 * @since 4.3
 */
interface ImageOperationInterface
{

    /**
     * Implement the rendering of your operation in this method
     *
     * @param resource &$objResource
     *
     * @return mixed
     */
    public function render(&$objResource);

    /**
     * Return a characteristic of your plugin in order to include it into
     * the calculated cache checksum
     *
     * @return mixed
     */
    public function getCacheIdValues();
}