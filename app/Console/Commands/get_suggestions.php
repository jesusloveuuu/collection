<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\Topic;
use App\Models\Word;
use App\Models\TermsSuggestion;
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
    protected $signature = 'get_suggestions';

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
        $this->line(str_repeat("\n", 32));
        $this->line(str_repeat("-", 128));
        $this->info("begin");
        $this->info("get_suggestions");

        //参数
        //$argument_begin = $this->argument('begin');
        //$argument_limit = $this->argument('limit');

        //获取关键词
        $arr_obj_terms = Term::where('type', Term::TYPE_TERM)/*->offset($argument_begin)->take($argument_limit)*/ ->get();
        $total = count($arr_obj_terms);

        //获取（关键词）推荐
        foreach ($arr_obj_terms as $i_term => $obj_term) {

            $this->info("index: " . $i_term . ' / ' . $total);
            $is_save = false;
            if (empty($obj_term)) {
                $this->warn("term empty!");
                continue;
            }
            $this->info($obj_term->term);
            //var_dump($temp_term_object);

            //查询（关键词）推荐
            $obj_suggestion = TermsSuggestion::where('term', $obj_term->term)->orderBy('id', 'desc')->first();

            //不存在则创建
            if (empty($obj_suggestion)) {
                $this->comment($obj_term->term . "terms_suggestions creating...");
                //网络请求
                $arr_suggestion = $this->getSuggestionsArray($obj_term->term);
                if (!empty($arr_suggestion)) {
                    $obj_suggestion = new TermsSuggestion();
                    $obj_suggestion->term = $obj_term->term;
                    $obj_term->suggestion_json = json_encode($arr_suggestion);
                    $obj_term->data_source = 'Google Trends';
                    $obj_term->save();
                } else {
                    $this->warn("arr_suggestion empty!");
                }
            }

            //存在，但空，则补充
            if (!empty($obj_suggestion)) {
                if (empty($obj_suggestion->json_suggestion)) {
                    $this->comment($obj_term->term . " json_suggestion auto completing...");

                    //网络请求
                    $arr_suggestion = $this->getSuggestionsArray($obj_term->term);
                    if (!empty($arr_suggestion)) {
                        $obj_term->suggestion_json = json_encode($arr_suggestion);
                        $obj_term->data_source = 'Google Trends';
                        $obj_term->save();
                    } else {
                        $this->warn("arr_suggestion empty!");
                    }
                }
            }

            //关联操作
            if (0) {
                $arr_suggestion = json_decode($obj_term->suggestion_json, true);
                $array_topics = $arr_suggestion['topics'] ?? [];
                if (!empty($array_topics)) {

                    //补充完善json，相似度等
                    if (empty($array_topics[0]['similar'] ?? null)) {
                        //获取相似度map
                        $this->info("checking array_topics similar");
                        foreach ($array_topics as $index_topic => $object_topic) {
                            similar_text($obj_term->name, $object_topic['title'], $percent);
                            $array_topics[$index_topic]['similar'] = $percent;
                        }

                        array_multisort(array_column($array_topics, 'similar'), SORT_DESC, $array_topics);

                        $arr_suggestion['topics'] = $array_topics;

                        $obj_term->suggestion_json = json_encode($arr_suggestion);

                        $is_save = true;
                    }

                    //检查字符完全相等
                    $this->info("checking array_topics");
                    foreach ($array_topics as $index_topic => $object_topic) {
                        if ($obj_term->name === $object_topic['title']) {
                            $this->info("Comparing " . $object_topic['title'] . ' and ' . $obj_term->name);
                            //创建对应话题
                            $topic_most_similar = Topic::where('mid', $object_topic['mid'])->orderBy('id', 'desc')->first();
                            if (empty($topic_most_similar)) {
                                $topic_most_similar = new Topic();
                                $topic_most_similar = $topic_most_similar->createTopic($object_topic);
                                $this->info("Created Topic ID: " . $topic_most_similar->id);
                                $is_save = true;
                            } else {
                                $this->info("Already exist Topic ID: " . $topic_most_similar->id);
                            }

                            //创建topic对应的term
                            $mid = $object_topic['mid'];
                            $term_most_similar = Term::where('name', '=', $mid)->where('type', 1)->orderBy('id', 'desc')->first();

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
                $obj_term->save();
            }


        }

        $this->info("end");
        $this->line(str_repeat("-", 128));

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
