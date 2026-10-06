<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WarmImagesTest extends TestCase
{
    protected function cacheFiles(): array
    {
        return collect(File::allFiles(storage_path('app/.glide-cache')))
            ->map(fn ($file) => $file->getRelativePathname())
            ->all();
    }

    public function test_warms_the_images_of_the_crawled_pages(): void
    {
        $teams = $this->teams();
        $contact = $this->contact();
        $this->contactImage($contact, $this->upload('qa-contact-desktop.jpg'));
        $this->contactImage($contact, $this->upload('qa-contact-mobile.jpg'), ['device' => 'mobile']);
        $this->homeImage($this->home(), $this->upload('qa-hero.jpg'));
        $member = $this->member($teams['luks'], ['firstname' => 'QA-Anna', 'name' => 'Muster']);
        $this->memberImage($member, $this->upload('qa-member.jpg'));
        $this->assistant($teams['luks']);
        $this->assistant($teams['vogt']);

        $this->assertSame(0, Artisan::call('images:warm'), Artisan::output());

        $files = $this->cacheFiles();
        foreach (['qa-hero.jpg', 'qa-member.jpg', 'qa-contact-desktop.jpg'] as $name) {
            $this->assertNotEmpty(preg_grep("#{$name}/#", $files), "No cached variant of {$name}");
        }
        $this->assertSame(0, DB::table('sessions')->count(), 'The crawl stored sessions');
    }

    public function test_fails_when_an_image_is_missing(): void
    {
        $this->homeImage($this->home(), 'qa-missing.jpg');
        $this->teams();

        $this->assertSame(1, Artisan::call('images:warm'));
        $this->assertStringContainsString('404 /img/crop/qa-missing.jpg', Artisan::output());
    }
}
