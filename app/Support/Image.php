<?php

namespace App\Support;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Compatibility shim for Intervention Image v3.
 * Keeps the v2 style call: Image::make($path)->resize($w, $h)->save($target)
 */
class Image
{
    public static function make($source): ImageInterface
    {
        return (new ImageManager(new Driver()))->read($source);
    }
}
