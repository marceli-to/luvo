<?php
namespace App\Models;
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
}
