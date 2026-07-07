<?php

namespace Database\Seeders;

use App\Models\Layer;
use App\Models\MapFeature;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthCentersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener usuario administrador para asignar la creación
        $admin = User::where('email', 'admin@gis.local')->first();
        $adminId = $admin ? $admin->id : 1;

        // 2. Crear las capas si no existen
        // Capa de límites distritales (polígonos)
        $districtLayer = Layer::updateOrCreate(
            ['slug' => 'limites-distritales'],
            [
                'name'        => 'Límites Distritales',
                'description' => 'Límites de los distritos de la Provincia Constitucional del Callao',
                'type'        => 'polygon',
                'color'       => '#6366F1',
                'opacity'     => 0.80,
                'is_active'   => true,
                'is_public'   => true,
                'sort_order'  => 1,
                'created_by'  => $adminId,
            ]
        );

        // Capa de establecimientos de salud (marcadores)
        $healthLayer = Layer::updateOrCreate(
            ['slug' => 'establecimientos-salud'],
            [
                'name'        => 'Establecimientos de Salud',
                'description' => 'Centros de salud, hospitales y postas de la DIRESA y EsSalud en el Callao',
                'type'        => 'marker',
                'color'       => '#3B82F6',
                'opacity'     => 0.90,
                'is_active'   => true,
                'is_public'   => true,
                'sort_order'  => 2,
                'created_by'  => $adminId,
            ]
        );

        // 3. Cargar los datos geocodificados
        $jsonPath = base_path('scratch/callao_geocoded.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("No se encontró el archivo callao_geocoded.json en " . $jsonPath);
            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        // Colores distintivos para los distritos en el mapa
        $districtColors = [
            'BELLAVISTA' => '#EC4899', // Rosa
            'CALLAO' => '#3B82F6', // Azul
            'CARMEN DE LA LEGUA - REYNOSO' => '#10B981', // Verde
            'LA PERLA' => '#F59E0B', // Ámbar
            'LA PUNTA' => '#8B5CF6', // Púrpura
            'MI PERU' => '#06B6D4', // Cian
            'VENTANILLA' => '#F97316', // Naranja
        ];

        // Centroides de distritos como fallback en caso de no poder geocodificar la dirección exacta
        $districtCentroids = [
            'BELLAVISTA' => ['lat' => -12.0667, 'lon' => -77.1083],
            'CALLAO' => ['lat' => -12.0566, 'lon' => -77.1181],
            'CARMEN DE LA LEGUA - REYNOSO' => ['lat' => -12.0435, 'lon' => -77.0850],
            'LA PERLA' => ['lat' => -12.0708, 'lon' => -77.0983],
            'LA PUNTA' => ['lat' => -12.0722, 'lon' => -77.1556],
            'MI PERU' => ['lat' => -11.8500, 'lon' => -77.1167],
            'VENTANILLA' => ['lat' => -11.8750, 'lon' => -77.1250],
        ];

        // 4. Insertar límites distritales (polígonos)
        DB::transaction(function () use ($data, $districtLayer, $adminId, $districtColors) {
            // Limpiar límites anteriores si existen para evitar duplicados
            MapFeature::where('layer_id', $districtLayer->id)->forceDelete();

            foreach ($data['districts'] as $name => $geometry) {
                if (!$geometry) continue;

                $color = $districtColors[$name] ?? '#6366F1';
                
                $feature = new MapFeature();
                $feature->layer_id = $districtLayer->id;
                $feature->type = 'polygon';
                $feature->name = 'Límite de ' . ucwords(strtolower($name));
                $feature->description = 'Límite geográfico oficial del distrito de ' . ucwords(strtolower($name));
                $feature->color = $color;
                $feature->opacity = 0.70;
                $feature->status = 'active';
                $feature->properties = [
                    'distrito' => $name,
                    'fillOpacity' => 0.05, // Muy transparente para no tapar los marcadores
                    'weight' => 2.5,
                    'color' => $color
                ];
                $feature->created_by = $adminId;
                $feature->save();

                // Guardar la geometría
                DB::statement(
                    'UPDATE map_features SET geometry = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?',
                    [json_encode($geometry), $feature->id]
                );
            }
        });
        $this->command->info('✅ Límites distritales sembrados con éxito.');

        // 5. Insertar establecimientos de salud (marcadores)
        DB::transaction(function () use ($data, $healthLayer, $adminId, $districtCentroids) {
            // Limpiar marcadores anteriores si existen para evitar duplicados
            MapFeature::where('layer_id', $healthLayer->id)->forceDelete();

            $seededCount = 0;
            foreach ($data['centers'] as $center) {
                $lat = $center['lat'];
                $lon = $center['lon'];

                if ($lat === null || $lon === null) {
                    // Fallback al centroide del distrito con un pequeño offset aleatorio
                    $district = strtoupper(trim($center['district']));
                    if (isset($districtCentroids[$district])) {
                        // Generar un pequeño offset aleatorio entre -0.003 y +0.003 grados (~300 metros)
                        // para que no se superpongan exactamente en el mismo punto del mapa
                        $offsetLat = (mt_rand(-3000, 3000) / 1000000);
                        $offsetLon = (mt_rand(-3000, 3000) / 1000000);
                        $lat = $districtCentroids[$district]['lat'] + $offsetLat;
                        $lon = $districtCentroids[$district]['lon'] + $offsetLon;
                        $this->command->info("📍 Coordenadas estimadas para: " . $center['name'] . " en distrito " . $district);
                    } else {
                        $this->command->warn("Saltando establecimiento sin coordenadas ni distrito conocido: " . $center['name']);
                        continue;
                    }
                }

                $is24Hours = (strpos(strtoupper($center['hours']), '24 HORAS') !== false);
                
                // Color rojo para 24 horas (atención emergencias), azul para otros horarios
                $color = $is24Hours ? '#EF4444' : '#3B82F6';
                
                $feature = new MapFeature();
                $feature->layer_id = $healthLayer->id;
                $feature->type = 'marker';
                $feature->name = $center['name'];
                $feature->description = $is24Hours 
                    ? 'Establecimiento de 24 HORAS con atención de Emergencias y Ambulancias.' 
                    : 'Establecimiento de salud con atención regular.';
                $feature->color = $color;
                $feature->opacity = 0.90;
                $feature->status = 'active';
                $feature->properties = [
                    'direccion' => $center['address'],
                    'horas_atencion' => $center['hours'],
                    'entidad' => $center['entity'],
                    'distrito' => $center['district'],
                    'color' => $color
                ];
                $feature->created_by = $adminId;
                $feature->save();

                // Guardar la geometría (Punto)
                $pointGeom = [
                    'type' => 'Point',
                    'coordinates' => [(float)$lon, (float)$lat]
                ];

                DB::statement(
                    'UPDATE map_features SET geometry = ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) WHERE id = ?',
                    [json_encode($pointGeom), $feature->id]
                );

                $seededCount++;
            }
            $this->command->info("✅ {$seededCount} establecimientos de salud sembrados con éxito.");
        });
    }
}
