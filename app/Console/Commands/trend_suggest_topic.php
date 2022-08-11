<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use App\Models\Suggestion;
use App\Models\SuggestionTopic;
use App\Models\Topic;
use Illuminate\Console\Command;

class trend_suggest_topic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trend_suggest_topic {begin=0} {limit=1}';

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


        //参数
        $argument_begin = $this->argument('begin');
        $argument_limit = $this->argument('limit');

        $suggestions = Suggestion::offset($argument_begin)->take($argument_limit)->get();

        foreach ($suggestions as $temp_suggestion){
            if(empty($temp_suggestion->json)){
                continue;
            }

            $suggestion_array = json_decode($temp_suggestion->json,true);
            $array_topics = $suggestion_array['topics'];
            if(!empty($array_topics)){

                //获取相似度map
                $map_similar = [];
                foreach ($array_topics as $array_topic){
                    similar_text($temp_suggestion->keyword,$array_topic['title'],$percent);
                    $map_similar[$array_topic['title']] = $percent;
                }

                //获取顶部话题
                arsort($map_similar);
                reset($map_similar);
                $key_most_similar_title = key($map_similar);

                foreach ($array_topics as $array_topic){
                    //只通过顶部话题
                    if($array_topic['title'] !== $key_most_similar_title){
                        continue;
                    }

                    //检索话题，如无则自动创建
                    $temp_topic = Topic::where('mid', $array_topic['mid'])->orderBy('id','desc')->first();
                    if(empty($temp_topic)){
                        $temp_topic = new Topic();
                        $temp_topic = $temp_topic->createTopic($array_topic);
                        $this->info("created topic id: " . $temp_topic);
                    }

                    //判断是否关联过
                    $temp_suggestion_topic = SuggestionTopic::where('keyword_id', $temp_suggestion->keyword_id)->where('topic_id', $temp_topic->id)->orderBy('id','desc')->first();
                    if(empty($temp_suggestion_topic)){
                        $temp_suggestion_topic = new SuggestionTopic();
                        $temp_suggestion_topic->keyword_id = $temp_suggestion->keyword_id;
                        $temp_suggestion_topic->keyword_name = $temp_suggestion->keyword;
                        $temp_suggestion_topic->topic_id = $temp_topic->id;
                        $temp_suggestion_topic->topic_title = $temp_topic->title;
                        $temp_suggestion_topic->similar = $map_similar[$array_topic['title']] ?? 0;
                        $temp_suggestion_topic->is_most_similar = (int)($key_most_similar_title == $array_topic['title']);
                        $temp_suggestion_topic->save();

                        $this->info("created temp_suggestion_topic similar: " . $map_similar[$array_topic['title']] ?? 0);
                    }

                }
            }else{
                //存在该
            }


        }


        $this->info("end");
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");

    }
}
