<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Team;
use App\Models\TeamImage;
use App\Http\Requests\TeamStoreRequest;
use Illuminate\Http\Request;

class TeamController extends Controller
{
  public function __construct(Team $team, TeamImage $teamImage)
  {
    $this->team       = $team;
    $this->teamImage  = $teamImage;
  }

  /**
   * Get a list of team
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->team->with('images', 'category')->get());
  }

  /**
   * Get a single team for a given team or authenticated team
   * 
   * @param Team $team
   * @return \Illuminate\Http\Response
   */
  public function find(Team $team)
  {
    $team = $this->team->with('images')->findOrFail($team->id);
    return response()->json($team);
  }

  /**
   * Store a newly created team
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(TeamStoreRequest $request)
  {
    // Store team
    $team = new Team([
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
      'category_id' => $request->input('category_id'),
      'publish' => $request->input('publish'),
    ]);

    $team->save();

    // Store images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $caption['de'] = isset($i['caption']['de']) ? $i['caption']['de'] : null;
        $caption['fr'] = isset($i['caption']['fr']) ? $i['caption']['fr'] : null;
        $caption['en'] = isset($i['caption']['en']) ? $i['caption']['en'] : null;

        $image = new TeamImage([
          'team_id' => $team->id,
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
          'device'        => $i['device'] ? $i['device'] : NULL,
          'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
        ]);
        $image->save();
      }
    }
    return response()->json(['teamId' => $team->id]);
  }

  /**
   * Update a Team for a given Team or authenticated Team
   *
   * @param Team $team
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Team $team, TeamStoreRequest $request)
  {
    $team = $this->team->findOrFail($team->id);

    // German
    $team->setTranslation('title', 'de', $request->input('title.de'));
    $team->setTranslation('text', 'de', $request->input('text.de'));

    // English
    $team->setTranslation('title', 'fr', $request->input('title.fr'));
    $team->setTranslation('text', 'fr', $request->input('text.fr'));

    // English
    $team->setTranslation('title', 'en', $request->input('title.en'));
    $team->setTranslation('text', 'en', $request->input('text.en'));

    $team->category_id = $request->input('category_id');
    $team->publish = $request->input('publish');

    // Save changes
    $team->save();

    // Update or add images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $caption['de'] = isset($i['caption']['de']) ? $i['caption']['de'] : null;
        $caption['fr'] = isset($i['caption']['fr']) ? $i['caption']['fr'] : null;
        $caption['en'] = isset($i['caption']['en']) ? $i['caption']['en'] : null;
        
        $image = TeamImage::updateOrCreate(
          ['id' => $i['id']], 
          [
            'team_id' => $team->id,
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
            'device'        => $i['device'] ? $i['device'] : NULL,
            'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
          ]
        );
      }
    }

    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given Team
   *
   * @param  Team $team
   * @return \Illuminate\Http\Response
   */
  public function toggle(Team $team)
  {
    $team->publish = $team->publish == 0 ? 1 : 0;
    $team->save();
    return response()->json($team->publish);
  }

  /**
   * Remove a Team
   *
   * \Observers\TeamObserver observes and deletes child elements.
   * @param  Team $team
   * @return \Illuminate\Http\Response
   */
  public function destroy(Team $team)
  {
    $team->delete();
    return response()->json('successfully deleted');
  }
}
