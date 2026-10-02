<?php

namespace Tests\Support;

use Attribute;

/**
 * Links a test to ids of .rewrite/qa-checklist.json, e.g. #[Qa('pg-404')].
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Qa
{
    public array $ids;

    public function __construct(string ...$ids)
    {
        $this->ids = $ids;
    }
}
