<?php
namespace App\Observers;
use App\Models\Assistant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class AssistantObserver
{
  public function __construct(Assistant $assistant)
  {
    $this->assistant = $assistant;
  }

  /**
   * Handle the assistant "created" event.
   *
   * @param  \App\Models\Assistant $assistant
   * @return void
   */
  public function created(Assistant $assistant)
  {
    //
  }

  /**
   * Handle the assistant "updated" event.
   *
   * @param  \App\Models\Assistant $assistant
   * @return void
   */
  public function updated(Assistant $assistant)
  {
    //
  }

  /**
   * Handle the assistant "deleting" event.
   *
   * @param  \App\Models\Assistant $assistant
   * @return void
   */
  public function deleting(Assistant $assistant)
  {
    $assistant = $this->assistant->with('images')->find($assistant->id);
    foreach($assistant->images as $image)
    {
      $directories = Storage::allDirectories('public');
      foreach($directories as $d)
      {
        Storage::delete($d . '/'. $image->name);
      }
    }
  }

  /**
   * Handle the assistant "restored" event.
   *
   * @param  \App\Models\Assistant $assistant
   * @return void
   */
  public function restored(Assistant $assistant)
  {
    //
  }

  /**
   * Handle the assistant "force deleted" event.
   *
   * @param  \App\Models\Assistant $assistant
   * @return void
   */
  public function forceDeleted(Assistant $assistant)
  {
    //
  }
}
