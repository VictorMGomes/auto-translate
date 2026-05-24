<?php

namespace Victormgomes\AutoTranslate\Facades;

use Illuminate\Support\Facades\Facade;

class AutoTranslate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Victormgomes\AutoTranslate\AutoTranslate::class;
    }
}
