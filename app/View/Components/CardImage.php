<?php
namespace App\View\Components;
use Illuminate\View\Component;

class CardImage extends Component
{
  /**
   * Image
   *
   * @var object
   */
  public $image;

  /**
   * Template
   *
   * @var string
   */
  public $template;

  /**
   * Image max width
   *
   * @var string
   */
  public $maxWidth;

  /**
   * Image max height
   *
   * @var string
   */
  public $maxHeight;

  /**
   * Query string
   *
   * @var string
   */
  public $query;

  /**
   * Lazy loading
   *
   * @var boolean
   */
  public $hasLazy;

  /**
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct($image = NULL, $template = 'large', $maxWidth = NULL, $maxHeight = NULL, $hasLazy = FALSE)
  {
    $this->image     = $image;
    $this->template  = $template;
    $this->maxWidth  = $maxWidth;
    $this->maxHeight = $maxHeight;
    $this->query     = NULL;
    $this->hasLazy   = $hasLazy;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    if ($this->image->orientation == 'l')
    {
      $this->query = '?w='.$this->maxWidth.'&h='.$this->maxHeight;
      $width  = $this->maxWidth;
      $height = $this->maxHeight;
    }
    else
    {
      $this->query = '?w='.$this->maxHeight.'&h='.$this->maxWidth;
      $width  = $this->maxHeight;
      $height = $this->maxWidth;
    }

    if ($this->image->coords_w && $this->image->coords_h)
    {
      $this->query .= '&c=' . floor($this->image->coords_w) . ',' .  floor($this->image->coords_h) . ',' .  floor($this->image->coords_x) . ',' .  floor($this->image->coords_y);
    }

    return view('web.components.content.card-image', ['width' => $width, 'height' => $height]);
  }
}
