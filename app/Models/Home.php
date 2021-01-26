<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Home extends Base
{
	use HasTranslations;

	protected $table = 'home';

	public $translatable = [
		'title',
    'text',
  ];

	protected $fillable = [
		'title',
    'text',
		'publish',
  ];

	public function images()
	{
		return $this->hasMany('App\Models\HomeImage', 'home_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\HomeImage', 'home_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}
}