<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Home;
use App\Models\HomeImage;
use App\Http\Requests\HomeStoreRequest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
  public function __construct(Home $home, HomeImage $homeImage)
  {
    $this->home       = $home;
    $this->homeImage  = $homeImage;
  }

  /**
   * Get a list of home
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->home->with('images')->get());
  }

  /**
   * Get a single home for a given home or authenticated home
   * 
   * @param Home $home
   * @return \Illuminate\Http\Response
   */
  public function find(Home $home)
  {
    $home = $this->home->with('images')->findOrFail($home->id);
    return response()->json($home);
  }

  /**
   * Store a newly created home
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(HomeStoreRequest $request)
  {
    // Store home
    $home = new Home([
      'title' => [
        'de' => $request->input('title.de'),
        'fr' => $request->input('title.fr'),
        'en' => $request->input('title.en'),
      ],
      'text' => [
        'de' => $request->input('text.de'),
        'fr' => $request->input('text.fr'),
        'en' => $request->input('text.en'),
      ],
      'publish' => $request->input('publish'),
    ]);

    $home->save();

    // Store images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $caption['de'] = isset($i['caption']['de']) ? $i['caption']['de'] : null;
        $caption['fr'] = isset($i['caption']['fr']) ? $i['caption']['fr'] : null;
        $caption['en'] = isset($i['caption']['en']) ? $i['caption']['en'] : null;

        $image = new HomeImage([
          'home_id' => $home->id,
          'name'       => $i['name'],
          'caption' => [
            'de' => $caption['de'],
            'fr' => $caption['fr'],
            'en' => $caption['en'],    
          ],
          'coords_w'     => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
          'coords_h'     => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
          'coords_x'     => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
          'coords_y'     => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
          'publish'      => $i['publish'] ? $i['publish'] : 0,
          'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
        ]);
        $image->save();
      }
    }
    return response()->json(['homeId' => $home->id]);
  }

  /**
   * Update a Home for a given Home or authenticated Home
   *
   * @param Home $home
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Home $home, HomeStoreRequest $request)
  {
    $home = $this->home->findOrFail($home->id);

    // German
    $home->setTranslation('title', 'de', $request->input('title.de'));
    $home->setTranslation('text', 'de', $request->input('text.de'));

    // English
    $home->setTranslation('title', 'fr', $request->input('title.fr'));
    $home->setTranslation('text', 'fr', $request->input('text.fr'));

    // English
    $home->setTranslation('title', 'en', $request->input('title.en'));
    $home->setTranslation('text', 'en', $request->input('text.en'));

    $home->publish = $request->input('publish');

    // Save changes
    $home->save();

    // Update or add images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $caption['de'] = isset($i['caption']['de']) ? $i['caption']['de'] : null;
        $caption['fr'] = isset($i['caption']['fr']) ? $i['caption']['fr'] : null;
        $caption['en'] = isset($i['caption']['en']) ? $i['caption']['en'] : null;
        
        $image = HomeImage::updateOrCreate(
          ['id' => $i['id']], 
          [
            'home_id' => $home->id,
            'name'    => $i['name'],
            'caption' => [
              'de' => $caption['de'],
              'fr' => $caption['fr'],
              'en' => $caption['en'],    
            ],
            'coords_w'     => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
            'coords_h'     => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
            'coords_x'     => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
            'coords_y'     => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
            'publish'      => $i['publish'] ? $i['publish'] : 0,
            'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
          ]
        );
      }
    }

    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given Home
   *
   * @param  Home $home
   * @return \Illuminate\Http\Response
   */
  public function toggle(Home $home)
  {
    $home->publish = $home->publish == 0 ? 1 : 0;
    $home->save();
    return response()->json($home->publish);
  }

  /**
   * Remove a Home
   *
   * \Observers\HomeObserver observes and deletes child elements.
   * @param  Home $home
   * @return \Illuminate\Http\Response
   */
  public function destroy(Home $home)
  {
    $home->delete();
    return response()->json('successfully deleted');
  }
}
