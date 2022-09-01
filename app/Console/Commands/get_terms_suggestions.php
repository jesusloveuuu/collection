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

class get_terms_suggestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_terms_suggestions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取关键词建议';

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

        //获取关键词
        $arr_obj_terms = Term::where('type', Term::TYPE_TERM)/*->offset($argument_begin)->take($argument_limit)*/ ->get();
        $total = count($arr_obj_terms);

        //获取（关键词）推荐
        foreach ($arr_obj_terms as $i_term => $obj_term) {

            $this->info("index: " . $i_term . ' / ' . $total);

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
                $this->comment($obj_term->term . " terms_suggestions creating...");
                //网络请求
                $arr_suggestion = $this->getSuggestionsArray($obj_term->term);
                if (empty($arr_suggestion)) {
                    $this->warn("arr_suggestion empty!");
                }

                //空也创建
                $obj_suggestion = new TermsSuggestion();
                $obj_suggestion->term = $obj_term->term;
                $obj_suggestion->json_suggestion = json_encode($arr_suggestion);
                $obj_suggestion->data_source = 'Google Trends';
                $obj_suggestion->save();
            }

            //存在，但空，则补充
            if ($obj_suggestion !== null) {
                if (empty($obj_suggestion->json_suggestion)) {
                    $this->comment($obj_term->term . " json_suggestion auto completing...");

                    //网络请求
                    $arr_suggestion = $this->getSuggestionsArray($obj_term->term);
                    if (!empty($arr_suggestion)) {
                        $obj_suggestion->json_suggestion = json_encode($arr_suggestion);
                        $obj_suggestion->data_source = 'Google Trends';
                        $obj_suggestion->save();
                    } else {
                        $this->warn("arr_suggestion empty!");
                    }
                }
            }


        }

        $this->info("end");
        $this->line(str_repeat("-", 128));

    }

    //获取推荐
    public function getSuggestionsArray($keyword)
    {
        $this->info("GTrends...");

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
