<?php

namespace App\Console\Commands;

use App\Models\ImportKeyword;
use App\Models\Term;
use App\Models\WordsTag;
use Illuminate\Console\Command;

class import_terms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_terms {path}';

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

    /*    public $path_dirs = [
            "Academic discipline",
            "Bachelor_s degree",
            "Bag",
            "Car",
            "Career",
            "Computer",
            "Food",
            "Internet",
            "Master_s Degree",
            "Region",
            "Sports",
            "Vehicle",
        ];*/

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->line("--------------------------------------------------------------------------------------------------------------------------------");
        $this->info("begin");
        $this->info("import_terms");

        //获取参数
        $argument_path = $this->argument('path');
        $this->info("argument_path: $argument_path");
        if (empty($argument_path)) {
            $this->error("argument_path is empty!");
            exit();
        }
        var_dump($argument_path);

        //路径校验，在storage/app下的
        $path_dir = storage_path("app/$argument_path/");
        $is_dir = is_dir($path_dir);
        if (empty($is_dir)) {
            $this->error("$path_dir is not a dir!");
            exit();
        }
        var_dump($path_dir);

        //获取第一层path名称，作为namespace
        $dp = dir($path_dir);
        $this->info("get 1st level dirname...");
        $path_child_level_1 = [];
        while ($file = $dp->read()) {
            if ($file !== "." && $file !== "..") {
                $path_child_level_1[] = $file;
            }
        }
        $dp->close();
        sort($path_child_level_1);
        var_dump($path_child_level_1);

        //一层path写入tags表
        /*        $this->info("check tags");
                foreach ($path_child_level_1 as $temp_dir) {
                    $tag = Tag::where('name', $temp_dir)->first();
                    if (empty($tag)) {
                        $this->comment($temp_dir . " creating");
                        $tag = new Tag();
                        $temp_dir = str_replace("_", "'", $temp_dir);
                        $tag->name = $temp_dir;
                        $tag->save();
                    } else {
                        $this->info($temp_dir . " tag already exists, id: " . $tag->id);
                    }
                }*/

        //遍历目录，只获取（png）文件的文件名，过滤特殊字符
        $dir_filename_map_array = [];
        $this->info("get filenames...");
        foreach ($path_child_level_1 as $temp_dir) {
            $temp_dir_path = $path_dir . $temp_dir;
            $temp_filepaths = $this->get_filepaths_by_dir($temp_dir_path);//获取该目录下的全部文件名
            $temp_filename_array = [];
            foreach ($temp_filepaths as $temp_filepath_index => $temp_filepath_value) {
                $temp_filename_value = basename($temp_filepath_value, ".png");
                $temp_filename_value = str_replace("(1)", "", $temp_filename_value);
                $temp_filename_value = str_replace("(2)", "", $temp_filename_value);
                $temp_filename_value = str_replace("_", "'", $temp_filename_value);
                if (strpos($temp_filename_value, " - ") !== false) {
                    $temp_filename_value_array = explode(' - ', $temp_filename_value);
                    $temp_filename_value = $temp_filename_value_array[0];
                }
                $temp_filename_array[] = $temp_filename_value;
            }

            sort($temp_filename_array);
            $temp_filename_array = array_values($temp_filename_array);
            $temp_filename_array = array_flip($temp_filename_array);
            $temp_filename_array = array_flip($temp_filename_array);
            $temp_filename_array = array_values($temp_filename_array);

            $dir_filename_map_array[$temp_dir] = $temp_filename_array;
        }
        var_dump($dir_filename_map_array);

        //遍历映射，写入words
        $this->info("map to terms");
        $map_namespace_term_array = $dir_filename_map_array;

        foreach ($map_namespace_term_array as $temp_namespace => $temp_term_name_array) {
            //查询tag
            /*            $tag = Tag::where('name', $temp_tag_name)->first();
                        if(empty($tag)){
                            continue;
                        }*/

            //遍历map
            foreach ($temp_term_name_array as $temp_term_name) {
                //查找word
                $term = Term::where('name', '=', $temp_term_name)->orderBy('id', 'desc')->first();

                //不存在则创建
                if (empty($term)) {
                    $this->comment("\"$temp_term_name\" term creating...");
                    $term = new Term();
                    $term->name = $temp_term_name;
                    $term->namespace = $temp_namespace;
                    $term->save();
                } else {
                    $this->info("\"$temp_term_name\" term already exists, Term ID: $term->id");
                }

                //是否补充数据
                if (0) {
                    $this->comment("\"$temp_term_name\" term auto completing..., Term ID: $term->id");
                    if (empty($term->name)) $term->name = $temp_term_name ?? '';
                    if (empty($term->namespace)) $term->namespace = $temp_namespace ?? '';
                    $term->save();
                }

                //检测关联
                /*                $keyword_tag = KeywordsTag::where('keyword_id', $keyword->id)->where('tag_id', $tag->id)->orderBy('id', 'desc')->first();
                                if (empty($keyword_tag)) {
                                    //不存在，创建
                                    $this->comment($temp_word_name . " word_tag creating");

                                    $keyword_tag = new KeywordsTag();
                                    $keyword_tag->keyword_id = $keyword->id;
                                    $keyword_tag->keyword_name = $temp_word_name ?? '';
                                    $keyword_tag->tag_id = $tag->id;
                                    $keyword_tag->tag_name = $tag->name ?? '';
                                    $keyword_tag->save();
                                } else {
                                    $this->info("keyword_tag already exists, id: " . $keyword_tag->id);
                                }*/


            }

        }


        $this->info("end");
        $this->line("--------------------------------------------------------------------------------------------------------------------------------");


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
