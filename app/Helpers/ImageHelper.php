<?php

namespace App\Helpers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ImageHelper
{
  // static function large($image, $caption = NULL)
  // {
  //   $coords = '';
  //   if ($image->coords_w && $image->coords_h)
  //   {
  //     $coords =  floor($image->coords_w) . ',' .  floor($image->coords_h) . ',' .  floor($image->coords_x) . ',' .  floor($image->coords_y);
  //   }

  //   if ($image->orientation == 'l')
  //   {
  //     if ($coords != '')
  //     {
  //       return '<img src="/img/cache/'.$image->name.'?w=1600&h=1200&c='.$coords.'"  width="1600" height="1200" alt="'. $caption.'">';
  //     }
  //     return '<img src="/img/cache/'.$image->name.'"  width="1600" height="1200" alt="'. $caption.'">';
  //   }

  //   return '<img src="/img/cache/'.$image->name.'?w=1200&h=1600&c='.$coords.'" width="1200" height="1600" alt="'. $caption.'">';
  // }

  // static function post($image, $caption = NULL)
  // {
  //   $coords = '';
  //   if ($image->coords_w && $image->coords_h)
  //   {
  //     $coords =  floor($image->coords_w) . ',' .  floor($image->coords_h) . ',' .  floor($image->coords_x) . ',' .  floor($image->coords_y);
  //   }

  //   if ($image->orientation == 'l')
  //   {
  //     return '<img src="/img/cache/'.$image->name.'?w=800&h=600&c='.$coords.'"  width="800" height="600" alt="'. $caption.'">';
  //   }

  //   return '<img src="/img/cache/'.$image->name.'?w=600&h=800&c='.$coords.'"  width="600" height="800" alt="'. $caption.'">';
  // }

  // static function square($image, $caption = NULL)
  // {
  //   $coords = '';
  //   if ($image->coords_w && $image->coords_h)
  //   {
  //     $coords = floor($image->coords_w) . ',' .  floor($image->coords_h) . ',' .  floor($image->coords_x) . ',' .  floor($image->coords_y);
  //   }
  //   return '<img src="/img/cache/'.$image->name.'?w=900&h=900&c='.$coords.'"  width="600" height="600" alt="'. $caption.'">';
  // }

  // static function shopPreview($image, $caption = NULL)
  // {
  //   $coords = '';
  //   if ($image->coords_w && $image->coords_h)
  //   {
  //     $coords = floor($image->coords_w) . ',' .  floor($image->coords_h) . ',' .  floor($image->coords_x) . ',' .  floor($image->coords_y);
  //   }
  //   return '<img src="/img/shop-preview/'.$image->name.'?w=400&h=400&c='.$coords.'"  width="400" height="400" alt="'. $caption.'">';
  // }

  // static function shop($image, $caption = NULL)
  // {
  //   $coords = '';
  //   if ($image->coords_w && $image->coords_h)
  //   {
  //     $coords = floor($image->coords_w) . ',' .  floor($image->coords_h) . ',' .  floor($image->coords_x) . ',' .  floor($image->coords_y);
  //   }
  //   return '<img src="/img/shop/'.$image->name.'?w=1200&h=1200&c='.$coords.'"  width="1200" height="1200" alt="'. $caption.'">';
  // }

  static function thumbnail($image, $caption = NULL)
  {
    return '<img src="/img/thumbnail/'.$image->name .'"  width="600" height="600" alt="'. $caption.'">';
  }

  static function openGraphImage($image)
  {
    return "/img/cache/".$image->name."?w=1500&h=1500";
  }
}