<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_id')->nullable()->index();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');

            $table->string('mood')->nullable();
            $table->string('vibe')->nullable();
            $table->string('genre')->nullable();
            $table->string('tempo')->nullable();
            $table->string('era')->nullable();
            $table->string('language')->nullable()->default('any');
            $table->string('occasion')->nullable();
            $table->json('similar_artists')->nullable();
            $table->json('similar_tracks')->nullable();
            $table->string('instruments')->nullable();
            $table->enum('popularity', ['any', 'mainstream', 'underground', 'obscure'])->default('any');
            $table->text('description')->nullable();

            $table->text('ai_prompt')->nullable();
            $table->json('result')->nullable();
            $table->string('result_source')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
