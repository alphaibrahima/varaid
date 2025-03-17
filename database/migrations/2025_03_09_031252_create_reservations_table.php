<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('slot_id')->constrained()->onDelete('cascade');
            $table->foreignId('association_id')->constrained('users')->onDelete('cascade');
            
            // Champs optimisés
            $table->enum('size', ['grand', 'moyen', 'petit']);
            $table->integer('quantity')->unsigned();
            $table->date('date');
            $table->string('payment_intent_id')->nullable();
            
            $table->string('code')->unique();
            $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('confirmed');
            
            $table->timestamps();
            
            // Index critiques
            $table->index(['slot_id', 'date']);
            $table->index(['user_id', 'status']);
            $table->index(['association_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};