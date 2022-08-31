<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExplorationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('explorations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('term')->index();
            $table->unsignedTinyInteger('type')->default(0)->comment('类型，0=term，1=topic，2=query');
            $table->json('json_suggestion')->nullable();
            $table->json('related_topics_json')->nullable();
            $table->json('all_json')->nullable();
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
        Schema::dropIfExists('explorations');
    }
}
