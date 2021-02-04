<?php
namespace App\Observers;
use App\Models\AssistantImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class AssistantImageObserver
{
  public function __construct(AssistantImage $assistantImage)
  {
    $this->assistantImage = $assistantImage;
  }

  /**
   * Handle the assistantImage "created" event.
   *
   * @param  \App\Models\AssistantImage $assistantImage
   * @return void
   */
  public function created(AssistantImage $assistantImage)
  {
    //
  }

  /**
   * Handle the assistantImage "updated" event.
   *
   * @param  \App\Models\AssistantImage $assistantImage
   * @return void
   */
  public function updated(AssistantImage $assistantImage)
  {
    //
  }

  /**
   * Handle the assistantImage "deleting" event.
   *
   * @param  \App\Models\AssistantImage $assistantImage
   * @return void
   */
  public function deleting(AssistantImage $assistantImage)
  {
    $directories = Storage::allDirectories('public');
    foreach($directories as $d)
    {
      Storage::delete($d . '/'. $assistantImage->name);
    }
  }

  /**
   * Handle the assistantImage "restored" event.
   *
   * @param  \App\Models\AssistantImage $assistantImage
   * @return void
   */
  public function restored(AssistantImage $assistantImage)
  {
    //
  }

  /**
   * Handle the assistantImage "force deleted" event.
   *
   * @param  \App\Models\AssistantImage $assistantImage
   * @return void
   */
  public function forceDeleted(AssistantImage $assistantImage)
  {
    //
  }
}
