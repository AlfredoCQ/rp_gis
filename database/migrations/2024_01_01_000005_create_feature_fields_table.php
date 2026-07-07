<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);              // nombre interno (snake_case)
            $table->string('label', 150);             // etiqueta visible al usuario
            $table->enum('type', [
                'text', 'number', 'date', 'datetime',
                'list', 'boolean', 'file', 'image', 'coordinate'
            ])->default('text');
            $table->jsonb('options')->nullable();     // para tipo 'list': [{value, label}]
            $table->string('placeholder', 255)->nullable();
            $table->string('default_value', 255)->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_visible_in_popup')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['layer_id', 'sort_order']);
            $table->unique(['layer_id', 'name']);    // nombre único por capa
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_fields');
    }
};
