<?php
namespace App\Models;

class TeamMemberImage extends Base
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
    'team_member_id',
  ];
}
