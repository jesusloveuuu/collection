<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use XFran\GTrends\GTrends;

class test extends Command
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

        $this->info("begin");

        //This options are by default if none provided
        $options = [
            'hl' => 'en-US',
            'tz' => 0,
            //'geo' => 'US',
            'geo' => '',
            'time' => 'all',
            'category' => 0,
        ];
        $gt = new GTrends($options);

        //$data = $gt->getSuggestionsAutocomplete("台湾");
        $data = $gt->getAllOneKeyWord("台湾");

        //dd($gt->getGeo('/m/0d05w3'));
        //$data = $gt->getAllMultipleKeyWords(['USA', "UK"]);

        var_dump($data);
        //$data = json_decode($jsonString, true);

        // 写文件
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);

        $time = time();

        file_put_contents(storage_path("logs/trend_$time.json"), stripslashes($newJsonString));

        //$this->info("begin：".$gt);

    }

    public function importFile2Keyword($filename = "keyword")
    {


    }

}
