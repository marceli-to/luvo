<?php
// Render every URL from map.txt through Glide, porting image-cache's Crop rules:
// crop (if coords given and != 0,0,0,0) then scaleDown the longer side to 2400.
require __DIR__ . '/vendor/autoload.php';

use League\Glide\ServerFactory;

[$_, $scratch, $driver] = $argv + [null, null, 'gd'];
$src = '/Users/marceli.to/Jamon.digital/Webroot/luvo.ch/storage/app/public/uploads';
$out = "$scratch/glide-$driver-v2";
@mkdir($out);

$server = ServerFactory::create([
	'source' => $src,
	'cache' => "$scratch/glide-cache-$driver-v2",
	'driver' => $driver,
]);

foreach (file("$scratch/map.txt", FILE_IGNORE_NEW_LINES) as $line) {
	[$file, $old, , $url] = explode(' ', $line);
	if ($old !== '200') continue;

	$parts = explode('/', $url); // ['', 'img', 'crop', name, w, h, coords?]
	$name = $parts[3];
	$coords = $parts[6] ?? '';

	$params = ['q' => 75, 'fm' => 'jpg'];
	if ($coords !== '' && $coords !== '0,0,0,0') {
		$params['crop'] = $coords;
		[$w, $h] = array_map('intval', explode(',', $coords));
	} else {
		// Oriented source dimensions, as Intervention sees them after auto-orientation
		$img = (new Intervention\Image\ImageManager(new Intervention\Image\Drivers\Gd\Driver()))->decode("$src/$name");
		[$w, $h] = [$img->width(), $img->height()];
	}
	// Bound only the longer side; an unbounded other side stops Glide from
	// flooring the derived dimension (2400x1600.49 -> 2399x1600).
	$params['w'] = $w >= $h ? 2400 : 99999;
	$params['h'] = $w >= $h ? 99999 : 2400;
	$params['fit'] = 'max';

	$path = $server->makeImage($name, $params);
	file_put_contents("$out/$file", $server->getCache()->read($path));
	echo "$file " . http_build_query($params) . "\n";
}
