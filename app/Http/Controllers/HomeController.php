<?php
namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
  protected $viewPath = 'web.pages.';

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Page: 'Home'
   *
   * @return \Illuminate\Http\Response
   */

  public function index()
  { 
    return view($this->viewPath . 'home');
  }
}
