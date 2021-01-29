<?php
namespace App\Models;
use App\Models\Base;
use Illuminate\Database\Eloquent\Model;

class File extends Base
{
	protected $fillable = [
		'name',
    'size',
    'type',
	];
}
