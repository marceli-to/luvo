<?php
namespace App\Http\Controllers;
use App\Models\Team;

class TeamController extends Controller
{
  protected $viewPath = 'web.pages.team.';

  public function __construct(Team $team)
  {
    $this->team = $team;
  }

  /**
   * Page: 'Team Luks'
   *
   * @return \Illuminate\Http\Response
   */

  public function luks()
  { 
    $team = $this->team->published()->with('publishedImages')->where('slug', '=', 'luks')->firstOrFail();
    return view($this->viewPath . 'index', ['data' => $team]);
  }

  /**
   * Page: 'Team Vogt'
   *
   * @return \Illuminate\Http\Response
   */

  public function vogt()
  { 
    $team = $this->team->published()->with('publishedImages')->where('slug', '=', 'vogt')->firstOrFail();
    return view($this->viewPath . 'index', ['data' => $team]);
  }
}
