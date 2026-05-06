<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('print_logo')->nullable();
            $table->string('fav_icon')->nullable();
            $table->text('short_about')->nullable();
            $table->text('address')->nullable();
            $table->text('currency')->nullable();
            $table->text('map_embed')->nullable();
            $table->text('return_policy')->nullable();
            $table->text('barcode_height')->default('1in');
            $table->text('barcode_width')->default('1.5in');
            $table->foreignId('user_id');
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
        Schema::dropIfExists('site_infos');
    }
}
