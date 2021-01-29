<?php
namespace App\Observers;
use App\Models\TeamMemberImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TeamMemberImageObserver
{
  public function __construct(TeamMemberImage $teamMemberImage)
  {
    $this->teamMemberImage = $teamMemberImage;
  }

  /**
   * Handle the teamMemberImage "created" event.
   *
   * @param  \App\Models\TeamMemberImage $teamMemberImage
   * @return void
   */
  public function created(TeamMemberImage $teamMemberImage)
  {
    //
  }

  /**
   * Handle the teamMemberImage "updated" event.
   *
   * @param  \App\Models\TeamMemberImage $teamMemberImage
   * @return void
   */
  public function updated(TeamMemberImage $teamMemberImage)
  {
    //
  }

  /**
   * Handle the teamMemberImage "deleting" event.
   *
   * @param  \App\Models\TeamMemberImage $teamMemberImage
   * @return void
   */
  public function deleting(TeamMemberImage $teamMemberImage)
  {
    $directories = Storage::allDirectories('public');
    foreach($directories as $d)
    {
      Storage::delete($d . '/'. $teamMemberImage->name);
    }
  }

  /**
   * Handle the teamMemberImage "restored" event.
   *
   * @param  \App\Models\TeamMemberImage $teamMemberImage
   * @return void
   */
  public function restored(TeamMemberImage $teamMemberImage)
  {
    //
  }

  /**
   * Handle the teamMemberImage "force deleted" event.
   *
   * @param  \App\Models\TeamMemberImage $teamMemberImage
   * @return void
   */
  public function forceDeleted(TeamMemberImage $teamMemberImage)
  {
    //
  }
}
