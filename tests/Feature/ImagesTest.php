<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Qa;
use Tests\TestCase;

class ImagesTest extends TestCase
{
    protected function dimensions(string $body): array
    {
        $size = getimagesizefromstring($body);

        return [$size[0], $size[1]];
    }

    protected function cacheFiles(): array
    {
        return collect(File::allFiles(storage_path('app/.glide-cache')))
            ->mapWithKeys(fn ($file) => [$file->getPathname() => $file->getMTime()])
            ->all();
    }

    #[Qa('setup-glide')]
    public function test_glide_cache_directory_is_writable(): void
    {
        $path = base_path('storage/app/.glide-cache');

        $this->assertDirectoryExists($path);
        $this->assertDirectoryIsWritable($path);
    }

    public static function formats(): array
    {
        return [
            'jpg (no fm)' => ['', 'image/jpeg'],
            'webp' => ['?fm=webp', 'image/webp'],
            'avif' => ['?fm=avif', 'image/avif'],
        ];
    }

    #[Qa('img-formats')]
    #[DataProvider('formats')]
    public function test_crop_is_served_in_the_requested_format(string $query, string $type): void
    {
        $this->upload('qa-format.jpg');

        $response = $this->get("/img/crop/qa-format.jpg/900/600{$query}")->assertOk()->assertHeader('Content-Type', $type);
        $this->assertSame($type, getimagesizefromstring($response->getContent())['mime']);
    }

    public static function slots(): array
    {
        // [source w, h], url tail, expected [w, h]
        return [
            'landscape 900 slot' => [[1200, 800], '900/600', [900, 600]],
            'landscape 1200 slot' => [[1200, 800], '1200/800', [1200, 800]],
            'landscape, slot larger than source' => [[1200, 800], '2400/1500', [1200, 800]],
            'portrait tall slot (member page)' => [[1000, 2000], '1600/1920', [960, 1920]],
            'crop scaled to slot' => [[2000, 1600], '1200/800/1500,1000,100,100', [1200, 800]],
            'crop anchored top left' => [[2000, 1600], '900/600/1200,800,0,0', [900, 600]],
            'crop with x/y sent as "null"' => [[2000, 1600], '900/600/1200,800,null,null', [900, 600]],
        ];
    }

    #[Qa('img-sizes')]
    #[DataProvider('slots')]
    public function test_crop_matches_the_requested_slot(array $source, string $tail, array $expected): void
    {
        $this->upload('qa-slot.jpg', ...$source);

        $response = $this->get("/img/crop/qa-slot.jpg/{$tail}")->assertOk();
        $this->assertSame($expected, $this->dimensions($response->getContent()));
    }

    #[Qa('img-sizes', 'pp-member-crops')]
    public function test_crop_cuts_the_saved_region(): void
    {
        // Quadrants: red top left, green top right, blue bottom left, yellow bottom right
        $this->upload('qa-region.png', 1000, 1000);

        $topLeft = $this->get('/img/crop/qa-region.png/1200/1200/500,500,0,0')->getContent();
        $bottomRight = $this->get('/img/crop/qa-region.png/1200/1200/500,500,500,500')->getContent();

        $this->assertSame([255, 0, 0], $this->pixel($topLeft, 200, 200));
        $this->assertSame([255, 255, 0], $this->pixel($bottomRight, 200, 200));
    }

    protected function pixel(string $body, int $x, int $y): array
    {
        $image = new \Imagick();
        $image->readImageBlob($body);
        $colour = $image->getImagePixelColor($x, $y)->getColor();

        // JPEG noise: round to the nearest of 0/255
        return array_map(fn ($v) => $v > 127 ? 255 : 0, [$colour['r'], $colour['g'], $colour['b']]);
    }

    #[Qa('img-original-thumb')]
    public function test_original_and_thumbnail(): void
    {
        $this->upload('qa-orig.jpg', 1200, 800);

        $original = $this->get('/img/original/qa-orig.jpg')->assertOk();
        $this->assertSame(file_get_contents(storage_path('app/public/uploads/qa-orig.jpg')), $original->streamedContent());

        $thumbnail = $this->get('/img/thumbnail/qa-orig.jpg')->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame([300, 300], $this->dimensions($thumbnail->getContent()));
    }

    #[Qa('img-original-thumb', 'img-guard')]
    public function test_unknown_or_outside_files_are_404(): void
    {
        foreach (['/img/original/qa-missing.jpg', '/img/thumbnail/qa-missing.jpg', '/img/crop/qa-missing.jpg/900/600',
            '/img/original/..%2F..%2F.env', '/img/original/files'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    #[Qa('img-guard')]
    public function test_out_of_range_sizes_are_rejected(): void
    {
        $this->upload('qa-guard.jpg');

        foreach (['2401/600', '900/2401', '0/600', 'abc/600', '900/-1'] as $size) {
            $this->get("/img/crop/qa-guard.jpg/{$size}")->assertNotFound();
        }
    }

    #[Qa('img-guard')]
    public function test_non_whitelisted_sizes_are_rejected(): void
    {
        $this->upload('qa-guard.jpg');

        foreach (['901/601', '900/601', '2400/2400', '900', '1/1'] as $size) {
            $this->get("/img/crop/qa-guard.jpg/{$size}")->assertNotFound();
        }
        $this->get('/img/crop/qa-guard.jpg/900/600')->assertOk();
    }

    #[Qa('img-guard')]
    public function test_non_whitelisted_formats_are_rejected(): void
    {
        $this->upload('qa-guard.jpg');

        foreach (['tiff', 'gif', 'png', 'x'] as $format) {
            $this->get("/img/crop/qa-guard.jpg/900/600?fm={$format}")->assertNotFound();
        }
        $this->get('/img/crop/qa-guard.jpg/900/600?fm=jpg')->assertOk()->assertHeader('Content-Type', 'image/jpeg');
    }

    #[Qa('img-cache')]
    public function test_second_request_is_served_from_the_glide_cache(): void
    {
        $this->upload('qa-cache.jpg');

        $first = $this->get('/img/crop/qa-cache.jpg/900/600?fm=webp')->assertOk()->getContent();
        $cached = $this->cacheFiles();
        $this->assertCount(1, $cached);

        sleep(1);
        $second = $this->get('/img/crop/qa-cache.jpg/900/600?fm=webp')->assertOk()
            ->assertHeader('Cache-Control', 'max-age=31536000, public')
            ->getContent();

        $this->assertSame($cached, $this->cacheFiles(), 'The variant was rendered again');
        $this->assertSame($first, $second);
    }
}
