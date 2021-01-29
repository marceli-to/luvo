<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TeamMemberImage extends Base
{
	use HasTranslations;

	public $translatable = [];

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
  
  public function teamMember()
  {
    return $this->belongsTo('App\Models\TeamMember');
  }
}
