<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('author')->nullable();
            $table->string('source');
            $table->string('source_name');
            $table->string('external_id')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['author', 'source', 'source_name', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
