<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TeamMember extends Base
{
  use HasTranslations;

	public $translatable = [
    'credits',
    'description',
    'area',
    'languages',
    'biography',
    'membership',
    'publication',
    'meta_description'
  ];

	protected $fillable = [
    'firstname',
    'name',
    'credits',
		'description',
    'area',
    'languages',
    'biography',
    'membership',
    'publication',
		'order',
    'publish',
    'team_id',
  ];

	public function team()
	{
		return $this->hasOne('App\Models\Team', 'id', 'team_id');
	}

	public function images()
	{
		return $this->hasMany('App\Models\TeamMemberImage', 'team_member_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\TeamMemberImage', 'team_member_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}

	public function publications()
	{
		return $this->hasMany('App\Models\Publication', 'team_member_id', 'id')->orderBy('order');
  }
  
	public function publishedPublications()
	{
		return $this->hasMany('App\Models\Publication', 'team_member_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}

  /**
   * Get fullname
   */

  public function getFullnameAttribute()
  {
    return $this->firstname . ' ' . $this->name;
	}
	
  /**
   * Get slug
   */

  public function getSlugAttribute()
  {
    return \Str::slug($this->firstname . ' ' . $this->name, '-');
  }
}
