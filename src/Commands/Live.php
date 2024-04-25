<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Console\Command as AppCommand;

class Live extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Place an application in live mode after updating';

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
        unlink(base_path('public/index.html'));

        $this->info('Application is now in live mode');
    }
}
