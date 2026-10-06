<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

/**
 * Fills the Glide cache with every image variant the public pages use.
 *
 * Crawls the site through the HTTP kernel (no web server needed), starting
 * at the home page of each language and following links under /de, /fr and
 * /en, collects the /img/... URLs from src, srcset and content attributes
 * and requests each one once. Variants already in the cache are just read,
 * so running it again only renders what is missing.
 */
class WarmImages extends Command
{
  protected $signature = 'images:warm';

  protected $description = 'Render every image variant the public pages use into the Glide cache';

  public const START = ['/de/home', '/fr/home', '/en/home'];

  public function handle(Kernel $kernel): int
  {
    // Internal requests must not leave rows in the sessions table
    config(['session.driver' => 'array']);

    [$pages, $images, $failedPages] = $this->crawl($kernel);
    $this->info(count($pages) . ' pages, ' . count($images) . ' image URLs');

    $failed = $failedPages;
    $bar = $this->output->createProgressBar(count($images));
    foreach ($images as $url) {
      $status = $this->get($kernel, $url)->getStatusCode();
      if ($status !== 200) {
        $failed[] = "{$status} {$url}";
      }
      $bar->advance();
    }
    $bar->finish();
    $this->newLine();

    if ($failed) {
      $this->warn(count($failed) . ' failed:');
      foreach ($failed as $line) {
        $this->line("  {$line}");
      }

      return self::FAILURE;
    }

    $this->info('Done.');

    return self::SUCCESS;
  }

  /**
   * @return array{0: string[], 1: string[], 2: string[]} pages, image URLs, failed pages
   */
  protected function crawl(Kernel $kernel): array
  {
    $queue = self::START;
    $seen = array_fill_keys($queue, true);
    $pages = $images = $failed = [];

    while ($path = array_shift($queue)) {
      $response = $this->get($kernel, $path);
      if ($response->getStatusCode() !== 200) {
        $failed[] = "{$response->getStatusCode()} {$path}";
        continue;
      }
      $pages[] = $path;
      $html = (string) $response->getContent();

      foreach ($this->images($html) as $url) {
        $images[$url] = true;
      }

      foreach ($this->links($html) as $link) {
        if (!isset($seen[$link])) {
          $seen[$link] = true;
          $queue[] = $link;
        }
      }
    }

    return [$pages, array_keys($images), $failed];
  }

  protected function get(Kernel $kernel, string $path)
  {
    $request = Request::create($path);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    return $response;
  }

  /**
   * /img/... paths in src, srcset and content attributes. Crop coords contain
   * commas, so srcset candidates are split on whitespace only.
   */
  protected function images(string $html): array
  {
    preg_match_all('/\s(?:src|srcset|content)="([^"]*)"/', $html, $matches);

    $urls = [];
    foreach ($matches[1] as $value) {
      foreach (preg_split('/\s+/', html_entity_decode($value)) as $token) {
        $path = $this->localPath(rtrim($token, ','), keepQuery: true);
        if ($path && str_starts_with($path, '/img/')) {
          $urls[] = $path;
        }
      }
    }

    return $urls;
  }

  /**
   * Public pages linked from the HTML: same host, under /de, /fr or /en.
   */
  protected function links(string $html): array
  {
    preg_match_all('/\shref="([^"]*)"/', $html, $matches);

    return collect($matches[1])
      ->map(fn ($href) => $this->localPath(html_entity_decode($href)))
      ->filter(fn ($path) => $path && preg_match('#^/(de|fr|en)/#', $path))
      ->unique()
      ->values()
      ->all();
  }

  /**
   * Path (and query) of a URL on this site; null for other hosts and for
   * javascript:, mailto: and the like.
   */
  protected function localPath(string $url, bool $keepQuery = false): ?string
  {
    $parts = parse_url($url);
    if ($parts === false || isset($parts['scheme']) && !in_array($parts['scheme'], ['http', 'https'], true)) {
      return null;
    }
    if (isset($parts['host']) && $parts['host'] !== Request::create('/')->getHost()) {
      return null;
    }
    if (!isset($parts['path']) || !str_starts_with($parts['path'], '/')) {
      return null;
    }

    return $parts['path'] . ($keepQuery && isset($parts['query']) ? '?' . $parts['query'] : '');
  }
}
