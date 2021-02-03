<?php
namespace App\Observers;
use App\Models\ContactImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ContactImageObserver
{
  public function __construct(ContactImage $contactImage)
  {
    $this->contactImage = $contactImage;
  }

  /**
   * Handle the contactImage "created" event.
   *
   * @param  \App\Models\ContactImage $contactImage
   * @return void
   */
  public function created(ContactImage $contactImage)
  {
    //
  }

  /**
   * Handle the contactImage "updated" event.
   *
   * @param  \App\Models\ContactImage $contactImage
   * @return void
   */
  public function updated(ContactImage $contactImage)
  {
    //
  }

  /**
   * Handle the contactImage "deleting" event.
   *
   * @param  \App\Models\ContactImage $contactImage
   * @return void
   */
  public function deleting(ContactImage $contactImage)
  {
    $directories = Storage::allDirectories('public');
    foreach($directories as $d)
    {
      Storage::delete($d . '/'. $contactImage->name);
    }
  }

  /**
   * Handle the contactImage "restored" event.
   *
   * @param  \App\Models\ContactImage $contactImage
   * @return void
   */
  public function restored(ContactImage $contactImage)
  {
    //
  }

  /**
   * Handle the contactImage "force deleted" event.
   *
   * @param  \App\Models\ContactImage $contactImage
   * @return void
   */
  public function forceDeleted(ContactImage $contactImage)
  {
    //
  }
}
