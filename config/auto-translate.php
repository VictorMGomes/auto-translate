<?php

// config for Victormgomes/AutoTranslate
return [
    /**
     * The AI connection to use for translations.
     * This should match one of the connections defined in your laravel-ai config.
     * Supported: "openai", "anthropic", "google", "ollama", etc.
     */
    'ai_connection' => (string) env('AUTO_TRANSLATE_AI_CONNECTION', 'openai'),

    /**
     * The model to use for translations.
     * Examples: "gpt-4o", "claude-3-5-sonnet", "gemini-1.5-pro", etc.
     */
    'ai_model' => (string) env('AUTO_TRANSLATE_AI_MODEL', 'gpt-4o'),
];
