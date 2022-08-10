<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use XFran\GTrends\GTrends;

class trend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");

        $this->info("begin：");


        //This options are by default if none provided
        $options = [
            'hl' => 'en-US',
            'tz' => 0,
            'geo' => 'US',
            'time' => 'all',
            'category' => 0,
        ];
        $gt = new GTrends($options);
        dd($gt->getRelatedTopics('/m/0d05w3'));


        $this->info("begin：".$gt);

    }
}
