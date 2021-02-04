<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;

class AssistantImage extends Base
{
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
    'assistant_id',
	];

  public function contact()
  {
    return $this->belongsTo('App\Models\Assistant');
  }
}
