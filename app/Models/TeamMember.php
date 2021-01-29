<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TeamMember extends Base
{
  use HasTranslations;

	public $translatable = [
    'description',
    'area',
    'biography',
    'membership',
  ];

	protected $fillable = [
    'firstname',
		'name',
		'description',
    'area',
    'biography',
    'membership',
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
}
