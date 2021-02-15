<?php
namespace App\Http\Controllers;
use App\Models\TeamMember;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class TeamMemberController extends BaseController
{
  protected $viewPath = 'web.pages.team.';

  public function __construct(TeamMember $teamMember)
  {
    parent::__construct();
    $this->teamMember = $teamMember;
  }

  /**
   * Page: 'Team member'
   *  
   * @param TeamMember $teamMember
   * @return \Illuminate\Http\Response
   */

  public function index($slugTeam = NULL, $slug = NULL, TeamMember $teamMember)
  { 
    $teamMember = $this->teamMember->with('publishedImages', 'publications', 'team')->findOrFail($teamMember->id);
    return view($this->viewPath . 'member', ['data' => $teamMember]);
  }
}
