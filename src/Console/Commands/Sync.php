<?php

namespace Stickee\Sync\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Stickee\Sync\Client;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync';

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
        $client = app(Client::class);
        $client->sync();

        // TODO
        // $table = $config['table'] ?? $configName;
        // $tables = $this->argument('table')
        //     ? [$this->argument('table')]
        //     : array_keys(config('sync.tables'));

        // DB::transaction(function () use ($tables) {
        //     // TODO
        //     dump($tables);
        // });
    }
}
