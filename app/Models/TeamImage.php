<?php
namespace App\Models;
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
    'device',
    'publish',
    'order',
    'team_id',
	];
}
