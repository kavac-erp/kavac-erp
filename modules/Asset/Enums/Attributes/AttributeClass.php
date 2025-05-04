<?php

declare(strict_types=1);

namespace Modules\Asset\Enums\Attributes;

use Attribute;

#[\Attribute]
abstract class AttributeClass
{
    public function __construct(
        public readonly string|int|bool $value,
    ) {
    }
}
