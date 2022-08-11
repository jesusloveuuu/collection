<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Models\Suggestion;
use Illuminate\Console\Command;
use XFran\GTrends\GTrends;

class trend_suggest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trend_suggest {begin=0} {limit=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'keyword获取建议';

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
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");
        $this->info("begin");



        //获取关键词
        //参数
        $argument_begin = $this->argument('begin');
        $argument_limit = $this->argument('limit');
        $keywords = Keyword::offset($argument_begin)->take($argument_limit)->get();
        $total = count($keywords);
        //循环获取
        foreach ($keywords as $index => $keyword) {
            $this->info("index: " . $index . ' / ' . $total);
            $this->info("keyword: " . $keyword);
            if (empty($keyword)) {
                $this->warn("empty!");
                continue;
            }
            $this->info($keyword->name);

            //最新建议
            $temp_suggestion = Suggestion::where('keyword_id', $keyword->id)->orderBy('id','desc')->first();
            if (empty($temp_suggestion)) {
                $this->comment("suggestion dont have");
                //不存在
                $temp_suggestion = new Suggestion();
                $temp_suggestion->keyword_id = $keyword->id;
                $temp_suggestion->keyword = $keyword->name;

                //获取
                $options = [
                    'hl' => 'en-US',//英文
                    'tz' => 0,//没搞懂
                    'geo' => '', //不加就是world
                    //'geo' => 'US',
                    'time' => 'all',//all就是最初到现在
                    'category' => 0,//0就是全部
                ];

                $this->info("GTrends...");

                $trend = new GTrends($options);
                $array = $trend->getSuggestionsAutocomplete($keyword->name);
                $random = random_int(0,1);
                sleep($random);//暂停，反反爬虫
                var_dump($array);
                if (!empty($array)) {
                    $temp_suggestion->json = json_encode($array);
                }

                $temp_suggestion->save();

            } else {
                //已经存在
                $this->comment("suggestion already have keyword: " . $temp_suggestion->keyword);
                //var_dump($temp_suggestion);
            }


        }

        $this->info("end");
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");

    }
}
