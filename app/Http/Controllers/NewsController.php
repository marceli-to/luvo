<?php
namespace App\Http\Controllers;
use App\Models\News;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class NewsController extends BaseController
{
  protected $viewPath = 'web.pages.news.';

  public function __construct(News $news)
  {
    parent::__construct();
    $this->news = $news;
  }

  /**
   * Page: 'News > Listing'
   *
   * @return \Illuminate\Http\Response
   */

  public function index()
  { 
    $news = $this->news->published()->with('previewImage')->orderBy('order')->get();
    return view($this->viewPath . 'listing', ['news' => $news]);
  }

  /**
   * Page: 'News > Detail'
   * @param News $news
   * @return \Illuminate\Http\Response
   */

  public function show($slug = NULL, News $news)
  { 
    $news = $this->news->with('publishedImages')->findOrFail($news->id);
    return view($this->viewPath . 'show', ['news' => $news]);
  }
}
