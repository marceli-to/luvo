<?php
namespace App\View\Components;
use App\Stores\Cart;
use Illuminate\View\Component;

class CartIcon extends Component
{

  public $count;

  /**
   * Create a new component instance.
   *
   * @return void
   */
  public function __construct(Cart $cart)
  {
    $this->count = $cart->getCount();
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    return view('web.components.content.cart-icon');
  }
}
