<?php

namespace Tests\Support\QaResults;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\Qa;

/**
 * Writes each test's outcome with its #[Qa] checklist ids to a JSON file,
 * which scripts/qa-results.mjs merges into tests/qa-results.json.
 */
final class Extension implements PHPUnitExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $output = $parameters->has('output') ? $parameters->get('output') : 'tests/.results/phpunit.json';
        $results = new class($output) {
            public array $tests = [];

            public function __construct(public string $output) {}

            public function record($test, string $status, ?string $message = null): void
            {
                if (!$test instanceof TestMethod) {
                    return;
                }
                $ids = [];
                foreach ([new ReflectionClass($test->className()), new ReflectionMethod($test->className(), $test->methodName())] as $reflection) {
                    foreach ($reflection->getAttributes(Qa::class) as $attribute) {
                        $ids = [...$ids, ...$attribute->newInstance()->ids];
                    }
                }
                $title = class_basename($test->className()) . '::' . $test->methodName();
                if ($test->testData()->hasDataFromDataProvider()) {
                    $title .= ' ' . $test->testData()->dataFromDataProvider()->dataSetName();
                }
                $this->tests[] = [
                    'title' => $title,
                    'ids' => array_values(array_unique($ids)),
                    'status' => $status,
                    'error' => $message === null ? null : strtok(trim($message), "\n"),
                ];
            }

            public function write(): void
            {
                @mkdir(dirname($this->output), 0775, true);
                file_put_contents($this->output, json_encode([
                    'suite' => 'phpunit',
                    'run' => date(DATE_ATOM),
                    'tests' => $this->tests,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
        };

        $facade->registerSubscribers(
            new class($results) implements PassedSubscriber {
                public function __construct(private $results) {}
                public function notify(Passed $event): void { $this->results->record($event->test(), 'passed'); }
            },
            new class($results) implements FailedSubscriber {
                public function __construct(private $results) {}
                public function notify(Failed $event): void { $this->results->record($event->test(), 'failed', $event->throwable()->message()); }
            },
            new class($results) implements ErroredSubscriber {
                public function __construct(private $results) {}
                public function notify(Errored $event): void { $this->results->record($event->test(), 'failed', $event->throwable()->message()); }
            },
            new class($results) implements SkippedSubscriber {
                public function __construct(private $results) {}
                public function notify(Skipped $event): void { $this->results->record($event->test(), 'skipped', $event->message()); }
            },
            new class($results) implements ExecutionFinishedSubscriber {
                public function __construct(private $results) {}
                public function notify(ExecutionFinished $event): void { $this->results->write(); }
            },
        );
    }
}
