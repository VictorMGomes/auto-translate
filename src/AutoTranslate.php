<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
final class AutoTranslate
{
    /**
     * @param  array<int, string>|null  $fields
     */
    public function __construct(
        public ?array $fields = null
    ) {}
}
