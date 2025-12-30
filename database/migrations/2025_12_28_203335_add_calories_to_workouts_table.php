<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
    Schema::table('workouts', function (Blueprint $table) {
        $table->integer('calories')->default(0); // default 0
    });
    }

    public function down()
   {
    Schema::table('workouts', function (Blueprint $table) {
        $table->dropColumn('calories');
    });
   }
};
