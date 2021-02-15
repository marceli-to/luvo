<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Team extends Base
{
	use HasTranslations;

	public $translatable = [
		'title',
    'text',
  ];

	protected $fillable = [
		'slug',
		'title',
		'text',
		'order',
		'publish',
  ];

	public function members()
	{
		return $this->hasMany('App\Models\TeamMember', 'team_id', 'id')->orderBy('order');
	}

	public function images()
	{
		return $this->hasMany('App\Models\TeamImage', 'team_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\TeamImage', 'team_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}

	/**
	 * Scope a query to only include a team by a given slug.
	 *
	 * @param  mixed  $slug
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function bySlug($slug)
	{
		return $this->where('slug', '=', $slug);
	}

  /**
   * Get capitalized
   */

  public function getCapitalizedSlugAttribute()
  {
    return ucfirst($this->slug);
  }

}