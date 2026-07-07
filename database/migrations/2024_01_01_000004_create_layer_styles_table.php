<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layer_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained()->cascadeOnDelete();
            $table->string('field_name', 100);       // campo del JSONB a evaluar
            $table->string('operator', 20)->default('eq'); // eq, neq, gt, gte, lt, lte, contains, in
            $table->string('value', 255);            // valor de comparación
            $table->string('fill_color', 7)->default('#3B82F6');
            $table->string('stroke_color', 7)->default('#1D4ED8');
            $table->decimal('opacity', 3, 2)->default(0.80);
            $table->string('icon', 50)->nullable();
            $table->string('label', 100)->nullable(); // texto para la leyenda
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['layer_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layer_styles');
    }
};
