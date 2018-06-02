<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRightholdersPhoto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rightholder_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('owner');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('rhphone');
            $table->string('rhname');
            $table->string('rhemail');
            $table->mediumText('link');
            $table->integer('status'); // 0 - pendiente, 100 - rechazado , 200 - aceptado
            $table->timestamps();
            $table->unsignedInteger('photo_id');
            $table->foreign('photo_id')
                ->references('id')
                ->on('photos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rightholder_photos');
    }
}
