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

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Victormgomes\\AutoTranslate\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            AutoTranslateServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}

class TestModel extends Model
{
    use AutoTranslatable;
}
