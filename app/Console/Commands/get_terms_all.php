<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\TermsAll;
use App\Models\TermsSuggestion;
use App\Models\Topic;
use App\Services\GTrendsService;
use Illuminate\Console\Command;

class get_terms_all extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_terms_all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取全部关联数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $options = [
        'hl' => 'en-US',//英文
        'tz' => 0,//没搞懂
        'geo' => '', //强制空字符串就是world，但必须有这个字段。否则默认US。
        //'geo' => 'US',
        'time' => 'all',//all就是最初到现在
        'category' => 0,//0就是全部
    ];

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
        $this->info("get_terms_all");

        //获取关键词（话题）
        $arr_obj_terms = Term::where('type', Term::TYPE_TOPIC)->get();
        $total = count($arr_obj_terms);

        //获取关键词推荐
        foreach ($arr_obj_terms as $i_term => $obj_term) {

            $this->info("index: " . $i_term . ' / ' . $total);

            if (empty($obj_term)) {
                $this->warn("term empty!");
                continue;
            }
            $this->info($obj_term->term);

            //查询（关键词）全部数据
            $obj_term_all = TermsAll::where('term', $obj_term->term)->orderBy('id', 'desc')->first();

            //不存在则创建
            if (empty($obj_term_all)) {
                $this->comment($obj_term->term . " terms_all creating...");
                //网络请求
                $arr_term_all = $this->getAllOneKeywordArray($obj_term->term);
                if (empty($arr_term_all)) {
                    $this->warn("arr_term_all empty!");
                }

                //空也创建
                $obj_term_all = new TermsAll();
                $obj_term_all->term = $obj_term->term;
                $obj_term_all->json_all = json_encode($arr_term_all);
                foreach ($this->options as $k_option => $v_option) {
                    $obj_term_all->$k_option = $v_option;
                }
                $obj_term_all->save();
            }

            //临时补充关联字段
            if(1){
                if (!empty($obj_term_all)) {
                    foreach ($this->options as $k_option => $v_option) {
                        $obj_term_all->$k_option = $v_option;
                    }
                    $obj_term_all->save();

                }
            }

            //存在，但空，则补充
            if ($obj_term_all !== null) {
                if (empty($obj_term_all->json_all)) {
                    $this->comment($obj_term->term . " json_all auto completing...");

                    //网络请求
                    $arr_term_all = $this->getAllOneKeywordArray($obj_term->term);
                    if (empty($arr_term_all)) {
                        $this->warn("arr_term_all empty!");
                    }

                    //空也补充
                    $obj_term_all->json_all = json_encode($arr_term_all);
                    foreach ($this->options as $k_option => $v_option) {
                        $obj_term_all->$k_option = $v_option;
                    }
                    $obj_term_all->save();
                }
            }

        }

        $this->info("end");
        $this->line(str_repeat("-", 128));

    }

    //获取全部
    public function getAllOneKeywordArray($keyword)
    {
        $this->info("GTrends...");
        $options = $this->options;

        $trend_service = new GTrendsService($options);
        $array = $trend_service->getAllOneKeyWord($keyword);
        //var_dump($array);

        return $array;

    }
}
