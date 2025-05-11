<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->string('block_reason')->nullable()->after('available');
        });
    }

    public function down()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('block_reason');
        });
    }
};