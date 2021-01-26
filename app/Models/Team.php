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
    'category',
		'title',
    'text',
		'publish',
  ];

	public function images()
	{
		return $this->hasMany('App\Models\TeamImage', 'team_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\TeamImage', 'team_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}
}