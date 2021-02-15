<?php
namespace App\View\Components;
use App\Models\Team;
use Illuminate\View\Component;

class MenuTeams extends Component
{ 
  /**
   * Create a new component instance.
   *  
   * @param Team $team
   * @return void
   */
  public function __construct(Team $team)
  {
    $this->team = $team;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    $teams = $this->team->published()->orderBy('order')->get();
    return view('web.components.menu.teams', ['data' => $teams]);
  }
}
