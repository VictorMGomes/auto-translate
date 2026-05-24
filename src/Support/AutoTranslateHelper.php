<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate\Support;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Victormgomes\AutoTranslate\AutoTranslate;

final class AutoTranslateHelper
{
    public static function getTranslatableAttributes(Model $model): array
    {
        $reflection = new ReflectionClass($model);
        $attributes = $reflection->getAttributes(AutoTranslate::class);

        if (! empty($attributes)) {
            /** @var AutoTranslate $attr */
            $attr = $attributes[0]->newInstance();
            if ($attr->fields) {
                return $attr->fields;
            }
        }

        return [];
    }

    public static function shouldTranslateAttribute(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $value = trim($value);

        return ! empty($value) && strlen($value) > 1;
    }
}
