<?php
namespace App\Observers;
use App\Models\Post;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class NewsObserver
{
  public function __construct(Post $post, News $news)
  {
    $this->post = $post;
    $this->news = $news;
  }

  /**
   * Handle the news "created" event.
   *
   * @param  \App\Models\News $news
   * @return void
   */
  public function created(News $news)
  {
    //
  }

  /**
   * Handle the news "updated" event.
   *
   * @param  \App\Models\News $news
   * @return void
   */
  public function updated(News $news)
  {
    //
  }

  /**
   * Handle the news "deleting" event.
   *
   * @param  \App\Models\News $news
   * @return void
   */
  public function deleting(News $news)
  {
    $news = $this->news->with('images')->find($news->id);
    foreach($news->images as $image)
    {
      $posts = $this->post->where('news_image_id', '=', $image->id)->get();
      $postIds = $posts->map(function($post) {
        return $post->id;
      });
      $this->post->whereIn('id', $postIds)->delete();

      // Delete all images from storage
      $directories = Storage::allDirectories('public');
      foreach($directories as $d)
      {
        Storage::delete($d . '/'. $image->name);
      }
    }
  }

  /**
   * Handle the news "restored" event.
   *
   * @param  \App\Models\News $news
   * @return void
   */
  public function restored(News $news)
  {
    //
  }

  /**
   * Handle the news "force deleted" event.
   *
   * @param  \App\Models\News $news
   * @return void
   */
  public function forceDeleted(News $news)
  {
    //
  }
}
