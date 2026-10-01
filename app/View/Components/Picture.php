<?php
namespace App\View\Components;
use App\Helpers\ImageHelper;
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
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct($image, $queries, $width, $height)
  {
    $this->image   = $image;
    // Callers pass 'min-width: 900px'; a media feature needs parentheses,
    // without them browsers read the query as "not all" and skip the source.
    $this->queries = array_map(fn ($q) => $q && $q[0] !== '(' ? '(' . $q . ')' : $q, $queries);
    $this->width   = $width;
    $this->height  = $height;
    $this->formats = ImageSupport::modernFormats();
  }

  /**
   * Image url for a size and an optional format (avif, webp)
   *
   * @param int $k index into width/height
   * @param string|null $format
   * @return string
   */
  public function src($k, $format = NULL)
  {
    return ImageHelper::cropUrl($this->image, $this->width[$k], $this->height[$k], $format);
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
