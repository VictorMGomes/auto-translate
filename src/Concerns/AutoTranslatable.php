<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Victormgomes\AutoTranslate\Jobs\TranslateModelJob;
use Victormgomes\AutoTranslate\Models\AutoTranslation;
use Victormgomes\AutoTranslate\Support\AutoTranslateHelper;

/**
 * @mixin Model
 */
trait AutoTranslatable
{
    public static function bootAutoTranslatable(): void
    {
        static::saved(fn ($model) => $model->triggerAutoTranslation());
    }

    /**
     * @param  string  $key
     * @return mixed
     */
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
            /** @var Builder<AutoTranslation> $query */
            $query = AutoTranslation::query();
            $translation = $query->where('translatable_type', get_class($this))
                ->where('translatable_id', $this->getKey())
                ->where('field', $key)
                ->where('locale', $locale)
                ->first();

            return $translation ? $translation->content : $value;
        });
    }

    public function triggerAutoTranslation(): void
    {
        /** @var string $modelClass */
        $modelClass = get_class($this);
        /** @var int|string $modelId */
        $modelId = $this->getKey();
        /** @var string $locale */
        $locale = app()->getLocale();

        TranslateModelJob::dispatch(
            $modelClass,
            $modelId,
            $locale
        );
    }
}
