<?php
namespace App\Http\Controllers\Api;
use App\Support\Uploads;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Assistant;
use App\Models\AssistantImage;
use App\Http\Requests\AssistantStoreRequest;

class AssistantController extends Controller
{
  public function __construct(Assistant $assistant, AssistantImage $assistantImage)
  {
    $this->assistant       = $assistant;
    $this->assistantImage  = $assistantImage;
  }

  /**
   * Get a list of assistant
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->assistant->with('images', 'team')->get());
  }

  /**
   * Get a single assistant for a given assistant or authenticated assistant
   * 
   * @param Assistant $assistant
   * @return \Illuminate\Http\Response
   */
  public function find(Assistant $assistant)
  {
    $assistant = $this->assistant->with('images', 'team')->findOrFail($assistant->id);
    return response()->json($assistant);
  }

  /**
   * Store a newly created assistant
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(AssistantStoreRequest $request)
  {
    // Store assistant
    $assistant = new Assistant([
      'description' => [
        'de' => $request->input('description.de'),
        'fr' => $request->input('description.fr'),
        'en' => $request->input('description.en'),
      ],
      'assistants' => [
        'de' => $request->input('assistants.de') ? $request->input('assistants.de') : '',
        'fr' => $request->input('assistants.fr') ? $request->input('assistants.fr') : '',
        'en' => $request->input('assistants.en') ? $request->input('assistants.en') : '',
      ],
      'team_id' => $request->input('team_id'),
      'publish' => $request->input('publish'),
    ]);

    $assistant->save();

    // Store images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $image = new AssistantImage([
          'assistant_id'  => $assistant->id,
          'name'          => $i['name'],
          'caption'       => $i['caption'] ? $i['caption'] : NULL,
          'coords_w'      => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
          'coords_h'      => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
          'coords_x'      => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
          'coords_y'      => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
          'publish'       => $i['publish'] ? $i['publish'] : 0,
          'device'        => $i['device'] ? $i['device'] : NULL,
          'orientation'   => $i['orientation'] ? $i['orientation'] : NULL,
        ]);
        $image->save();
      }
    }
    return response()->json(['assistantId' => $assistant->id]);
  }

  /**
   * Update a Assistant for a given Assistant or authenticated Assistant
   *
   * @param Assistant $assistant
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Assistant $assistant, AssistantStoreRequest $request)
  {
    $assistant = $this->assistant->findOrFail($assistant->id);

    // German
    $assistant->setTranslation('description', 'de', $request->input('description.de'));
    $assistant->setTranslation('description', 'fr', $request->input('description.fr'));
    $assistant->setTranslation('description', 'en', $request->input('description.en'));

    $assistant->setTranslation('assistants', 'de', $request->input('assistants.de'));
    $assistant->setTranslation('assistants', 'fr', $request->input('assistants.fr'));
    $assistant->setTranslation('assistants', 'en', $request->input('assistants.en'));

    $assistant->team_id = $request->input('team_id');
    $assistant->publish = $request->input('publish');

    // Save changes
    $assistant->save();

    // Update or add images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $image = AssistantImage::updateOrCreate(
          ['id' => $i['id']], 
          [
            'assistant_id' => $assistant->id,
            'name'         => $i['name'],
            'caption'      => $i['caption'] ? $i['caption'] : NULL,
            'coords_w'     => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
            'coords_h'     => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
            'coords_x'     => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
            'coords_y'     => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
            'publish'      => $i['publish'] ? $i['publish'] : 0,
            'device'       => $i['device'] ? $i['device'] : NULL,
            'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
          ]
        );
      }
    }

    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given Assistant
   *
   * @param  Assistant $assistant
   * @return \Illuminate\Http\Response
   */
  public function toggle(Assistant $assistant)
  {
    $assistant->publish = $assistant->publish == 0 ? 1 : 0;
    $assistant->save();
    return response()->json($assistant->publish);
  }

  /**
   * Remove a Assistant
   *
   * \Observers\AssistantObserver observes and deletes child elements.
   * @param  Assistant $assistant
   * @return \Illuminate\Http\Response
   */
  public function destroy(Assistant $assistant)
  {
    // Images first: contact_images and assistant_images don't cascade, and
    // their uploads go too
    Uploads::deleteImages($assistant->images()->get());
    $assistant->delete();
    return response()->json('successfully deleted');
  }
}
