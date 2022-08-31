<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_all', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('term')->index();
            $table->json('json_all_data')->nullable();
            $table->string('hl')->index();
            $table->integer('tz')->index();
            $table->string('geo')->index();
            $table->string('time')->index();
            $table->integer('category')->index();
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
        Schema::dropIfExists('terms_all');
    }
}
