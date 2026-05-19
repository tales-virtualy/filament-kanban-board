<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = config('kanban.tables');

        if (!Schema::hasTable($tables['tags'])) {
            Schema::create($tables['tags'], function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->string('type')->nullable();
                $table->string('badge_color')->nullable();
                $table->string('text_color')->nullable();
                $table->integer('order_column')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable($tables['taggables'])) {
            Schema::create($tables['taggables'], function (Blueprint $table) use ($tables) {
                $table->foreignId('tag_id')->constrained($tables['tags'])->cascadeOnDelete();
                $table->morphs('taggable');

                $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
            });
        }
    }

    public function down(): void
    {
        $tables = config('kanban.tables');

        Schema::dropIfExists($tables['taggables']);
        Schema::dropIfExists($tables['tags']);
    }
};
