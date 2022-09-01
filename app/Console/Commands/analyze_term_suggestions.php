<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\TermsTagsPivot;
use App\Models\Topic;
use App\Models\Word;
use App\Models\TermsSuggestion;
use App\Services\GTrendsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use XFran\GTrends\GTrends;

class analyze_term_suggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze_term_suggestions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '解析关键词建议';

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
        $this->info("get_terms_suggestions");

        //参数
        //$argument_begin = $this->argument('begin');
        //$argument_limit = $this->argument('limit');

        //获取关键词-建议
        $arr_obj_term_suggestions = TermsSuggestion::all();
        $total = count($arr_obj_term_suggestions);

        //获取（关键词）推荐
        foreach ($arr_obj_term_suggestions as $i_sug => $obj_sug) {
            if (empty($obj_sug)) {
                $this->warn("sug empty!");
                continue;
            }
            $this->info("index: " . $i_sug . ' / ' . $total . "ID: " . $obj_sug->id);

            //查询对应的term
            $t_term = Term::where('term', $obj_sug->term)->first();
            if (empty($t_term)) {
                $this->warn("Not Exist Term:" . $obj_sug->term);
                continue;
            }

            $is_save = false;

            //解析建议
            $arr_suggestion = json_decode($obj_sug->json_suggestion, true);
            $arr_topics = $arr_suggestion['topics'] ?? [];
            if (!empty($arr_topics)) {

                //遍历推荐topics
                foreach ($arr_topics as $i_topic => $arr_topic) {
                    //没有相似度
                    if (empty($arr_topic['similar'])) {
                        //计算相似度
                        similar_text($obj_sug->term, $arr_topic['title'], $percent);
                        $arr_topics[$i_topic]['similar'] = $percent;
                        $is_save = true;
                    }

                    //全等话题字符
                    if ($obj_sug->term === $arr_topic['title']) {
                        //全等话题，没有则自动创建
                        $topic_most_similar = Topic::where('mid', $arr_topic['mid'])->first();
                        if (empty($topic_most_similar)) {
                            $this->comment("Creating...Topic: " . $arr_topic['mid']);
                            $topic_most_similar = new Topic();
                            $topic_most_similar->mid = $arr_topic['mid'];
                            $topic_most_similar->title = $arr_topic['title'];
                            $topic_most_similar->type = $arr_topic['type'];
                            $topic_most_similar->save();
                            $this->info("Created Topic mid: " . $topic_most_similar->mid);
                        } else {
                            $this->info("Already exist Topic mid: " . $topic_most_similar->mid);
                        }

                        //查询对应的term，没有则自动创建
                        $term_most_similar = Term::where('term', '=', $arr_topic['mid'])->where('type', Term::TYPE_TOPIC)->first();
                        if (empty($term_most_similar)) {
                            $this->comment("Creating...term: " . $arr_topic['mid']);
                            $term_most_similar = new Term();
                            $term_most_similar->term = $arr_topic['mid'];
                            $term_most_similar->type = Term::TYPE_TOPIC;
                            $term_most_similar->topic_title = $arr_topic['title'];
                            $term_most_similar->topic_type = $arr_topic['type'];
                            $term_most_similar->tag_first = $t_term->tag_first ?? '';//继承首要标签
                            $term_most_similar->save();

                            $this->info("Created term: " . $term_most_similar->term);
                        } else {
                            $this->info("Already exist term: " . $term_most_similar->term);
                            //补充数据
                            if (empty($term_most_similar->topic_title)) {
                                $this->comment("Auto completing topic_title: " . $term_most_similar->term);
                                $term_most_similar->topic_title = $arr_topic['title'];
                                $term_most_similar->save();
                            }
                            if ($term_most_similar->topic_type) {
                                $this->comment("Auto completing topic_type: " . $term_most_similar->term);
                                $term_most_similar->topic_type = $arr_topic['type'];
                                $term_most_similar->save();
                            }
                            if (empty($term_most_similar->tag_first)) {
                                $this->comment("Auto completing tag_first: " . $term_most_similar->term);
                                $term_most_similar->tag_first = $t_term->tag_first ?? '';//继承首要标签
                                $term_most_similar->save();
                            }
                        }

                        //创建新term和原tag关联
                        if (!empty($term_most_similar->term) && $term_most_similar->tag_first) {
                            $term_tag_pivot = TermsTagsPivot::where('term', $term_most_similar->term)->where('tag', $term_most_similar->tag_first)->orderBy('id', 'desc')->first();
                            if (empty($term_tag_pivot)) {
                                //不存在，创建
                                $this->comment("Creating...term_tag");
                                $term_tag_pivot = new TermsTagsPivot();
                                $term_tag_pivot->term = $term_most_similar->term;
                                $term_tag_pivot->tag = $term_most_similar->tag_first;
                                $term_tag_pivot->save();
                            } else {
                                $this->info("Already exists, term_tag id: " . $term_tag_pivot->id);
                            }
                        }

                    }
                }

                //自动排序
                if ($is_save) {
                    array_multisort(array_column($arr_topics, 'similar'), SORT_DESC, $arr_topics);
                    $arr_suggestion['topics'] = $arr_topics;
                    $obj_sug->json_suggestion = json_encode($arr_suggestion);
                    $obj_sug->save();
                }

            }

        }

        $this->info("end");
        $this->line(str_repeat("-", 128));

    }

}
