<?php
namespace App\Observers;
use App\Models\HomeImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class HomeImageObserver
{
  public function __construct(HomeImage $homeImage)
  {
    $this->homeImage = $homeImage;
  }

  /**
   * Handle the homeImage "created" event.
   *
   * @param  \App\Models\HomeImage $homeImage
   * @return void
   */
  public function created(HomeImage $homeImage)
  {
    //
  }

  /**
   * Handle the homeImage "updated" event.
   *
   * @param  \App\Models\HomeImage $homeImage
   * @return void
   */
  public function updated(HomeImage $homeImage)
  {
    //
  }

  /**
   * Handle the homeImage "deleting" event.
   *
   * @param  \App\Models\HomeImage $homeImage
   * @return void
   */
  public function deleting(HomeImage $homeImage)
  {
    $directories = Storage::allDirectories('public');
    foreach($directories as $d)
    {
      Storage::delete($d . '/'. $homeImage->name);
    }
  }

  /**
   * Handle the homeImage "restored" event.
   *
   * @param  \App\Models\HomeImage $homeImage
   * @return void
   */
  public function restored(HomeImage $homeImage)
  {
    //
  }

  /**
   * Handle the homeImage "force deleted" event.
   *
   * @param  \App\Models\HomeImage $homeImage
   * @return void
   */
  public function forceDeleted(HomeImage $homeImage)
  {
    //
  }
}
