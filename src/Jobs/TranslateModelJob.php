<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LaravelLang\Translator\Services\Translate;
use Throwable;
use Victormgomes\AutoTranslate\Support\AutoTranslateHelper;
use Victormgomes\AutoTranslate\Support\AutoTranslation;

final class TranslateModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public function __construct(
        public readonly string $modelClass,
        public readonly int|string $modelId,
        public readonly string $fromLocale,
        public readonly ?array $onlyFields = null,
        public readonly ?array $targetLocales = null
    ) {}

    public function handle(Translate $translator): void
    {
        if (! is_subclass_of($this->modelClass, Model::class)) {
            return;
        }

        $model = $this->modelClass::query()->find($this->modelId);
        if (! $model) {
            return;
        }

        $driverMethod = $this->resolveDriverMethod();
        $targets = $this->resolveTargetLocales();
        $fields = AutoTranslateHelper::getTranslatableAttributes($model);

        $fallbackLocale = config('app.fallback_locale', 'pt_BR');

        if (! $driverMethod || empty($targets) || empty($fields)) {
            return;
        }

        foreach ($targets as $localeCode => $localeEnum) {
            if ($localeCode === $this->fromLocale || $localeCode === $fallbackLocale) {
                continue;
            }

            foreach ($fields as $field) {
                $this->translateAttribute($translator, $driverMethod, $model, (string) $field, (string) $localeCode, $localeEnum);
            }
        }

        Cache::tags(['auto_translate', get_class($model), (string) $model->getKey()])->flush();
    }

    private function translateAttribute(
        Translate $translator,
        string $method,
        Model $model,
        string $field,
        string $targetLocale,
        mixed $targetEnum
    ): void {
        $sourceText = $model->getRawOriginal($field) ?: $model->getAttribute($field);

        if (! AutoTranslateHelper::shouldTranslateAttribute($sourceText)) {
            return;
        }

        $exists = AutoTranslation::where('translatable_type', get_class($model))
            ->where('translatable_id', (string) $model->getKey())
            ->where('field', $field)
            ->where('locale', $targetLocale)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            Log::info("[AutoTranslate] Translating '$field' to '$targetLocale' via $method");

            $translatedText = $translator->$method($sourceText, $targetEnum);

            if (AutoTranslateHelper::shouldTranslateAttribute($translatedText)) {
                AutoTranslation::updateOrCreate([
                    'translatable_type' => get_class($model),
                    'translatable_id' => (string) $model->getKey(),
                    'field' => $field,
                    'locale' => $targetLocale,
                ], [
                    'content' => $translatedText,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning("[AutoTranslate] Error translating $field: ".$e->getMessage());
        }
    }

    private function resolveDriverMethod(): ?string
    {
        $channels = config('localization.translators.channels', []);
        $enabled = array_filter($channels, fn ($c) => ($c['enabled'] ?? false) === true);
        if (empty($enabled)) {
            return null;
        }
        uasort($enabled, fn ($a, $b) => ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99));

        return 'via'.ucfirst(array_key_first($enabled));
    }

    private function resolveTargetLocales(): array
    {
        $codes = $this->targetLocales ?? config('app.available_locales', []);
        $map = config('localization.auto_translate.map', []);
        $results = [];

        foreach ($codes as $code) {
            if ($code === $this->fromLocale) {
                continue;
            }
            if (isset($map[$code])) {
                $results[$code] = $map[$code];
            }
        }

        return $results;
    }
}
