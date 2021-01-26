<?php
namespace App\Observers;
use App\Models\Home;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class HomeObserver
{
  public function __construct(Home $home)
  {
    $this->home = $home;
  }

  /**
   * Handle the home "created" event.
   *
   * @param  \App\Models\Home $home
   * @return void
   */
  public function created(Home $home)
  {
    //
  }

  /**
   * Handle the home "updated" event.
   *
   * @param  \App\Models\Home $home
   * @return void
   */
  public function updated(Home $home)
  {
    //
  }

  /**
   * Handle the home "deleting" event.
   *
   * @param  \App\Models\Home $home
   * @return void
   */
  public function deleting(Home $home)
  {
    $home = $this->home->with('images')->find($home->id);
    foreach($home->images as $image)
    {
      $directories = Storage::allDirectories('public');
      foreach($directories as $d)
      {
        Storage::delete($d . '/'. $image->name);
      }
    }
  }

  /**
   * Handle the home "restored" event.
   *
   * @param  \App\Models\Home $home
   * @return void
   */
  public function restored(Home $home)
  {
    //
  }

  /**
   * Handle the home "force deleted" event.
   *
   * @param  \App\Models\Home $home
   * @return void
   */
  public function forceDeleted(Home $home)
  {
    //
  }
}
