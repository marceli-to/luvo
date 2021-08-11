<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\TeamMember;
use App\Models\TeamMemberImage;
use App\Http\Requests\TeamMemberStoreRequest;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
  public function __construct(TeamMember $teamMember, TeamMemberImage $teamMemberImage)
  {
    $this->teamMember       = $teamMember;
    $this->teamMemberImage  = $teamMemberImage;
  }

  /**
   * Get a list of team members
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->teamMember->with('images', 'team')->orderBy('order')->get());
  }

  /**
   * Get a single team member for a given team member
   * 
   * @param TeamMember $teamMember
   * @return \Illuminate\Http\Response
   */
  public function find(TeamMember $teamMember)
  {
    $teamMember = $this->teamMember->with('images', 'publications')->findOrFail($teamMember->id);
    return response()->json($teamMember);
  }

  /**
   * Store a newly created team member
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(TeamMemberStoreRequest $request)
  {
    // Store team member
    $teamMember = new TeamMember([
      'firstname' => $request->input('firstname'),
      'name' => $request->input('name'),
      'description' => [
        'de' => $request->input('credits.de'),
        'fr' => $request->input('credits.fr'),
        'en' => $request->input('credits.en'),
      ],
      'description' => [
        'de' => $request->input('description.de'),
        'fr' => $request->input('description.fr'),
        'en' => $request->input('description.en'),
      ],
      'area' => [
        'de' => $request->input('area.de'),
        'fr' => $request->input('area.fr'),
        'en' => $request->input('area.en'),
      ],
      'languages' => [
        'de' => $request->input('languages.de'),
        'fr' => $request->input('languages.fr'),
        'en' => $request->input('languages.en'),
      ],
      'biography' => [
        'de' => $request->input('biography.de'),
        'fr' => $request->input('biography.fr'),
        'en' => $request->input('biography.en'),
      ],
      'membership' => [
        'de' => $request->input('membership.de'),
        'fr' => $request->input('membership.fr'),
        'en' => $request->input('membership.en'),
      ],
      'publication' => [
        'de' => $request->input('publication.de'),
        'fr' => $request->input('publication.fr'),
        'en' => $request->input('publication.en'),
      ],
      'team_id' => $request->input('team_id'),
      'publish' => $request->input('publish'),
    ]);

    $teamMember->save();

    // Store images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $image = new TeamMemberImage([
          'team_member_id' => $teamMember->id,
          'name'        => $i['name'],
          'caption'     => $i['caption'],
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
    return response()->json(['teamMemberId' => $teamMember->id]);
  }

  /**
   * Update a team member for a given team member
   *
   * @param TeamMember $teamMember
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(TeamMember $teamMember, TeamMemberStoreRequest $request)
  {
    $teamMember = $this->teamMember->findOrFail($teamMember->id);
    $teamMember->firstname = $request->input('firstname');
    $teamMember->name = $request->input('name');
    
    // German
    $teamMember->setTranslation('credits', 'de', $request->input('credits.de'));
    $teamMember->setTranslation('description', 'de', $request->input('description.de'));
    $teamMember->setTranslation('area', 'de', $request->input('area.de'));
    $teamMember->setTranslation('languages', 'de', $request->input('languages.de'));
    $teamMember->setTranslation('biography', 'de', $request->input('biography.de'));
    $teamMember->setTranslation('membership', 'de', $request->input('membership.de'));
    $teamMember->setTranslation('publication', 'de', $request->input('publication.de'));

    // English
    $teamMember->setTranslation('credits', 'en', $request->input('credits.en'));
    $teamMember->setTranslation('description', 'en', $request->input('description.en'));
    $teamMember->setTranslation('area', 'en', $request->input('area.en'));
    $teamMember->setTranslation('languages', 'en', $request->input('languages.en'));
    $teamMember->setTranslation('biography', 'en', $request->input('biography.en'));
    $teamMember->setTranslation('membership', 'en', $request->input('membership.en'));
    $teamMember->setTranslation('publication', 'en', $request->input('publication.en'));

    // French
    $teamMember->setTranslation('credits', 'fr', $request->input('credits.fr'));
    $teamMember->setTranslation('description', 'fr', $request->input('description.fr'));
    $teamMember->setTranslation('area', 'fr', $request->input('area.fr'));
    $teamMember->setTranslation('languages', 'fr', $request->input('languages.fr'));
    $teamMember->setTranslation('biography', 'fr', $request->input('biography.fr'));
    $teamMember->setTranslation('membership', 'fr', $request->input('membership.fr'));
    $teamMember->setTranslation('publication', 'fr', $request->input('publication.fr'));

    $teamMember->team_id = $request->input('team_id');
    $teamMember->publish = $request->input('publish');

    // Save changes
    $teamMember->save();

    // Update or add images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $image = TeamMemberImage::updateOrCreate(
          ['id' => $i['id']], 
          [
            'team_member_id' => $teamMember->id,
            'name'         => $i['name'],
            'caption'      => $i['caption'],
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
   * Toggle the status a given team member
   *
   * @param  TeamMember $teamMember
   * @return \Illuminate\Http\Response
   */
  public function toggle(TeamMember $teamMember)
  {
    $teamMember->publish = $teamMember->publish == 0 ? 1 : 0;
    $teamMember->save();
    return response()->json($teamMember->publish);
  }

  /**
   * Update the order of the given team members
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */

  public function order(Request $request)
  {
    $members = $request->get('members');
    foreach($members as $member)
    {
      $t = $this->teamMember->find($member['id']);
      $t->order = $member['order'];
      $t->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Remove a TeamMember
   *
   * \Observers\TeamMemberObserver observes and deletes child elements.
   * @param  TeamMember $teamMember
   * @return \Illuminate\Http\Response
   */
  public function destroy(TeamMember $teamMember)
  {
    $teamMember->delete();
    return response()->json('successfully deleted');
  }
}
