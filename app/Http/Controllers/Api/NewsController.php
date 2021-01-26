<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\News;
use App\Models\NewsImage;
use App\Http\Requests\NewsStoreRequest;
use Illuminate\Http\Request;

class NewsController extends Controller
{
  public function __construct(News $news, NewsImage $newsImage)
  {
    $this->news       = $news;
    $this->newsImage  = $newsImage;
  }

  /**
   * Get a list of news
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    return new DataCollection($this->news->with('images')->orderBy('order')->get());
  }

  /**
   * Get a single news for a given news or authenticated news
   * 
   * @param News $news
   * @return \Illuminate\Http\Response
   */
  public function find(News $news)
  {
    $news = $this->news->with('images')->findOrFail($news->id);
    return response()->json($news);
  }

  /**
   * Store a newly created News
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(NewsStoreRequest $request)
  {
    // Store news
    $news = new News([
      'title' => [
        'de' => $request->input('title.de'),
        'en' => $request->input('title.en'),
      ],
      'subtitle' => [
        'de' => $request->input('subtitle.de'),
        'en' => $request->input('subtitle.en'),
      ],
      'text' => [
        'de' => $request->input('text.de'),
        'en' => $request->input('text.en'),
      ],
      'publish' => $request->input('publish'),
    ]);

    $news->save();

    // Store images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $caption['de'] = isset($i['caption']['de']) ? $i['caption']['de'] : null;
        $caption['en'] = isset($i['caption']['en']) ? $i['caption']['en'] : null;

        $image = new NewsImage([
          'news_id' => $news->id,
          'name'       => $i['name'],
          'caption' => [
            'de' => $caption['de'],
            'en' => $caption['en'],    
          ],
          'coords_w'     => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
          'coords_h'     => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
          'coords_x'     => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
          'coords_y'     => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
          'preview'      => $i['preview'] ? $i['preview'] : 0,
          'publish'      => $i['publish'] ? $i['publish'] : 0,
          'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
        ]);
        $image->save();
      }
    }
    return response()->json(['newsId' => $news->id]);
  }

  /**
   * Update a News for a given News or authenticated News
   *
   * @param News $news
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(News $news, NewsStoreRequest $request)
  {
    $news = $this->news->findOrFail($news->id);

    // German
    $news->setTranslation('title', 'de', $request->input('title.de'));
    $news->setTranslation('subtitle', 'de', $request->input('subtitle.de'));
    $news->setTranslation('text', 'de', $request->input('text.de'));

    // English
    $news->setTranslation('title', 'en', $request->input('title.en'));
    $news->setTranslation('subtitle', 'en', $request->input('subtitle.en'));
    $news->setTranslation('text', 'en', $request->input('text.en'));

    $news->publish = $request->input('publish');

    // Save changes
    $news->save();

    // Update or add images
    if (!empty($request->images))
    {
      foreach($request->images as $i)
      {
        $caption['de'] = isset($i['caption']['de']) ? $i['caption']['de'] : null;
        $caption['en'] = isset($i['caption']['en']) ? $i['caption']['en'] : null;

        $image = NewsImage::updateOrCreate(
          ['id' => $i['id']], 
          [
            'news_id' => $news->id,
            'name'         => $i['name'],
            'caption' => [
              'de' => $caption['de'],
              'en' => $caption['en'],    
            ],
            'coords_w'     => $i['coords_w'] ? round($i['coords_w'], 12) : NULL,
            'coords_h'     => $i['coords_h'] ? round($i['coords_h'], 12) : NULL,
            'coords_x'     => $i['coords_x'] ? round($i['coords_x'], 12) : NULL,
            'coords_y'     => $i['coords_y'] ? round($i['coords_y'], 12) : NULL,
            'preview'      => $i['preview'] ? $i['preview'] : 0,
            'publish'      => $i['publish'] ? $i['publish'] : 0,
            'orientation'  => $i['orientation'] ? $i['orientation'] : NULL,
          ]
        );
      }
    }

    return response()->json('successfully updated');
  }

  /**
   * Update the order of the given newss
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function order(Request $request)
  {
    $newss = $request->get('news');
    foreach($newss as $news)
    {
      $p = $this->news->find($news['id']);
      $p->order = $news['order'];
      $p->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given News
   *
   * @param  News $news
   * @return \Illuminate\Http\Response
   */
  public function toggle(News $news)
  {
    $news->publish = $news->publish == 0 ? 1 : 0;
    $news->save();
    return response()->json($news->publish);
  }

  /**
   * Remove a News
   *
   * \Observers\NewsObserver observes and deletes child elements.
   * @param  News $news
   * @return \Illuminate\Http\Response
   */
  public function destroy(News $news)
  {
    $news->delete();
    return response()->json('successfully deleted');
  }
}
