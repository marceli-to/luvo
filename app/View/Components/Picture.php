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

    // $coords = [
    //   $this->image->coords_w,
    //   $this->image->coords_h,
    //   $this->image->coords_x,
    //   $this->image->coords_y
    // ];

    $this->coords = '';
    if (($this->image->coords_w && $this->image->coords_h) && ($this->image->coords_x || $this->image->coords_y))
    {

      $w = $this->image->coords_w ? $this->image->coords_w : 0;
      $h = $this->image->coords_h ? $this->image->coords_h : 0;
      $x = $this->image->coords_x ? $this->image->coords_x : 0;
      $y = $this->image->coords_y ? $this->image->coords_y : 0;
      $this->coords = $w . ',' . $h . ',' . $x . ',' . $y;
    }

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
