<?php
namespace App\Observers;
use App\Models\Team;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TeamObserver
{
  public function __construct(Team $team)
  {
    $this->team = $team;
  }

  /**
   * Handle the team "created" event.
   *
   * @param  \App\Models\Team $team
   * @return void
   */
  public function created(Team $team)
  {
    //
  }

  /**
   * Handle the team "updated" event.
   *
   * @param  \App\Models\Team $team
   * @return void
   */
  public function updated(Team $team)
  {
    //
  }

  /**
   * Handle the team "deleting" event.
   *
   * @param  \App\Models\Team $team
   * @return void
   */
  public function deleting(Team $team)
  {
    $team = $this->team->with('images')->find($team->id);
    foreach($team->images as $image)
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
   * @param  \App\Models\Team $team
   * @return void
   */
  public function restored(Team $team)
  {
    //
  }

  /**
   * Handle the team "force deleted" event.
   *
   * @param  \App\Models\Team $team
   * @return void
   */
  public function forceDeleted(Team $team)
  {
    //
  }
}
