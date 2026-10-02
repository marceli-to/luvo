<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use Tests\Support\BuildsRecords;

/**
 * Feature tests run against the MySQL database `luvo_test`, loaded once per
 * run from tests/fixtures/schema.sql (structure of the local `luvo` DB), and
 * against a temporary storage directory, so neither `luvo` nor storage/ is
 * written.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions, BuildsRecords;

    public const DATABASE = 'luvo_test';

    protected static bool $schemaLoaded = false;

    protected string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = sys_get_temp_dir() . '/luvo-tests/' . uniqid();
        foreach (['app/public/uploads/files', 'app/.glide-cache', 'framework/views', 'logs'] as $dir) {
            File::makeDirectory("{$this->storage}/{$dir}", 0775, true, true);
        }
        $this->app->useStoragePath($this->storage);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storage);

        parent::tearDown();
    }

    /**
     * Guard and schema load run before the transaction of DatabaseTransactions.
     */
    protected function setUpTraits()
    {
        $database = config('database.connections.mysql.database');
        if (config('database.default') !== 'mysql' || $database !== self::DATABASE) {
            throw new RuntimeException("Feature tests only run against `" . self::DATABASE . "`, not `{$database}`.");
        }

        if (!static::$schemaLoaded) {
            $this->loadSchema();
            static::$schemaLoaded = true;
        }

        return parent::setUpTraits();
    }

    protected function loadSchema(): void
    {
        $config = config('database.connections.mysql');
        $pdo = new PDO("mysql:host={$config['host']};port={$config['port']}", $config['username'], $config['password']);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . self::DATABASE . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        DB::purge('mysql');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (DB::select('SHOW TABLES') as $row) {
            DB::statement('DROP TABLE `' . array_values((array) $row)[0] . '`');
        }
        DB::unprepared(file_get_contents(base_path('tests/fixtures/schema.sql')));
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * A JPEG/PNG upload of the given size in the temporary storage: four
     * coloured quadrants (red, green / blue, yellow), so crops can be told apart.
     */
    protected function upload(string $name = 'qa-image.jpg', int $width = 1200, int $height = 800): string
    {
        $image = new \Imagick();
        $image->newImage($width, $height, 'yellow');
        $draw = new \ImagickDraw();
        foreach ([['red', 0, 0], ['green', 1, 0], ['blue', 0, 1]] as [$colour, $col, $row]) {
            $draw->setFillColor($colour);
            $draw->rectangle($col * $width / 2, $row * $height / 2, ($col + 1) * $width / 2 - 1, ($row + 1) * $height / 2 - 1);
        }
        $image->drawImage($draw);
        $image->setImageFormat(pathinfo($name, PATHINFO_EXTENSION) === 'png' ? 'png' : 'jpeg');
        $image->writeImage(storage_path('app/public/uploads/' . $name));

        return $name;
    }

    /**
     * Run an assertion that documents a known finding (.rewrite/08-test-plan.md).
     * While it fails, the test is skipped as "Known finding F<n>" (reported as
     * fail in tests/qa-results.json); once it passes, the test fails so the
     * marker gets removed.
     */
    protected function knownFinding(string $finding, string $summary, callable $assertion): void
    {
        try {
            $assertion();
        }
        catch (\PHPUnit\Framework\AssertionFailedError $e) {
            $this->markTestSkipped("Known finding {$finding}: {$summary}");
        }

        $this->fail("Known finding {$finding} passes now. Remove the knownFinding() marker and update the plan.");
    }
}
