<?php

namespace App\Console\Commands;

use App\Imports\TermsTagsImport;
use App\Models\ImportKeyword;
use App\Models\Tag;
use App\Models\Term;
use App\Models\TermsTagsPivot;
use App\Models\WordsTag;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class import_terms_from_excel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import_terms_from_excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description 从excel获取关键词';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $path = "import_terms_from_excel";

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
        $this->info("import_terms_from_excel");

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


        $import = new TermsTagsImport;
        Excel::import($import, 'import_terms_from_excel\import_terms_from_excel.xlsx');

        $this->info("end");
        $this->line(str_repeat("-", 128));


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
