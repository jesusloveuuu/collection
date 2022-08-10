<?php

namespace App\Console\Commands;

use App\Models\ImportKeyword;
use App\Models\Tag;
use Illuminate\Console\Command;

class keywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keywords {action?}';

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

    public $path_dirs = [
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
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //dirs写入tags表
        foreach ($this->path_dirs as $temp_dir) {
            $tag = Tag::where('name',$temp_dir)->first();
            if(empty($tag)){
                $tag = new Tag();
                $tag->name = $temp_dir;
                $tag->save();
            }
        }

        //遍历目录，获取png文件的文件名
        $map_dir_filenames = [];
        foreach ($this->path_dirs as $temp_dir) {
            $temp_dir_path = storage_path('app/Google Trends/' . $temp_dir);
            $temp_filepaths = $this->get_filepaths_by_dir($temp_dir_path);//获取该目录下的全部文件路径
            $temp_filenames = [];
            foreach ($temp_filepaths as $temp_filepath_index => $temp_filepath_value) {
                $temp_filename_value = basename($temp_filepath_value, ".png");
                $temp_filename_value = str_replace("(1)","",$temp_filename_value);
                $temp_filenames[] = $temp_filename_value;
            }

            sort($temp_filenames);
            $temp_filenames = array_values($temp_filenames);
            $temp_filenames = array_flip($temp_filenames);
            $temp_filenames = array_flip($temp_filenames);
            $temp_filenames = array_values($temp_filenames);

            $map_dir_filenames[$temp_dir] = $temp_filenames;

        }
        dd($map_dir_filenames);

        //遍历映射，写入keywards
        foreach ($this->path_dirs as $temp_dir) {
            //查询tag_id
            $tag = Tag::where()


            $keyword = ImportKeyword::where('');


        }

        $map_tag_keywords = $map_dir_filenames;



        foreach ($filenames as $index => $value) {
            $temp = basename($value, ".png");
            $filenames[$index] = $temp;
            //echo $value, PHP_EOL;
            $inserts[] = [
                'keyword' => $temp,
            ];
        }

        $keyword =

            dd($filenames);

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
