<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\Topic;
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
        $term_array = Term::where('type',0)->offset($argument_begin)->take($argument_limit)->get();
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
            if (empty($term_object->suggestion_json)) {
                $this->comment($term_object->name . " suggestion_json auto completing...");

                //网络请求
                $suggestion_array = $this->getSuggestionsArray($term_object->name);
                $term_object->suggestion_json = json_encode($suggestion_array);
                $is_save = true;

            } else {
                //已经存在
                $this->info($term_object->name . " already have suggestion_json");
            }

            //关联操作
            if(1){
                $suggestion_array = json_decode($term_object->suggestion_json,true);
                $array_topics = $suggestion_array['topics'] ?? [];
                if(!empty($array_topics)){

                    //补充完善json，相似度等
                    if(empty($array_topics[0]['similar'] ?? null)){
                        //获取相似度map
                        $this->info("checking array_topics similar");
                        foreach ($array_topics as $index_topic => $object_topic){
                            similar_text($term_object->name,$object_topic['title'],$percent);
                            $array_topics[$index_topic]['similar'] = $percent;
                        }

                        array_multisort(array_column($array_topics,'similar'),SORT_DESC,$array_topics);

                        $suggestion_array['topics'] = $array_topics;

                        $term_object->suggestion_json = json_encode($suggestion_array);

                        $is_save = true;
                    }

                    //检查字符完全相等
                    $this->info("checking array_topics");
                    foreach ($array_topics as $index_topic => $object_topic){
                        if($term_object->name === $object_topic['title']){
                            $this->info("Comparing ". $object_topic['title'] . ' and ' .$term_object->name  );
                            //创建对应话题
                            $topic_most_similar = Topic::where('mid', $object_topic['mid'])->orderBy('id','desc')->first();
                            if(empty($topic_most_similar)){
                                $topic_most_similar = new Topic();
                                $topic_most_similar = $topic_most_similar->createTopic($object_topic);
                                $this->info("Created Topic ID: " . $topic_most_similar->id);
                                $is_save = true;
                            }else{
                                $this->info("Already exist Topic ID: " . $topic_most_similar->id);
                            }

                            //创建topic对应的term
                            $mid = $object_topic['mid'];
                            $term_most_similar = Term::where('name', '=', $mid)->where('type',1)->orderBy('id', 'desc')->first();

                            //不存在则创建
                            if (empty($term_most_similar)) {
                                $this->comment("\"$mid\" term creating...");
                                $term_most_similar = new Term();
                                $term_most_similar->name = $mid;
                                $term_most_similar->namespace = '';
                                $term_most_similar->type = 1;
                                $term_most_similar->topic_title = $object_topic['title'];
                                $term_most_similar->topic_type = $object_topic['type'];
                                $term_most_similar->save();
                                $this->info("\"$mid\" term created, Term ID: $term_most_similar->id");
                            } else {
                                $this->info("\"$mid\" term already exists, Term ID: $term_most_similar->id");
                            }

                        }
                    }


                }
            }

            if ($is_save) {
                $term_object->save();
            }


        }

        $this->info("end");
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");

    }

    //获取推荐
    public function getSuggestionsArray($keyword)
    {
        $this->info("GTrends...");
        $random = random_int(5, 15);
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
