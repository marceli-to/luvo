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
   * Page: 'Assistant'
   *  
   * @param Assistant $assistant
   * @return \Illuminate\Http\Response
   */

  public function index($slugTeam = NULL, $slug = NULL, Assistant $assistant)
  { 
    $assistant = $this->assistant->with('publishedImages', 'team')->findOrFail($assistant->id);
    return view($this->viewPath . 'assistant', ['data' => $assistant]);
  }
}
