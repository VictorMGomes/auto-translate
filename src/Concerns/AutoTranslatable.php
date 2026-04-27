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

    /**
     * Intercept attribute access to provide translations from sidecar table.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        $fields = AutoTranslateHelper::getTranslatableAttributes($this);

        if (! in_array($key, $fields)) {
            return $value;
        }

        $locale = app()->getLocale();

        // If it's the default locale, return the original DB value
        if ($locale === config('app.fallback_locale', 'pt_BR')) {
            return $value;
        }

        return $this->getAutoTranslation($key, $locale) ?: $value;
    }

    public function getAutoTranslation(string $field, string $locale): ?string
    {
        return Cache::tags(['auto_translate', get_class($this), (string) $this->getKey()])
            ->remember("trans_{$field}_{$locale}", 3600, function () use ($field, $locale) {
                return AutoTranslation::where('translatable_type', get_class($this))
                    ->where('translatable_id', (string) $this->getKey())
                    ->where('field', $field)
                    ->where('locale', $locale)
                    ->value('content');
            });
    }

    protected function triggerAutoTranslation(): void
    {
        $fields = AutoTranslateHelper::getTranslatableAttributes($this);

        if (empty($fields)) {
            return;
        }

        if ($this->shouldAutoTranslate($fields)) {
            $this->dispatchTranslateJob();
        }
    }

    protected function shouldAutoTranslate(array $fields): bool
    {
        if ($this->wasRecentlyCreated) {
            return true;
        }

        $changes = array_keys($this->getChanges());

        return count(array_intersect($changes, $fields)) > 0;
    }

    protected function dispatchTranslateJob(): void
    {
        TranslateModelJob::dispatch(
            get_class($this),
            $this->getKey(),
            config('app.fallback_locale', 'pt_BR')
        )->afterCommit();
    }
}
