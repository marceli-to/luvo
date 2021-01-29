<?php
namespace App\Providers;
use App\Observers\HomeObserver;
use App\Observers\HomeImageObserver;
use App\Models\Home;
use App\Models\HomeImage;
use App\Observers\TeamObserver;
use App\Observers\TeamImageObserver;
use App\Models\Team;
use App\Models\TeamImage;
use App\Observers\TeamMemberObserver;
use App\Observers\TeamMemberImageObserver;
use App\Models\TeamMember;
use App\Models\TeamMemberImage;
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
    Home::observe(HomeObserver::class);
    HomeImage::observe(HomeImageObserver::class);
    Team::observe(TeamObserver::class);
    TeamImage::observe(TeamImageObserver::class);
    TeamMember::observe(TeamMemberObserver::class);
    TeamMemberImage::observe(TeamMemberImageObserver::class);
  }
}