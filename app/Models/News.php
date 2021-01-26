<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class News extends Base
{
	use HasTranslations;

	public $translatable = [
		'title',
		'subtitle',
    'text',
	];

	protected $fillable = [
		'title',
		'subtitle',
    'text',
		'order',
		'publish',
  ];

	public function images()
	{
		return $this->hasMany('App\Models\NewsImage', 'news_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\NewsImage', 'news_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}

	public function previewImage()
	{
		return $this->hasOne('App\Models\NewsImage', 'news_id', 'id')->where('publish', '=', 1)->where('preview', '=', 1);
	}
}
