<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableKeywordsTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('keyword_id')->default(0)->index();
            $table->bigInteger('tag_id')->default(0)->index();
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_keywords_tags');
    }
}
