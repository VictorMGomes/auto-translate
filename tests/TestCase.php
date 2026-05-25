<?php

namespace Victormgomes\AutoTranslate\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as Orchestra;
use Victormgomes\AutoTranslate\AutoTranslateServiceProvider;
use Victormgomes\AutoTranslate\Concerns\AutoTranslatable;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('content')->nullable();
            $table->timestamps();
        });

        $migration = include __DIR__.'/../database/migrations/create_auto_translations_table.php.stub';
        $migration->up();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Victormgomes\\AutoTranslate\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            \Laravel\Ai\AiServiceProvider::class,
            AutoTranslateServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}

use Victormgomes\AutoTranslate\AutoTranslate;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 */
#[AutoTranslate(['title', 'content'])]
class TestModel extends Model
{
    use AutoTranslatable;

    protected $guarded = [];
}
