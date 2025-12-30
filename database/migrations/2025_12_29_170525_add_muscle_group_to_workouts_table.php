<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
{
    Schema::table('workouts', function (Blueprint $table) {
        $table->string('muscle_group')->nullable()->after('calories');
    });
}

public function down()
{
    Schema::table('workouts', function (Blueprint $table) {
        $table->dropColumn('muscle_group');
    });
}



};