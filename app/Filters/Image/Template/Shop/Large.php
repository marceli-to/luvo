<?php
namespace App\Filters\Image\Template\Shop;
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class Large implements FilterInterface
{
  protected $maxWidth;   
  protected $maxHeight;
  protected $coords;
  protected $orientation = 'landscape';

  public function __construct($maxWidth = null, $maxHeight = null, $coords = null)
  {
    $this->maxWidth  = $maxWidth ? $maxWidth : 1200;
    $this->maxHeight = $maxHeight ? $maxHeight : 1200;
    $this->coords    = $coords;
  }

  public function applyFilter(Image $image)
  {
    // Get image orientation
    $width  = $image->getWidth();
    $height = $image->getHeight();

    // Crop and resize if coords are set...
    if ($this->coords)
    {
      list($coords_w, $coords_h, $coords_x, $coords_y) = explode(',', $this->coords);
      return 
        $image->crop(floor(floatval($coords_w)), floor(floatval($coords_h)), floor(floatval($coords_x)), floor(floatval($coords_y)))
              ->fit($this->maxWidth, $this->maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
              });
    }
    else
    {
      return
        $image->fit($this->maxWidth, $this->maxHeight, function ($constraint) {
          $constraint->upsize();
      });
    }
  }
}