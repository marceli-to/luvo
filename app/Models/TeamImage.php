<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TeamImage extends Model
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
    'usage',
    'publish',
    'order',
    'team_id',
	];

  public function team()
  {
    return $this->belongsTo('App\Models\Team');
  }
}
