<?php
namespace App\View\Components;
use App\Support\ImageSupport;
use Illuminate\View\Component;

class Picture extends Component
{
  /**
   * Image
   *
   * @var object
   */
  public $image;

  public $queries;

  public $width;

  public $height;

  /**
   * Modern formats offered before the jpg fallback
   *
   * @var array
   */
  public $formats;

  /**
   * Show the caption below the picture
   *
   * @var bool
   */
  public $caption;

  /**
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct($image, $queries, $width, $height, $caption = true)
  {
    $this->image   = $image;
    // Callers pass 'min-width: 900px'; a media feature needs parentheses,
    // without them browsers read the query as "not all" and skip the source.
    $this->queries = array_map(fn ($q) => $q && $q[0] !== '(' ? '(' . $q . ')' : $q, $queries);
    $this->width   = $width;
    $this->height  = $height;
    $this->formats = ImageSupport::modernFormats();
    $this->caption = $caption;
  }

  /**
   * Image url for a size, with the image's saved crop (if any)
   * and an optional format (avif, webp)
   *
   * @param int $k index into width/height
   * @param string|null $format
   * @return string
   */
  public function src($k, $format = NULL)
  {
    $url = '/img/crop/' . $this->image->name . '/' . $this->width[$k] . '/' . $this->height[$k];

    // A crop needs width and height; x/y may be 0 (crop anchored top/left)
    if ($this->image->coords_w > 0 && $this->image->coords_h > 0)
    {
      $url .= '/' . implode(',', [
        (int) $this->image->coords_w,
        (int) $this->image->coords_h,
        (int) $this->image->coords_x,
        (int) $this->image->coords_y,
      ]);
    }

    return $format ? $url . '?fm=' . $format : $url;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    return view('web.components.content.picture');
  }
}
