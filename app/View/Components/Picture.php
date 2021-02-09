<?php
namespace App\View\Components;
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

  public $coords;

  /**
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct($image = NULL, $queries, $width, $height)
  {
    $this->image   = $image;
    $this->queries = $queries;
    $this->width   = $width;
    $this->height  = $height;

    $coords = [
      $this->image->coords_w,
      $this->image->coords_h,
      $this->image->coords_x,
      $this->image->coords_y
    ];

    $this->coords = implode(',', $coords);

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
