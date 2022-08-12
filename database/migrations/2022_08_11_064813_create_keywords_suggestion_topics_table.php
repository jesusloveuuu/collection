<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordsSuggestionTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords_suggestion_topics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('keyword_id')->index();
            $table->string('keyword_name');
            $table->unsignedBigInteger('topic_id');
            $table->string('topic_title');
            $table->float('similar')->comment("similar text between keyword name and topic title");
            $table->tinyInteger('is_most_similar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keywords_suggestion_topics');
    }
}
