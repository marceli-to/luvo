<?php
namespace App\Models;
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
	
	protected $appends = ['capitalizedSlug'];

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
   * Get capitalized
   */

  public function getCapitalizedSlugAttribute()
  {
    return ucfirst($this->slug);
  }
}