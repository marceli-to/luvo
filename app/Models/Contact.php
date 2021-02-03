<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Contact extends Base
{
	use HasTranslations;

	public $translatable = [
		'address',
    'imprint',
  ];

	protected $fillable = [
		'address',
		'imprint',
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