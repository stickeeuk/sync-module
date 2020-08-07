<?php

namespace Stickee\Sync\Console\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Stickee\Sync\Client;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:sync
        {--table= : The name of a table from sync.tables}
        {--directory= : The name of a directory from sync.directories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise tables / files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $originalConfig = config('sync');
        $client = app(Client::class);

        $table = $this->option('table');
        $directory = $this->option('directory');

        if ($table !== null) {
            if (!isset($originalConfig['tables'][$table])) {
                throw new InvalidArgumentException('Invalid table "' . $table . '"');
            }

            config(['sync.tables' => [$table => $originalConfig['tables'][$table]]]);

            if ($directory === null) {
                config(['sync.directories' => []]);
            }
        }

        if ($directory !== null) {
            if (!isset($originalConfig['directories'][$directory])) {
                throw new InvalidArgumentException('Invalid directory "' . $directory . '"');
            }

            config(['sync.directories' => [$directory => $originalConfig['directories'][$directory]]]);

            if ($table === null) {
                config(['sync.tables' => []]);
            }
        }

        $client->sync();
    }
}
