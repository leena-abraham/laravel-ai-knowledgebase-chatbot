<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chunk_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->json('vector'); // Store embedding vector as JSON
            $table->string('model')->default('text-embedding-3-small'); // OpenAI model used
            $table->integer('dimensions')->default(1536);
            $table->timestamps();
            
            $table->index('chunk_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embeddings');
    }
};
