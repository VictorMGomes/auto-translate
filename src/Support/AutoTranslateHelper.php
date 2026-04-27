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
        // 1. Check for PHP 8 Attribute on Class
        $reflection = new ReflectionClass($model);
        $attributes = $reflection->getAttributes(AutoTranslate::class);

        if (! empty($attributes)) {
            $attr = $attributes[0]->newInstance();
            if ($attr->fields) {
                return $attr->fields;
            }
        }

        // 2. Check for property
        if (property_exists($model, 'translatable') && is_array($model->translatable)) {
            return array_filter($model->translatable, fn ($f) => is_string($f) && $f !== '');
        }

        return [];
    }

    public static function shouldTranslateAttribute(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
