<?php
namespace App\View\Components;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Assistant;
use Illuminate\View\Component;

class MenuTeamMembers extends Component
{ 

  public $teamId;

  public $isMobileMenu;

  /**
   * Create a new component instance.
   * 
   * @param Team $team
   * @param TeamMember $teamMember
   * @param Assistant $assistant
   * @return void
   */
  public function __construct(Team $team, TeamMember $teamMember, Assistant $assistant, $teamId, $isMobileMenu = 0)
  {
    $this->team = $team;
    $this->teamMember = $teamMember;
    $this->assistant = $assistant;
    $this->teamId = $teamId;
    $this->isMobileMenu = $isMobileMenu;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    $team = $this->team->findOrFail($this->teamId);
    $members   = $this->teamMember->published()->orderBy('order')->where('team_id', '=', $this->teamId)->get();
    $assistant = $this->assistant->published()->where('team_id', '=', $this->teamId)->get()->first(); 
    return view('web.components.menu.team-members', ['team' => $team, 'members' => $members, 'assistant' => $assistant, 'isMobileMenu' => $this->isMobileMenu]);
  }
}
