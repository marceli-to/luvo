<?php
namespace App\Observers;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ContactObserver
{
  public function __construct(Contact $contact)
  {
    $this->contact = $contact;
  }

  /**
   * Handle the contact "created" event.
   *
   * @param  \App\Models\Contact $contact
   * @return void
   */
  public function created(Contact $contact)
  {
    //
  }

  /**
   * Handle the contact "updated" event.
   *
   * @param  \App\Models\Contact $contact
   * @return void
   */
  public function updated(Contact $contact)
  {
    //
  }

  /**
   * Handle the contact "deleting" event.
   *
   * @param  \App\Models\Contact $contact
   * @return void
   */
  public function deleting(Contact $contact)
  {
    $contact = $this->contact->with('images')->find($contact->id);
    foreach($contact->images as $image)
    {
      $directories = Storage::allDirectories('public');
      foreach($directories as $d)
      {
        Storage::delete($d . '/'. $image->name);
      }
    }
  }

  /**
   * Handle the contact "restored" event.
   *
   * @param  \App\Models\Contact $contact
   * @return void
   */
  public function restored(Contact $contact)
  {
    //
  }

  /**
   * Handle the contact "force deleted" event.
   *
   * @param  \App\Models\Contact $contact
   * @return void
   */
  public function forceDeleted(Contact $contact)
  {
    //
  }
}
