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
use Laravel\Ai\Facades\Ai;
use Throwable;
use Victormgomes\AutoTranslate\Models\AutoTranslation;
use Victormgomes\AutoTranslate\Support\AutoTranslateHelper;

final class TranslateModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly string $modelClass,
        public readonly int|string $modelId,
        public readonly string $fromLocale,
        public readonly ?array $onlyFields = null,
        public readonly ?array $targetLocales = null
    ) {}

    public function handle(): void
    {
        if (! is_subclass_of($this->modelClass, Model::class)) {
            return;
        }

        /** @var Model|null $model */
        $model = $this->modelClass::query()->find($this->modelId);
        if (! $model) {
            return;
        }

        $targets = $this->resolveTargetLocales();
        $fields = AutoTranslateHelper::getTranslatableAttributes($model);
        /** @var string $fallbackLocale */
        $fallbackLocale = config('app.fallback_locale', 'pt_BR');

        if (empty($targets) || empty($fields)) {
            return;
        }

        foreach ($targets as $localeCode) {
            if ($localeCode === $this->fromLocale || $localeCode === $fallbackLocale) {
                continue;
            }

            foreach ($fields as $field) {
                $this->translateAttribute($model, (string) $field, (string) $localeCode);
            }
        }

        /** @var int|string $modelKey */
        $modelKey = $model->getKey();
        Cache::tags(['auto_translate', get_class($model), (string) $modelKey])->flush();
    }

    private function translateAttribute(
        Model $model,
        string $field,
        string $targetLocale
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
            /** @var string $connection */
            $connection = config('auto-translate.ai_connection');
            /** @var string $modelName */
            $modelName = config('auto-translate.ai_model');

            Log::info("[AutoTranslate] Translating '$field' to '$targetLocale' via AI ($connection/$modelName)");

            $prompt = "Translate the following text from {$this->fromLocale} to {$targetLocale}.
                      Return ONLY the translated text, preserving any HTML tags or placeholders like :name or :attribute.
                      Text: \"{$sourceText}\"";

            $translatedText = Ai::withConnection($connection)
                ->withModel($modelName)
                ->prompt($prompt)
                ->execute()
                ->text();

            $translatedText = trim($translatedText, " \"\n\r\t\v\0");

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

    /**
     * @return array<int, string>
     */
    private function resolveTargetLocales(): array
    {
        /** @var array<int, string> $availableLocales */
        $availableLocales = (array) config('app.available_locales', []);
        $codes = $this->targetLocales ?? $availableLocales;
        /** @var array<string, string> $map */
        $map = (array) config('localization.auto_translate.map', []);
        $results = [];

        foreach ($codes as $code) {
            if ($code === $this->fromLocale) {
                continue;
            }

            if (isset($map[$code])) {
                $results[] = (string) $code;
            }
        }

        return $results;
    }
}
