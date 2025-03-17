<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Nouveaux champs optimisés
            $table->enum('role', ['admin', 'buyer', 'association'])->default('buyer');
            $table->string('phone')->unique()->nullable();
            // $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('registration_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('association_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};