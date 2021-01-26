<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class HomeImage extends Model
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
    'publish',
    'order',
    'home_id',
	];

  public function home()
  {
    return $this->belongsTo('App\Models\Home');
  }
}
