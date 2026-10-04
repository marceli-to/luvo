<?php
namespace App\Models;

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
}
