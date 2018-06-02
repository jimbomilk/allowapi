<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Rightholders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rightholders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('documentId');
            $table->string('relation');
            $table->string('email');
            $table->string('phone');
            $table->smallInteger('status')->default(0);
            $table->mediumText('consent')->nullable();
            $table->dateTime('consent_date')->nullable();

            $table->integer('person_id')->unsigned();
            $table->foreign('person_id')
                ->references('id')
                ->on('persons')
                ->onDelete('cascade');

            $table->integer('location_id')->unsigned()->nullable;
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');

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
        Schema::dropIfExists('rightholders');
    }
}
