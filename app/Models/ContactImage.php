<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ContactImage extends Model
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
    'contact_id',
	];

  public function contact()
  {
    return $this->belongsTo('App\Models\Contact');
  }
}
