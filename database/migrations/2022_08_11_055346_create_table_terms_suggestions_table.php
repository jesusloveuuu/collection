<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermsSuggestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms_suggestions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('term')->index();
            $table->json('json_suggestion');
            $table->string('data_source')->default("");
            $table->timestamps();
            //$table->unsignedBigInteger('term_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('terms_suggestions');
    }
}
