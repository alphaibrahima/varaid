<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->integer('recipients_count')->default(0);
            $table->string('status')->default('pending');
            $table->json('response')->nullable();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_messages');
    }
};