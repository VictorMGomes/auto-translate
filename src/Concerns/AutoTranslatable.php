<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Victormgomes\AutoTranslate\Jobs\TranslateModelJob;
use Victormgomes\AutoTranslate\Models\AutoTranslation;
use Victormgomes\AutoTranslate\Support\AutoTranslateHelper;

/**
 * @mixin Model
 *
 * @phpstan-ignore-next-line
 */
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
        $fallbackLocale = config('app.fallback_locale', 'en');

        if ($locale === $fallbackLocale) {
            return $value;
        }

        $cacheKey = 'auto_translate:'.get_class($this).':'.$this->getKey().':'.$key.':'.$locale;

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($key, $locale, $value) {
            $translation = AutoTranslation::where('translatable_type', get_class($this))
                ->where('translatable_id', $this->getKey())
                ->where('field', $key)
                ->where('locale', $locale)
                ->first();

            return $translation ? $translation->content : $value;
        });
    }

    public function triggerAutoTranslation(): void
    {
        TranslateModelJob::dispatch(
            get_class($this),
            $this->getKey(),
            app()->getLocale()
        );
    }
}
