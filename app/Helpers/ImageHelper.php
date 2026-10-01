<?php

namespace App\Helpers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ImageHelper
{
  static function thumbnail($image, $caption = NULL)
  {
    return '<img src="/img/thumbnail/'.$image->name .'"  width="600" height="600" alt="'. $caption.'">';
  }

  static function openGraphImage($image)
  {
    return "/img/crop/".$image->name."/1500/1500/0,0,0,0";
  }

  /**
   * Url of an image scaled to width/height, with its saved crop (if any)
   * and an optional format (avif, webp)
   *
   * @param object $image with name and coords_w/h/x/y
   * @param int $width
   * @param int $height
   * @param string|null $format
   * @return string
   */
  static function cropUrl($image, $width, $height, $format = NULL)
  {
    $url = '/img/crop/' . $image->name . '/' . $width . '/' . $height;

    // A crop needs width and height; x/y may be 0 (crop anchored top/left)
    if ($image->coords_w > 0 && $image->coords_h > 0)
    {
      $url .= '/' . implode(',', [
        (int) $image->coords_w,
        (int) $image->coords_h,
        (int) $image->coords_x,
        (int) $image->coords_y,
      ]);
    }

    return $format ? $url . '?fm=' . $format : $url;
  }
}