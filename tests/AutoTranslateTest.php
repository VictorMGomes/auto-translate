<?php

use Illuminate\Support\Facades\App;
use Laravel\Ai\AnonymousAgent;
use Victormgomes\AutoTranslate\Models\AutoTranslation;
use Victormgomes\AutoTranslate\Tests\TestModel;

it('can auto translate model attributes', function () {
    // 1. Fake the AI agent so we don't need real credentials.
    AnonymousAgent::fake([
        'Translated Title',
        'Translated Content',
    ]);

    // 2. Set current locale to source
    App::setLocale('en');

    // 3. Configure target locales and connection
    config()->set('app.available_locales', ['en', 'es']);
    config()->set('localization.auto_translate.map', ['es' => 'es']);
    config()->set('auto-translate.ai_connection', 'openai');
    config()->set('auto-translate.ai_model', 'gpt-4o');

    // 4. Create the model. This should dispatch the translation job automatically via the model event.
    $model = TestModel::create([
        'title' => 'Hello World',
        'content' => 'This is a test content.',
    ]);

    // 5. Change locale to target
    App::setLocale('es');

    // 6. Check if translated value is returned
    expect($model->title)->toBe('Translated Title')
        ->and($model->content)->toBe('Translated Content');

    // 7. Verify the translations were saved in the DB
    $translations = AutoTranslation::where('translatable_type', TestModel::class)
        ->where('translatable_id', $model->id)
        ->get();

    expect($translations)->toHaveCount(2);
});
