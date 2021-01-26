<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class NewsImage extends Model
{
  use HasTranslations;

	public $translatable = [
    'caption'
  ];
  
	protected $fillable = [
		'name',
    'caption',
		'coords_w',
    'coords_h',
    'coords_x',
    'coords_y',
    'orientation',
    'preview',
    'publish',
    'order',
    'news_id',
	];

  public function news()
  {
    return $this->belongsTo('App\Models\News');
  }
}
