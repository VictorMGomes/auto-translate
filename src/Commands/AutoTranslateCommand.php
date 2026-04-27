<?php

namespace Victormgomes\AutoTranslate\Commands;

use Illuminate\Console\Command;

class AutoTranslateCommand extends Command
{
    public $signature = 'auto-translate';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
