<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\Support\Qa;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    #[Qa('rb-server-error')]
    public function test_server_error_shows_the_styled_page_without_details(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/qa-server-error', fn () => throw new RuntimeException('QA-secret-detail'));

        $this->get('/qa-server-error')
            ->assertStatus(500)
            ->assertSee('Fehler 500')
            ->assertDontSee('QA-secret-detail')
            ->assertDontSee('RuntimeException')
            ->assertDontSee(base_path());
    }
}
