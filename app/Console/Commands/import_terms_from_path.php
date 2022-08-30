<?php

namespace App\Console\Commands;

use App\Models\ImportKeyword;
use App\Models\Tag;
use App\Models\Term;
use App\Models\TermsTagsPivot;
use App\Models\WordsTag;
use Illuminate\Console\Command;

class import_terms_from_path extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_terms_from_path';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description 从文件名获取关键词';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $path = "import_terms_from_path";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //开始
        $this->line(str_repeat("\n", 32));
        $this->line(str_repeat("-", 128));
        $this->info("begin");
        $this->info("import_terms_from_path");

        //获取相对路径，静态参数
        $argument_path = $this->path ?? "";
        $this->info("argument_path: $argument_path");
        if (empty($argument_path)) {
            $this->error("argument_path is empty!");
            exit();
        }
        var_dump($argument_path);

        //获取绝对路径
        $root_dir = storage_path("app/$argument_path/");
        $is_dir = is_dir($root_dir);
        if (empty($is_dir)) {
            $this->error("$root_dir is not a dir!");
            exit();
        }
        var_dump($root_dir);

        //获取第一层path名称，作为tag
        $dp = dir($root_dir);
        $this->info("get fitst level path dirname...");
        $arr_paths = [];
        //读取path
        while ($file = $dp->read()) {
            if ($file !== "." && $file !== "..") {
                $arr_paths[] = $file;
            }
        }
        $dp->close();
        //排序
        sort($arr_paths);
        var_dump($arr_paths);

        //遍历目录，获取文件（png）的文件名，作为term
        $map_dir_file = [];
        $this->info("get filenames...");
        foreach ($arr_paths as $v_path) {
            //获取该目录下的全部文件名
            $temp_dir = $root_dir . $v_path;
            $temp_files = $this->get_filepaths_by_dir($temp_dir);

            //处理文件名列表
            $arr_temp_filename = [];
            foreach ($temp_files as $i_temp_filepath => $v_temp_filepath) {
                //过滤特殊字符
                $v_temp_filename = basename($v_temp_filepath, ".png");
                $v_temp_filename = str_replace("(1)", "", $v_temp_filename);
                $v_temp_filename = str_replace("(2)", "", $v_temp_filename);
                $v_temp_filename = str_replace("_", "'", $v_temp_filename);
                if (strpos($v_temp_filename, " - ") !== false) {
                    $temp_filename_value_array = explode(' - ', $v_temp_filename);
                    $v_temp_filename = $temp_filename_value_array[0];
                }
                $arr_temp_filename[] = $v_temp_filename;
            }

            //排序等
            sort($arr_temp_filename);
            $arr_temp_filename = array_values($arr_temp_filename);
            $arr_temp_filename = array_flip($arr_temp_filename);
            $arr_temp_filename = array_flip($arr_temp_filename);
            $arr_temp_filename = array_values($arr_temp_filename);

            //过滤特殊字符
            $v_path = str_replace("_", "'", $v_path);

            //修改数组
            $map_dir_file[$v_path] = $arr_temp_filename;
        }
        var_dump($map_dir_file);

        //遍历映射，写入words
        $this->info("map to terms");
        $map_tag_term = $map_dir_file;

        foreach ($map_tag_term as $t_tag_name => $arr_t_term) {
            //查找tag，不存在则创建
            $tag = Tag::where('name', '=', $t_tag_name)->orderBy('id', 'desc')->first();
            if (empty($tag)) {
                $this->comment("\"$t_tag_name\" tag creating...");
                $tag = new Tag();
                $tag->name = $t_tag_name;
                $tag->save();
            } else {
                $this->info("\"$t_tag_name\" tag already exists, Tag ID: $tag->id");
            }

            //遍历map
            foreach ($arr_t_term as $temp_term) {
                //查找term：不存在则创建
                $term = Term::where('name', '=', $temp_term)->orderBy('id', 'desc')->first();
                if (empty($term)) {
                    $this->comment("\"$temp_term\" term creating...");
                    $term = new Term();
                    $term->name = $temp_term;
                    $term->tag_name = $t_tag_name;
                    $term->type = Term::TYPE_TERM;
                    $term->save();
                } else {
                    $this->info("\"$temp_term\" term already exists, Term ID: $term->id");
                }

                //是否补充数据
                if (1) {
                    $this->comment("\"$temp_term\" term auto completing..., Term ID: $term->id");
                    if (empty($term->name)) $term->name = $temp_term ?? '';
                    if (empty($term->tag_name)) $term->tag_name = $t_tag_name ?? '';
                    $term->save();
                }

                //检测关联
                $term_tag_pivot = TermsTagsPivot::where('term_id', $term->id)->where('tag_id', $tag->id)->orderBy('id', 'desc')->first();
                if (empty($term_tag_pivot)) {
                    //不存在，创建
                    $this->comment("term_tag creating");
                    $term_tag_pivot = new TermsTagsPivot();
                    $term_tag_pivot->term_id = $term->id;
                    $term_tag_pivot->term_name = $term->name ?? '';
                    $term_tag_pivot->tag_id = $tag->id;
                    $term_tag_pivot->tag_name = $tag->name ?? '';
                    $term_tag_pivot->save();
                } else {
                    $this->info("term_tag already exists, id: " . $term_tag_pivot->id);
                }

            }

        }


        $this->info("end");
        $this->line(str_repeat("-", 30));


    }

    public function get_all_file_paths($path, &$files)
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file !== "." && $file !== "..") {
                    $this->get_all_file_paths($path . "/" . $file, $files);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $files[] = $path;
        }
    }


    public function get_filepaths_by_dir($dir)
    {
        $files = array();
        $this->get_all_file_paths($dir, $files);
        return $files;
    }


}
