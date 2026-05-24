<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Victormgomes\AutoTranslate\Commands\AutoTranslateCommand;
use Victormgomes\AutoTranslate\Support\AutoTranslateHelper;

class AutoTranslateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('auto-translate')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_auto_translations_table')
            ->hasCommand(AutoTranslateCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AutoTranslateHelper::class, function (): AutoTranslateHelper {
            return new AutoTranslateHelper;
        });
    }
}
