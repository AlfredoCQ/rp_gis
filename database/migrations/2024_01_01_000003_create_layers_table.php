<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['marker', 'polygon', 'line', 'heatmap'])->default('marker');
            $table->string('color', 7)->default('#3B82F6');
            $table->string('icon', 50)->nullable();
            $table->decimal('opacity', 3, 2)->default(0.80);
            $table->unsignedTinyInteger('min_zoom')->default(1);
            $table->unsignedTinyInteger('max_zoom')->default(22);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layers');
    }
};
