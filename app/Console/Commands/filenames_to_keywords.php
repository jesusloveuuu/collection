<?php

namespace App\Console\Commands;

use App\Models\ImportKeyword;
use App\Models\Keyword;
use App\Models\KeywordsTag;
use App\Models\Tag;
use Illuminate\Console\Command;

class filenames_to_keywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filenames_to_keywords {path}';

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
        $this->info("path_dir: $path_dir");
        $is_dir = is_dir($path_dir);
        if (empty($is_dir)) {
            $this->error("$path_dir is not a dir!");
            exit();
        }
        var_dump($path_dir);

        //获取一层path
        $dp = dir($path_dir);
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
        $this->info("check tags");
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
        }

        //遍历目录，获取png文件的文件名
        $map_dir_filenames = [];
        $this->info("dir to filenames");
        foreach ($path_child_level_1 as $temp_dir) {
            $temp_dir_path = $path_dir . $temp_dir;
            $temp_filepaths = $this->get_filepaths_by_dir($temp_dir_path);//获取该目录下的全部文件路径
            $temp_filenames = [];
            foreach ($temp_filepaths as $temp_filepath_index => $temp_filepath_value) {
                $temp_filename_value = basename($temp_filepath_value, ".png");
                $temp_filename_value = str_replace("(1)", "", $temp_filename_value);
                $temp_filename_value = str_replace("(2)", "", $temp_filename_value);
                $temp_filename_value = str_replace("_", "'", $temp_filename_value);
                if(strpos($temp_filename_value," - ") !== false ){
                    $temp_filename_value_array = explode(' - ',$temp_filename_value);
                    $temp_filename_value = $temp_filename_value_array[0];
                }
                $temp_filenames[] = $temp_filename_value;
            }

            sort($temp_filenames);
            $temp_filenames = array_values($temp_filenames);
            $temp_filenames = array_flip($temp_filenames);
            $temp_filenames = array_flip($temp_filenames);
            $temp_filenames = array_values($temp_filenames);

            $map_dir_filenames[$temp_dir] = $temp_filenames;
        }
        var_dump($map_dir_filenames);

        //遍历映射，写入keywards
        $this->info("map to db");
        $map_tag_keywords = $map_dir_filenames;
        foreach ($map_tag_keywords as $temp_tag_name => $temp_keyword_names) {
            //查询tag
            $tag = Tag::where('name', $temp_tag_name)->first();
            if(empty($tag)){
                continue;
            }

            //遍历map
            foreach ($temp_keyword_names as $temp_keyword_name) {
                //查找keyword
                $keyword = Keyword::where('name', '=', $temp_keyword_name)->orderBy('id', 'desc')->first();

                //不存在则创建
                if (empty($keyword)) {
                    $this->comment($temp_keyword_name . " keyword creating");
                    $keyword = new Keyword();
                    $keyword->name = $temp_keyword_name;
                    $keyword->save();
                } else {
                    $this->info($temp_keyword_name . " keyword already exists, id: " . $keyword->id);
                }

                //检测关联
                $keyword_tag = KeywordsTag::where('keyword_id', $keyword->id)->where('tag_id', $tag->id)->orderBy('id', 'desc')->first();
                if (empty($keyword_tag)) {
                    //不存在，创建
                    $this->comment($temp_keyword_name . " keyword_tag creating");

                    $keyword_tag = new KeywordsTag();
                    $keyword_tag->keyword_id = $keyword->id;
                    $keyword_tag->keyword_name = $temp_keyword_name ?? '';
                    $keyword_tag->tag_id = $tag->id;
                    $keyword_tag->tag_name = $tag->name ?? '';
                    $keyword_tag->save();
                } else {
                    $this->info("keyword_tag already exists, id: " . $keyword_tag->id);
                }

                //是否补充数据
                if(0){
                    if (empty($keyword_tag->tag_name)) $keyword_tag->tag_name = $tag->name ?? '';
                    if (empty($keyword_tag->keyword_name)) $keyword_tag->keyword_name = $temp_keyword_name ?? '';
                    $keyword_tag->save();
                }

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