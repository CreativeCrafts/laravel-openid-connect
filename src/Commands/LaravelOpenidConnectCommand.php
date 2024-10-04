<?php

namespace CreativeCrafts\LaravelOpenidConnect\Commands;

use Illuminate\Console\Command;

class LaravelOpenidConnectCommand extends Command
{
    public $signature = 'laravel-openid-connect';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
