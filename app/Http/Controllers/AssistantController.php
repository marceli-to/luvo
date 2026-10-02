<?php
namespace App\Http\Controllers;
use App\Models\Assistant;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class AssistantController extends BaseController
{
  protected $viewPath = 'web.pages.team.';

  public function __construct(Assistant $assistant)
  {
    parent::__construct();
    $this->assistant  = $assistant;
  }

  /**
   * Page: 'Assistant Vogt'
   *  
   * @return \Illuminate\Http\Response
   */

  public function vogt()
  { 
    $assistant = $this->_get('vogt');
    return view($this->viewPath . 'assistant', ['data' => $assistant]);
  }

  /**
   * Page: 'Assistant Luks'
   *  
   * @return \Illuminate\Http\Response
   */

  public function luks()
  { 
    $assistant = $this->_get('luks');
    return view($this->viewPath . 'assistant', ['data' => $assistant]);
  }

  public function _get($slug)
  {
    return $this->assistant
      ->published()
      ->with('publishedImages', 'team')
      ->whereHas('team', function($query) use ($slug) {
        $query->published()->where('slug', $slug);
      })->firstOrFail();
  }

}
