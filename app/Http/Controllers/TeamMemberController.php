<?php
namespace App\Http\Controllers;
use App\Models\TeamMember;

class TeamMemberController extends Controller
{
  protected $viewPath = 'web.pages.team.';

  public function __construct(TeamMember $teamMember)
  {
    $this->teamMember = $teamMember;
  }

  /**
   * Page: 'Team member'; the team and member slugs in the url are
   * cosmetic, the member is found by its id
   *
   * @param string $slugTeam
   * @param string $slugMember
   * @param TeamMember $teamMember
   * @return \Illuminate\Http\Response
   */

  public function index(string $slugTeam, string $slugMember, TeamMember $teamMember)
  {
    $teamMember = $this->teamMember
      ->published()
      ->whereHas('team', fn ($query) => $query->published())
      ->with('publishedImages', 'publishedPublications', 'team')
      ->findOrFail($teamMember->id);
    return view($this->viewPath . 'member', ['data' => $teamMember]);
  }
}
