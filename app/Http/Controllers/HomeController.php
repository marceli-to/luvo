<?php
namespace App\Http\Controllers;
use App\Models\Home;

class HomeController extends Controller
{
  protected $viewPath = 'web.pages.home.';

  public function __construct(Home $home)
  {
    $this->home = $home;
  }

  /**
   * Page: 'Home'
   *
   * @return \Illuminate\Http\Response
   */

  public function index()
  { 
    $data = $this->home->published()->with('publishedImages')->orderBy('id')->firstOrFail();
    return view($this->viewPath . 'index', ['data' => $data]);
  }
}
