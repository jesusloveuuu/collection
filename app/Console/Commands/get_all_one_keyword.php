<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\Topic;
use App\Services\GTrendsService;
use Illuminate\Console\Command;

class get_all_one_keyword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_all_one_keyword {begin=0} {limit=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取关联全部 Command description';

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
        $this->info("get_all_one_keyword");

        //参数
        $argument_begin = $this->argument('begin');
        $argument_limit = $this->argument('limit');

        //获取关键词
        $term_array = Term::offset($argument_begin)->take($argument_limit)->get();
        $total = count($term_array);

        //获取关键词推荐
        foreach ($term_array as $index => $term_object) {

            $this->info("index: " . $index . ' / ' . $total);
            $is_save = false;
            if (empty($term_object)) {
                $this->warn("term empty!");
                continue;
            }
            $this->info($term_object->name);
            //var_dump($temp_term_object);

            //查询json
            if (empty($term_object->all_json)) {
                $this->comment($term_object->name . " all_json getting...");

                //网络请求
                $all_array = $this->getAllOneKeywordArray($term_object->name);
                $term_object->all_json = json_encode($all_array);
                $is_save = true;

            } else {
                //已经存在
                $this->info($term_object->name . " already have all_json, ID: " . $term_object->id);
            }

            if ($is_save) {
                $term_object->save();
            }


        }

        $this->info("end");
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");

    }

    //获取全部
    public function getAllOneKeywordArray($keyword)
    {
        $this->info("GTrends...");
        $random = random_int(60, 90);
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
        $array = $trend_service->getAllOneKeyWord($keyword);
        //var_dump($array);

        return $array;

    }
}
