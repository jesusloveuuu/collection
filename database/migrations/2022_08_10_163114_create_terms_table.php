<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsTable extends Migration
{
    /**
     * Run the migrations.
     * 条目表
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms', function (Blueprint $table) {
            //$table->bigIncrements('id');
            $table->string('name')->primary();
            $table->string('tag_name')->index();
            $table->unsignedTinyInteger('type')->default(0)->comment('类型，0=term，1=topic，2=query');
            $table->json('suggestion_json')->nullable();
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
        Schema::dropIfExists('terms');
    }
}
