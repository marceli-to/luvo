<?php
namespace App\Http\Controllers\Api;
use App\Support\Uploads;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Contact;
use App\Models\ContactImage;
use App\Http\Requests\ContactStoreRequest;

class ContactController extends Controller
{
  public function __construct(Contact $contact, ContactImage $contactImage)
  {
    $this->contact       = $contact;
    $this->contactImage  = $contactImage;
  }

  /**
   * Get a list of contact
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->contact->with('images')->get());
  }

  /**
   * Get a single contact for a given contact or authenticated contact
   * 
   * @param Contact $contact
   * @return \Illuminate\Http\Response
   */
  public function find(Contact $contact)
  {
    $contact = $this->contact->with('images')->findOrFail($contact->id);
    return response()->json($contact);
  }

  /**
   * Store a newly created contact
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(ContactStoreRequest $request)
  {
    // Store contact
    $contact = new Contact([
      'address' => [
        'de' => $request->input('address.de'),
        'fr' => $request->input('address.fr'),
        'en' => $request->input('address.en'),
      ],
      'imprint' => [
        'de' => $request->input('imprint.de'),
        'fr' => $request->input('imprint.fr'),
        'en' => $request->input('imprint.en'),
      ],
      'privacy' => [
        'de' => $request->input('privacy.de'),
        'fr' => $request->input('privacy.fr'),
        'en' => $request->input('privacy.en'),
      ],
      'map_uri' => $request->input('map_uri'),
      'publish' => $request->input('publish'),
    ]);

    $contact->save();

    // Store images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $image = new ContactImage([
          'contact_id'  => $contact->id,
          'name'        => $i['name'],
          'caption'     => $i['caption'] ? $i['caption'] : NULL,
          'coords_w'    => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
          'coords_h'    => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
          'coords_x'    => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
          'coords_y'    => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
          'publish'     => $i['publish'] ? $i['publish'] : 0,
          'device'      => $i['device'] ? $i['device'] : NULL,
          'orientation' => $i['orientation'] ? $i['orientation'] : NULL,
        ]);
        $image->save();
      }
    }
    return response()->json(['contactId' => $contact->id]);
  }

  /**
   * Update a Contact for a given Contact or authenticated Contact
   *
   * @param Contact $contact
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Contact $contact, ContactStoreRequest $request)
  {
    $contact = $this->contact->findOrFail($contact->id);

    // German
    $contact->setTranslation('address', 'de', $request->input('address.de'));
    $contact->setTranslation('imprint', 'de', $request->input('imprint.de'));
    $contact->setTranslation('privacy', 'de', $request->input('privacy.de'));

    // French
    $contact->setTranslation('address', 'fr', $request->input('address.fr'));
    $contact->setTranslation('imprint', 'fr', $request->input('imprint.fr'));
    $contact->setTranslation('privacy', 'fr', $request->input('privacy.fr'));

    // English
    $contact->setTranslation('address', 'en', $request->input('address.en'));
    $contact->setTranslation('imprint', 'en', $request->input('imprint.en'));
    $contact->setTranslation('privacy', 'en', $request->input('privacy.en'));
    
    $contact->map_uri = $request->input('map_uri');
    $contact->publish = $request->input('publish');

    // Save changes
    $contact->save();

    // Update or add images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $image = ContactImage::updateOrCreate(
          ['id' => $i['id']], 
          [
            'contact_id'  => $contact->id,
            'name'        => $i['name'],
            'caption'     => $i['caption'] ? $i['caption'] : NULL,
            'coords_w'    => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
            'coords_h'    => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
            'coords_x'    => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
            'coords_y'    => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
            'publish'     => $i['publish'] ? $i['publish'] : 0,
            'device'      => $i['device'] ? $i['device'] : NULL,
            'orientation' => $i['orientation'] ? $i['orientation'] : NULL,
          ]
        );
      }
    }

    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given Contact
   *
   * @param  Contact $contact
   * @return \Illuminate\Http\Response
   */
  public function toggle(Contact $contact)
  {
    $contact->publish = $contact->publish == 0 ? 1 : 0;
    $contact->save();
    return response()->json($contact->publish);
  }

  /**
   * Remove a Contact
   *
   * \Observers\ContactObserver observes and deletes child elements.
   * @param  Contact $contact
   * @return \Illuminate\Http\Response
   */
  public function destroy(Contact $contact)
  {
    // Images first: contact_images and assistant_images don't cascade, and
    // their uploads go too
    Uploads::deleteImages($contact->images()->get());
    $contact->delete();
    return response()->json('successfully deleted');
  }
}
