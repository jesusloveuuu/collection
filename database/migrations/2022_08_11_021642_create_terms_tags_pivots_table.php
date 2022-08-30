<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsTagsPivotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_tags_pivots', function (Blueprint $table) {
            $table->bigIncrements('id');
            //term, 词
            $table->unsignedBigInteger('term_id')->default(0)->index();
            $table->string('term_name')->default("");
            //tag，标签
            $table->unsignedBigInteger('tag_id')->default(0)->index();
            $table->string('tag_name')->default("");
            //
            $table->unique(['term_id','tag_id']);
            $table->index('term_name');
            $table->index('tag_name');
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
        Schema::dropIfExists('terms_tags_pivots');
    }
}
