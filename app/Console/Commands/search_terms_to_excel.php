<?php

namespace App\Console\Commands;

use App\Exports\TermsExport;
use App\Exports\TermsSearchExport;
use App\Imports\TermsTagsImport;
use App\Models\ImportKeyword;
use App\Models\Tag;
use App\Models\Term;
use App\Models\TermsTagsPivot;
use App\Models\WordsTag;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class search_terms_to_excel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search_terms_to_excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description 搜索关键词到excel';

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
        //开始
        $this->line(str_repeat("\n", 32));
        $this->line(str_repeat("-", 128));
        $this->info("begin");
        $this->info("search_terms_to_excel");

        //过滤term


        $date = date('Y_m_d_H_i_s',time());
        $filename = "Terms_Search_$date.xlsx";

        $this->info("storing...$filename");
        $is_success  = Excel::store(new TermsSearchExport(), $filename,'export');

        if($is_success){
            $this->info("export success!");
        }else{
            $this->warn("export failed!");
        }

        $this->info("end");
        $this->line(str_repeat("-", 128));


    }


}
