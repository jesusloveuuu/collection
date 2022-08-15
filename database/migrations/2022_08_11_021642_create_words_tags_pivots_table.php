<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordsTagsPivotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('words_tags_pivots', function (Blueprint $table) {
            $table->bigIncrements('id');
            //word, 词
            $table->unsignedBigInteger('word_id')->default(0)->index();
            $table->string('word_name')->default("");
            //tag，标签
            $table->unsignedBigInteger('tag_id')->default(0)->index();
            $table->string('tag_name')->default("");
            //
            $table->unique(['word_id','tag_id']);
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
        Schema::dropIfExists('words_tags_pivots');
    }
}
