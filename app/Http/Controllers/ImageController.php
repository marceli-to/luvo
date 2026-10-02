<?php
namespace App\Http\Controllers;
use App\Support\Glide;
use App\Support\ImageSupport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves /img/... through Glide, keeping the URL shapes of the former
 * marceli-to/image-cache package:
 *
 *   /img/original/{file}
 *   /img/thumbnail/{file}
 *   /img/crop/{file}/{maxWidth?}/{maxHeight?}/{coords?}   coords = w,h,x,y
 *
 * Every variant accepts ?fm=avif|webp|jpg. Only the sizes in SIZES and those
 * formats are rendered, so arbitrary URLs can't fill the Glide cache.
 */
class ImageController extends Controller
{
  public const MAX_SIZE = 2400;

  public const FORMAT_QUALITY = ['jpg' => 75, 'webp' => 80, 'avif' => 70];

  /**
   * maxWidth x maxHeight pairs the site and the admin request: the <picture>
   * slots in the public views, the admin previews (1600x1000, 1000x1600) and
   * the Open Graph image (1500x1500).
   */
  public const SIZES = [
    '900x560', '900x600', '1200x750', '1200x800', '1200x1200', '1500x1500',
    '1600x1000', '1000x1600', '1600x1200', '1600x1920', '2400x1500',
  ];

  protected Server $server;

  public function __construct()
  {
    $this->server = Glide::server();
  }

  public function original(string $filename): BinaryFileResponse
  {
    return response()->file($this->source($filename), $this->cacheHeaders());
  }

  public function thumbnail(Request $request, string $filename): Response
  {
    $this->source($filename);

    return $this->respond($filename, ['w' => 300, 'h' => 300, 'fit' => 'crop'], $request);
  }

  /**
   * Crop to the saved coords, then scale down: landscape to maxWidth,
   * portrait to maxHeight (orientation taken after the crop).
   */
  public function crop(Request $request, string $filename, ?string $maxWidth = null, ?string $maxHeight = null, ?string $coords = null): Response
  {
    $source = $this->source($filename);
    abort_unless($maxWidth === null || in_array("{$maxWidth}x{$maxHeight}", self::SIZES, true), 404);
    $maxWidth = $this->dimension($maxWidth);
    $maxHeight = $this->dimension($maxHeight);

    $params = [];
    $crop = $this->coords($coords);

    if ($crop) {
      $params['crop'] = implode(',', $crop);
      [$width, $height] = $crop;
    }
    else {
      [$width, $height] = $this->orientedSize($source);
    }

    // Bound one side only; an unbounded other side keeps Glide from flooring
    // the derived dimension (2400 x 1600.49 would become 2399 x 1600).
    $portrait = $height > $width;
    $params['w'] = $portrait ? 99999 : $maxWidth;
    $params['h'] = $portrait ? $maxHeight : 99999;
    $params['fit'] = 'max';

    return $this->respond($filename, $params, $request);
  }

  protected function respond(string $filename, array $params, Request $request): Response
  {
    $format = strtolower((string) $request->query('fm', 'jpg'));
    abort_unless(in_array($format, ['jpg', 'jpeg', 'webp', 'avif'], true), 404);
    // A modern format this server can't write falls back to JPEG
    $format = in_array($format, ImageSupport::modernFormats(), true) ? $format : 'jpg';

    $params['fm'] = $format;
    $params['q'] = self::FORMAT_QUALITY[$format];

    $cachedPath = $this->server->makeImage('uploads/' . $filename, $params);

    return response($this->server->getCache()->read($cachedPath), 200, [
      'Content-Type' => 'image/' . ($format === 'jpg' ? 'jpeg' : $format),
    ] + $this->cacheHeaders());
  }

  /**
   * Absolute path of an upload; 404 for anything else.
   */
  protected function source(string $filename): string
  {
    $path = storage_path('app/public/uploads/' . $filename);
    abort_unless($filename === basename($filename) && is_file($path), 404);

    return $path;
  }

  protected function dimension(?string $value): int
  {
    if ($value === null) {
      return self::MAX_SIZE;
    }
    abort_unless(ctype_digit($value) && (int) $value > 0 && (int) $value <= self::MAX_SIZE, 404);

    return (int) $value;
  }

  /**
   * w,h,x,y as ints, or null when there is no usable crop. Missing or
   * non-numeric x/y (the admin sends "null") count as 0.
   */
  protected function coords(?string $coords): ?array
  {
    $parts = explode(',', (string) $coords);
    if (count($parts) !== 4) {
      return null;
    }

    $parts = array_map(fn ($v) => is_numeric($v) ? max(0, (int) $v) : 0, $parts);

    return $parts[0] > 0 && $parts[1] > 0 ? $parts : null;
  }

  /**
   * Width and height as displayed, i.e. after EXIF auto-orientation.
   */
  protected function orientedSize(string $path): array
  {
    [$width, $height] = getimagesize($path);
    $orientation = function_exists('exif_read_data') ? (@exif_read_data($path)['Orientation'] ?? 1) : 1;

    return $orientation >= 5 ? [$height, $width] : [$width, $height];
  }

  protected function cacheHeaders(): array
  {
    return ['Cache-Control' => 'max-age=31536000, public'];
  }
}
