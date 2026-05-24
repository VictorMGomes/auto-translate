<?php

// config for Victormgomes/AutoTranslate
return [
    /**
     * The AI connection to use for translations.
     * This should match one of the connections defined in your laravel-ai config.
     * Supported: "openai", "anthropic", "google", "ollama", etc.
     */
    /** @phpstan-ignore-next-line */
    'ai_connection' => env('AUTO_TRANSLATE_AI_CONNECTION', 'openai'),

    /**
     * The model to use for translations.
     * Examples: "gpt-4o", "claude-3-5-sonnet", "gemini-1.5-pro", etc.
     */
    /** @phpstan-ignore-next-line */
    'ai_model' => env('AUTO_TRANSLATE_AI_MODEL', 'gpt-4o'),
];
