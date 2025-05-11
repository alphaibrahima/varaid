<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliation_code')->nullable()->after('association_id');
            $table->boolean('affiliation_verified')->default(false)->after('affiliation_code');
            $table->timestamp('affiliation_verified_at')->nullable()->after('affiliation_verified');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['affiliation_code', 'affiliation_verified', 'affiliation_verified_at']);
        });
    }
};