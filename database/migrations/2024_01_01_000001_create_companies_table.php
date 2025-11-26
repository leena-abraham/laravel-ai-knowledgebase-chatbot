<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('domain')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('subscription_plan')->default('free'); // free, starter, pro, enterprise
            $table->integer('max_documents')->default(10);
            $table->integer('max_chats_per_month')->default(100);
            $table->integer('current_month_chats')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
