<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevenueTargetsTable extends Migration
{
    public function up()
    {
        Schema::create('revenue_targets', function (Blueprint $table) {
            $table->id();
            $table->integer('periode'); // tahun, contoh: 2025
            $table->tinyInteger('bulan'); // bulan dalam integer 1-12
            $table->bigInteger('revenue_target'); // revenue target dalam rupiah
            $table->char('flag', 1)->default('Y'); // varchar 1 digit, default Y
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revenue_targets');
    }
}
