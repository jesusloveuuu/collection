<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Models\KeywordsSuggestion;
use App\Services\GTrendsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use XFran\GTrends\GTrends;

class get_keywords_suggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_keywords_suggestions {begin=0} {limit=1}';

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

        //参数
        $argument_begin = $this->argument('begin');
        $argument_limit = $this->argument('limit');

        //获取关键词
        $keywords = Keyword::offset($argument_begin)->take($argument_limit)->get();
        $total = count($keywords);

        //获取关键词推荐
        foreach ($keywords as $index => $temp_keyword) {
            $this->info("index: " . $index . ' / ' . $total);
            $this->info("keyword: " . $temp_keyword);
            if (empty($temp_keyword)) {
                $this->warn("keyword empty!");
                continue;
            }
            $this->info($temp_keyword->name);

            //查看历史建议
            $temp_suggestion = KeywordsSuggestion::where('keyword_id', $temp_keyword->id)->orderBy('id', 'desc')->first();

            //创建建议，不存在建议
            if (empty($temp_suggestion)) {
                $this->comment($temp_keyword->name . " creating suggestion");

                $temp_suggestion = new KeywordsSuggestion();
                $temp_suggestion->keyword_id = $temp_keyword->id;
                $temp_suggestion->keyword_name = $temp_keyword->name;
                //网络请求
                $suggestion_array = $this->getSuggestionsArray($temp_keyword->name);
                $temp_suggestion->suggestion = json_encode($suggestion_array);
                $temp_suggestion->data_source = "Google Trends";
                $temp_suggestion->tool = "GTrends";
                $temp_suggestion->save();

            } else {
                //已经存在
                $this->info($temp_keyword->name . " already have suggestion");
            }

            //Cache::add('KeysSuggestion:' . $temp_keyword->id,$temp_suggestion->suggestion);

            //补充空字段
            if (0) {
                if (empty($temp_suggestion->suggestion)) {
                    //网络请求
                    $suggestion_array = $this->getSuggestionsArray($temp_keyword->name);
                    $temp_suggestion->suggestion = json_encode($suggestion_array);
                }

                $temp_suggestion->data_source = "Google Trends";
                $temp_suggestion->tool = "GTrends";
                $temp_suggestion->save();

            }

        }

        $this->info("end");
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");

    }

    //获取推荐
    public function getSuggestionsArray($keyword)
    {
        $this->info("GTrends...");
        $random = random_int(0, 1);
        sleep($random);//暂停，反反爬虫
        $options = [
            'hl' => 'en-US',//英文
            'tz' => 0,//没搞懂
            'geo' => '', //不加就是world
            //'geo' => 'US',
            'time' => 'all',//all就是最初到现在
            'category' => 0,//0就是全部
        ];

        $trend_service = new GTrendsService($options);
        $array = $trend_service->getSuggestionsAutocomplete($keyword);
        var_dump($array);

        return $array;

    }
}
