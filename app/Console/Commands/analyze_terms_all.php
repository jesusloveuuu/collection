<?php

namespace App\Console\Commands;

use App\Models\Term;
use App\Models\TermsAll;
use App\Models\TermsTagsPivot;
use App\Models\Topic;
use App\Models\Word;
use App\Models\TermsSuggestion;
use App\Services\GTrendsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use XFran\GTrends\GTrends;

class analyze_terms_all extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze_terms_all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '解析关键词全部';

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
        $this->info("analyze_terms_all");

        $this->info("end");
        $this->line(str_repeat("-", 128));

    }

}
