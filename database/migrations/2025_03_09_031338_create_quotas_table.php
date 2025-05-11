<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Ajouter cette ligne pour le DB::statement
use Illuminate\Support\Facades\DB;

class CreateQuotasTable extends Migration {
    public function up()
    {
        Schema::create('quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_id')->constrained('users')->cascadeOnDelete();
            $table->integer('quantite')->unsigned();
            $table->integer('grand')->nullable();
            $table->integer('moyen')->nullable();
            $table->integer('petit')->nullable();
            $table->timestamps();
        });

        // Vérifier si on utilise PostgreSQL avant d'ajouter la contrainte
        if (config('database.default') === 'pgsql') {
            DB::statement('
                ALTER TABLE quotas 
                ADD CONSTRAINT check_quota_repartition 
                CHECK (quantite >= COALESCE(grand, 0) + COALESCE(moyen, 0) + COALESCE(petit, 0))
            ');
        }
    }

    public function down()
    {
        Schema::dropIfExists('quotas');
    }
}