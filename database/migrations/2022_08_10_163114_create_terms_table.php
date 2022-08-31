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
            $table->string('term')->primary()->comment('主键');
            $table->string('title')->nullable()->comment('标题');
            $table->unsignedTinyInteger('type')->default(0)->comment('类型，0=term，1=topic，2=query');
            $table->string('classification')->nullable()->comment('人工分类');
            $table->string('description')->nullable()->comment('描述');
/*            $table->json('suggestion_json')->nullable();
            $table->json('related_topics_json')->nullable();
            $table->json('all_json')->nullable();*/
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
