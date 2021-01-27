<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;

class TeamCategory extends Base
{
	protected $fillable = [
		'name',
	];

  public function team()
  {
    return $this->belongsTo('App\Models\Team');
  }
}
