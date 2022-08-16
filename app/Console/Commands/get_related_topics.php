<?php

namespace App\Console\Commands;

use App\Models\Term;
use Illuminate\Console\Command;

class get_related_topics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_suggestions111 {begin=0} {limit=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取关联话题 Command description';

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

            //关联操作
            if(1){
                $suggestion_array = json_decode($temp_term_object->suggestion_json,true);
                $array_topics = $suggestion_array['topics'] ?? [];
                if(!empty($array_topics)){

                    //补充完善json，相似度等
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

                    //创建topic，字符完全相等
                    foreach ($array_topics as $index_topic => $object_topic){
                        if($temp_term_object->name === $object_topic){

                        }
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
}
