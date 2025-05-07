<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les colonnes d'affiliation si elles existent
            if (Schema::hasColumn('users', 'affiliation_code')) {
                $table->dropColumn('affiliation_code');
            }
            if (Schema::hasColumn('users', 'affiliation_verified')) {
                $table->dropColumn('affiliation_verified');
            }
            if (Schema::hasColumn('users', 'affiliation_verified_at')) {
                $table->dropColumn('affiliation_verified_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter les colonnes d'affiliation en cas de rollback
            $table->string('affiliation_code')->nullable();
            $table->boolean('affiliation_verified')->default(false);
            $table->timestamp('affiliation_verified_at')->nullable();
        });
    }
};