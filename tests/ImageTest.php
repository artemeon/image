<?php
/*
 * This file is part of the Artemeon Core - Web Application Framework.
 *
 * (c) Artemeon <www.artemeon.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Artemeon\Image\Tests;

use Artemeon\Image\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testParseColorRgbHex()
    {
        list($red, $green, $blue) = Image::parseColorRgb("#ff0010");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);

        list($red, $green, $blue, $alpha) = Image::parseColorRgb("#FF0010FF");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 127);
    }

    public function testParseColorRgbDecimal()
    {
        list($red, $green, $blue) = Image::parseColorRgb("rgb(255,0,16)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);

        list($red, $green, $blue) = Image::parseColorRgb("rgb( 256, 0, 16 )");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
    }

    public function testParseColorRgbaDecimal()
    {
        list($red, $green, $blue, $alpha) = Image::parseColorRgb("rgba(255,0,16,1.0)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 127);

        list($red, $green, $blue, $alpha) = Image::parseColorRgb("rgba(255,0,16,1.5)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 127);

        list($red, $green, $blue, $alpha) = Image::parseColorRgb("rgba(255,0,16,00.83)");
        $this->assertEquals($red, 255);
        $this->assertEquals($green, 0);
        $this->assertEquals($blue, 16);
        $this->assertEquals($alpha, 105);
    }
}

