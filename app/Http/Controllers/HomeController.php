<?php
namespace App\Http\Controllers;
use App\Models\Home;
use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
  protected $viewPath = 'web.pages.home.';

  public function __construct(Home $home)
  {
    parent::__construct();
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
