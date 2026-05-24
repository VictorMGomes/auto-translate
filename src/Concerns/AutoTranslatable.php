<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate\Concerns;

use Illuminate\Support\Facades\Cache;
use Victormgomes\AutoTranslate\Jobs\TranslateModelJob;
use Victormgomes\AutoTranslate\Models\AutoTranslation;
use Victormgomes\AutoTranslate\Support\AutoTranslateHelper;

trait AutoTranslatable
{
    public static function bootAutoTranslatable(): void
    {
        static::saved(fn ($model) => $model->triggerAutoTranslation());
    }

    
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        $fields = AutoTranslateHelper::getTranslatableAttributes($this);

        if (! in_array($key, $fields)) {
            return $value;
        }

        $locale = app()->getLocale();

        