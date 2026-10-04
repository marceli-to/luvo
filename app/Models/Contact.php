<?php
namespace App\Models;
use Spatie\Translatable\HasTranslations;

class Contact extends Base
{
	use HasTranslations;

	public $translatable = [
		'address',
		'imprint',
		'privacy',
  ];

	protected $fillable = [
		'address',
		'imprint',
		'privacy',
		'map_uri',
		'publish',
  ];

  public function images()
	{
		return $this->hasMany('App\Models\ContactImage', 'contact_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\ContactImage', 'contact_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}
}