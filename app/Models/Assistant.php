<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Assistant extends Base
{
	use HasTranslations;

	public $translatable = [
		'description',
		'assistants'
  ];

	protected $fillable = [
		'description',
		'assistants',
    'publish',
    'team_id',
  ];

	public function team()
	{
		return $this->hasOne('App\Models\Team', 'id', 'team_id');
	}

  public function images()
	{
		return $this->hasMany('App\Models\AssistantImage', 'assistant_id', 'id')->orderBy('order');
	}

	public function publishedImages()
	{
		return $this->hasMany('App\Models\AssistantImage', 'assistant_id', 'id')->where('publish', '=', 1)->orderBy('order');
	}
}