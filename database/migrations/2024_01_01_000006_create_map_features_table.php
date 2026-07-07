<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear la tabla principal con columnas estándar
        Schema::create('map_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['marker', 'polygon', 'line', 'heat_point'])->default('marker');
            $table->string('name', 255);
            $table->text('description')->nullable();
            // geometry se agrega con DB::statement abajo (PostGIS)
            $table->jsonb('properties')->nullable()->comment('Campos dinámicos definidos en feature_fields');
            $table->string('color', 7)->default('#3B82F6');
            $table->string('icon', 50)->nullable();
            $table->decimal('opacity', 3, 2)->default(0.80);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('layer_id');
            $table->index('type');
            $table->index('status');
            $table->index('category_id');
            $table->index('created_by');
            $table->index('created_at');
        });

        // Agregar columna geometry PostGIS (SRID 4326 = WGS84)
        DB::statement('ALTER TABLE map_features ADD COLUMN geometry geometry(Geometry, 4326)');

        // Índice espacial GiST sobre geometry (crítico para ST_Intersects, ST_DWithin, etc.)
        DB::statement('CREATE INDEX map_features_geometry_gist_idx ON map_features USING GIST (geometry)');

        // Índice GIN sobre JSONB para búsquedas rápidas en properties
        DB::statement('CREATE INDEX map_features_properties_gin_idx ON map_features USING GIN (properties)');
    }

    public function down(): void
    {
        Schema::dropIfExists('map_features');
    }
};
