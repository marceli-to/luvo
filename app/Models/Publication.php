<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Publication extends Base
{
  use HasTranslations;

	public $translatable = [
    'title',
    'description',
    'articles',
  ];

	protected $fillable = [
		'title',
    'description',
		'articles',
    'publish',
    'order',
    'team_member_id',
  ];
  
  public function teamMember()
  {
    return $this->belongsTo('App\Models\TeamMember');
  }
}
