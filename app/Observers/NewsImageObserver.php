<?php
namespace App\Observers;
use App\Models\Post;
use App\Models\NewsImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class NewsImageObserver
{
  public function __construct(Post $post, NewsImage $newsImage)
  {
    $this->post = $post;
    $this->newsImage = $newsImage;
  }

  /**
   * Handle the newsImage "created" event.
   *
   * @param  \App\Models\NewsImage $newsImage
   * @return void
   */
  public function created(NewsImage $newsImage)
  {
    //
  }

  /**
   * Handle the newsImage "updated" event.
   *
   * @param  \App\Models\NewsImage $newsImage
   * @return void
   */
  public function updated(NewsImage $newsImage)
  {
    //
  }

  /**
   * Handle the newsImage "deleting" event.
   *
   * @param  \App\Models\NewsImage $newsImage
   * @return void
   */
  public function deleting(NewsImage $newsImage)
  {
    // Delete all posts
    $posts = $this->post->where('news_image_id', '=', $newsImage->id)->get();
    $postIds = $posts->map(function($post) {
      return $post->id;
    });

    $this->post->whereIn('id', $postIds)->delete();

    // Delete all images from storage
    $directories = Storage::allDirectories('public');
    foreach($directories as $d)
    {
      Storage::delete($d . '/'. $newsImage->name);
    }
  }

  /**
   * Handle the newsImage "restored" event.
   *
   * @param  \App\Models\NewsImage $newsImage
   * @return void
   */
  public function restored(NewsImage $newsImage)
  {
    //
  }

  /**
   * Handle the newsImage "force deleted" event.
   *
   * @param  \App\Models\NewsImage $newsImage
   * @return void
   */
  public function forceDeleted(NewsImage $newsImage)
  {
    //
  }
}
