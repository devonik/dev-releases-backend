<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTechsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('techs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("github_release_id")->nullable();
            $table->string("title");
            $table->string("hero_image")->nullable();
            $table->string("latest_tag")->nullable();
            $table->string("github_owner");
            $table->string("github_repo");
            $table->timestamps();

            $table->unique(['title', 'github_owner']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('techs');
    }
}
