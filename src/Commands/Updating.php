<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Console\Command as AppCommand;

class Updating extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:updating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Place an application in maintenance mode while updating';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        file_put_contents(
            base_path('public/index.html'),
            file_get_contents(base_path('vendor/grafite/maintenance/resources/maintenance.html'))
        );

        $this->info('Application is now in maintenance mode');
    }
}
