<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Publication;
use App\Http\Requests\PublicationStoreRequest;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
  public function __construct(Publication $publication)
  {
    $this->publication = $publication;
  }

  /**
   * Get a list of publications
   * 
   * @param TeamMember $teamMember
   * @return \Illuminate\Http\Response
   */
  public function get(TeamMember $teamMember)
  {
    return new DataCollection($this->publication->where('team_member_id', '=', $teamMember->id)->get());
  }

  /**
   * Get a single publication for a given publication
   * 
   * @param Publication $publication
   * @return \Illuminate\Http\Response
   */
  public function find(Publication $publication)
  {
    $publication = $this->publication->findOrFail($publication->id);
    return response()->json($publication);
  }

  /**
   * Store a newly created publication
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(PublicationStoreRequest $request)
  {
    // Store publication
    $publication = new Publication([
      'title' => [
        'de' => $request->input('title.de'),
        'fr' => $request->input('title.fr'),
        'en' => $request->input('title.en'),
      ],
      'description' => [
        'de' => $request->input('description.de'),
        'fr' => $request->input('description.fr'),
        'en' => $request->input('description.en'),
      ],
      'articles' => [
        'de' => $request->input('articles.de'),
        'fr' => $request->input('articles.fr'),
        'en' => $request->input('articles.en'),
      ],
      'team_member_id' => $request->input('team_member_id'),
      'publish' => $request->input('publish'),
    ]);

    $publication->save();
    return response()->json(['publicationId' => $publication->id]);
  }

  /**
   * Update a Publication for a given Publication or authenticated Publication
   *
   * @param Publication $publication
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Publication $publication, PublicationStoreRequest $request)
  {
    $publication = $this->publication->findOrFail($publication->id);

    // German
    $publication->setTranslation('title', 'de', $request->input('title.de'));
    $publication->setTranslation('description', 'de', $request->input('description.de'));
    $publication->setTranslation('articles', 'de', $request->input('articles.de'));

    // English
    $publication->setTranslation('title', 'fr', $request->input('title.fr'));
    $publication->setTranslation('description', 'fr', $request->input('description.fr'));
    $publication->setTranslation('articles', 'fr', $request->input('articles.fr'));

    // English
    $publication->setTranslation('title', 'en', $request->input('title.en'));
    $publication->setTranslation('description', 'en', $request->input('description.en'));
    $publication->setTranslation('articles', 'en', $request->input('articles.en'));

    $publication->team_member_id = $request->input('team_member_id');
    $publication->publish = $request->input('publish');

    // Save changes
    $publication->save();
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given Publication
   *
   * @param  Publication $publication
   * @return \Illuminate\Http\Response
   */
  public function toggle(Publication $publication)
  {
    $publication->publish = $publication->publish == 0 ? 1 : 0;
    $publication->save();
    return response()->json($publication->publish);
  }

  /**
   * Update the order of the given team publications
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function order(Request $request)
  {
    $publications = $request->get('publications');
    foreach($publications as $publication)
    {
      $p = $this->publication->find($publication['id']);
      $p->order = $publication['order'];
      $p->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Remove a Publication
   *
   * @param  Publication $publication
   * @return \Illuminate\Http\Response
   */
  public function destroy(Publication $publication)
  {
    $publication->delete();
    return response()->json('successfully deleted');
  }
}
