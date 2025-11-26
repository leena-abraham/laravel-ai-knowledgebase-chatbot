<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->json('context_chunks')->nullable(); // IDs of chunks used for this response
            $table->string('model_used')->nullable(); // gpt-4, gpt-3.5-turbo, etc.
            $table->integer('tokens_used')->nullable();
            $table->decimal('response_time', 8, 2)->nullable(); // in seconds
            $table->boolean('was_helpful')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
            
            $table->index(['chat_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
