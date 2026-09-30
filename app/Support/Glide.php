<?php

namespace App\Support;

use League\Glide\Server;
use League\Glide\ServerFactory;

/**
 * The Glide server for /img/... (ImageController, images:warm).
 */
class Glide
{
	public static function server(): Server
	{
		return ServerFactory::create([
			'source' => storage_path('app/public'),
			'cache' => storage_path('app/.glide-cache'),
			'driver' => 'imagick',
		]);
	}
}
