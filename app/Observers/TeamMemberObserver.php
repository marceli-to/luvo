<?php
namespace App\Observers;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TeamMemberObserver
{
  public function __construct(TeamMember $teamMember )
  {
    $this->teamMember = $teamMember ;
  }

  /**
   * Handle the team "created" event.
   *
   * @param  \App\Models\TeamMember $teamMember 
   * @return void
   */
  public function created(TeamMember $teamMember )
  {
    //
  }

  /**
   * Handle the team "updated" event.
   *
   * @param  \App\Models\TeamMember $teamMember 
   * @return void
   */
  public function updated(TeamMember $teamMember )
  {
    //
  }

  /**
   * Handle the team "deleting" event.
   *
   * @param  \App\Models\TeamMember $teamMember 
   * @return void
   */
  public function deleting(TeamMember $teamMember )
  {
    $teamMember  = $this->teamMember->with('images')->find($teamMember ->id);
    foreach($teamMember ->images as $image)
    {
      $directories = Storage::allDirectories('public');
      foreach($directories as $d)
      {
        Storage::delete($d . '/'. $image->name);
      }
    }
  }

  /**
   * Handle the team "restored" event.
   *
   * @param  \App\Models\TeamMember $teamMember 
   * @return void
   */
  public function restored(TeamMember $teamMember )
  {
    //
  }

  /**
   * Handle the team "force deleted" event.
   *
   * @param  \App\Models\TeamMember $teamMember 
   * @return void
   */
  public function forceDeleted(TeamMember $teamMember )
  {
    //
  }
}
