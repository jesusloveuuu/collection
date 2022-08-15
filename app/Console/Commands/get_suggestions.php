<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\Word;
use App\Models\KeywordsSuggestion;
use App\Services\GTrendsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use XFran\GTrends\GTrends;

class get_suggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_suggestions {begin=0} {limit=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取建议';

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
        $this->info("get_suggestions");

        //参数
        $argument_begin = $this->argument('begin');
        $argument_limit = $this->argument('limit');

        //获取关键词
        $term_array = Term::offset($argument_begin)->take($argument_limit)->get();
        $total = count($term_array);

        //获取关键词推荐
        foreach ($term_array as $index => $temp_term_object) {

            $this->info("index: " . $index . ' / ' . $total);
            $is_save = false;
            if (empty($temp_term_object)) {
                $this->warn("term empty!");
                continue;
            }
            $this->info($temp_term_object->name);
            //var_dump($temp_term_object);

            //查询json
            if (empty($temp_term_object->suggestion_json)) {
                $this->comment($temp_term_object->name . " suggestion_json auto completing...");

                //网络请求
                $suggestion_array = $this->getSuggestionsArray($temp_term_object->name);
                $temp_term_object->suggestion_json = json_encode($suggestion_array);
                $is_save = true;

            } else {
                //已经存在
                $this->info($temp_term_object->name . " already have suggestion_json");
            }

            //完善json，相似度等
            if(1){
                $suggestion_array = json_decode($temp_term_object->suggestion_json,true);
                $array_topics = $suggestion_array['topics'] ?? [];
                if(!empty($array_topics)){
                    if(empty($array_topics[0]['similar'])){

                        //获取相似度map
                        $map_similar = [];
                        foreach ($array_topics as $index_topic => $object_topic){
                            similar_text($temp_term_object->name,$object_topic['title'],$percent);
                            $array_topics[$index_topic]['similar'] = $percent;
                        }

                        array_multisort(array_column($array_topics,'similar'),SORT_DESC,$array_topics);

                        $suggestion_array['topics'] = $array_topics;

                        $temp_term_object->suggestion_json = json_encode($suggestion_array);

                        $is_save = true;

                    }

                }

            }

            if ($is_save) {
                $temp_term_object->save();
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
            'geo' => '', //强制空字符串就是world，但必须有这个字段。否则默认US。
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
