<?php
namespace App\Observers;
use App\Models\TeamImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TeamImageObserver
{
  public function __construct(TeamImage $teamImage)
  {
    $this->teamImage = $teamImage;
  }

  /**
   * Handle the teamImage "created" event.
   *
   * @param  \App\Models\TeamImage $teamImage
   * @return void
   */
  public function created(TeamImage $teamImage)
  {
    //
  }

  /**
   * Handle the teamImage "updated" event.
   *
   * @param  \App\Models\TeamImage $teamImage
   * @return void
   */
  public function updated(TeamImage $teamImage)
  {
    //
  }

  /**
   * Handle the teamImage "deleting" event.
   *
   * @param  \App\Models\TeamImage $teamImage
   * @return void
   */
  public function deleting(TeamImage $teamImage)
  {
    $directories = Storage::allDirectories('public');
    foreach($directories as $d)
    {
      Storage::delete($d . '/'. $teamImage->name);
    }
  }

  /**
   * Handle the teamImage "restored" event.
   *
   * @param  \App\Models\TeamImage $teamImage
   * @return void
   */
  public function restored(TeamImage $teamImage)
  {
    //
  }

  /**
   * Handle the teamImage "force deleted" event.
   *
   * @param  \App\Models\TeamImage $teamImage
   * @return void
   */
  public function forceDeleted(TeamImage $teamImage)
  {
    //
  }
}
