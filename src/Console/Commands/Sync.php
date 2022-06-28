<?php

namespace Stickee\Sync\Console\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Stickee\Sync\Client;
use Stickee\Sync\Helpers;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:sync
        {--table= : The name of a table from sync-client.tables}
        {--directory= : The name of a directory from sync-client.directories}';

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
        $originalConfig = Helpers::clientConfig();
        $client = app(Client::class);

        $table = $this->option('table');
        $directory = $this->option('directory');

        if ($table !== null) {
            if (!isset($originalConfig['tables'][$table])) {
                throw new InvalidArgumentException('Invalid table "' . $table . '"');
            }

            config([Helpers::CLIENT_CONFIG . '.tables' => [$table => $originalConfig['tables'][$table]]]);

            if ($directory === null) {
                config([Helpers::CLIENT_CONFIG . '.directories' => []]);
            }
        }

        if ($directory !== null) {
            if (!isset($originalConfig['directories'][$directory])) {
                throw new InvalidArgumentException('Invalid directory "' . $directory . '"');
            }

            config([Helpers::CLIENT_CONFIG . '.directories' => [$directory => $originalConfig['directories'][$directory]]]);

            if ($table === null) {
                config([Helpers::CLIENT_CONFIG . '.tables' => []]);
            }
        }

        $client->sync();
    }
}
