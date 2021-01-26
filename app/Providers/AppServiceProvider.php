<?php
namespace App\Providers;
use App\Observers\NewsObserver;
use App\Observers\NewsImageObserver;
use App\Models\News;
use App\Models\NewsImage;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    // setLocale(LC_ALL, 'de_CH.UTF-8');
    News::observe(NewsObserver::class);
    NewsImage::observe(NewsImageObserver::class);
  }
}
