<template>
  <div class="relative w-full h-screen">

    <!-- Mapa Leaflet -->
    <div id="gis-map" ref="mapRef" class="absolute inset-0 z-0" />

    <!-- Panel de capas (izquierda) -->
    <LayerPanel
      :layers="mapStore.layers"
      :active-layers="mapStore.activeLayers"
      @toggle="mapStore.toggleLayer"
    />

    <!-- Herramientas de dibujo (top-right) -->
    <DrawingTools
      :active-tool="mapStore.activeTool"
      @tool-change="mapStore.setActiveTool"
    />

    <!-- Filtros laterales (derecha) -->
    <FilterSidebar
      :filters="mapStore.filters"
      :layers="mapStore.layers"
      @update="mapStore.updateFilters"
    />

    <!-- Popup de feature -->
    <FeaturePopup
      v-if="mapStore.isPopupOpen && mapStore.selectedFeature"
      :feature="mapStore.selectedFeature"
      @close="mapStore.closePopup"
      @deleted="onFeatureDeleted"
      @edit="onFeatureEdit"
    />

    <!-- Leyenda dinámica (bottom-left) -->
    <MapLegend :layers="mapStore.activeLayersList" />

    <!-- Loading indicator -->
    <div
      v-if="mapStore.isLoading"
      class="absolute top-4 left-1/2 -translate-x-1/2 z-50 bg-white/90 backdrop-blur px-4 py-2 rounded-full shadow text-sm font-medium text-gray-700"
    >
      <span class="animate-pulse">Cargando datos del mapa...</span>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet-draw/dist/leaflet.draw.css'
import 'leaflet-draw'
import 'leaflet.heat'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
import 'leaflet.markercluster'

import { useMapStore } from '@/Stores/mapStore'
import LayerPanel from '@/Components/Map/LayerPanel.vue'
import DrawingTools from '@/Components/Map/DrawingTools.vue'
import FilterSidebar from '@/Components/Map/FilterSidebar.vue'
import FeaturePopup from '@/Components/Map/FeaturePopup.vue'
import MapLegend from '@/Components/Map/MapLegend.vue'

import axios from 'axios'

const mapStore = useMapStore()
const mapRef = ref(null)
let map = null
let drawnItems = null
let clusterGroup = null
let heatLayer = null

// ─── Mapas base ──────────────────────────────────────────────────────────────

const basemaps = {
  osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
    maxZoom: 22,
  }),
  satellite: L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { attribution: 'Tiles © Esri', maxZoom: 20 }
  ),
  topo: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenTopoMap',
    maxZoom: 17,
  }),
}

// ─── Inicialización ──────────────────────────────────────────────────────────

onMounted(async () => {
  // Inicializar mapa Leaflet
  map = L.map(mapRef.value, {
    center: mapStore.center,
    zoom: mapStore.zoom,
    zoomControl: false,
  })

  // Control de zoom (posición top-left)
  L.control.zoom({ position: 'topleft' }).addTo(map)

  // Mapa base por defecto
  basemaps.osm.addTo(map)

  // Grupo para features dibujadas por el usuario
  drawnItems = new L.FeatureGroup()
  map.addLayer(drawnItems)

  // Cluster de marcadores
  clusterGroup = L.markerClusterGroup({ chunkedLoading: true })
  map.addLayer(clusterGroup)

  // Control de dibujo (Leaflet.draw)
  const drawControl = new L.Control.Draw({
    draw: {
      marker:    true,
      polygon:   { shapeOptions: { color: '#3B82F6' } },
      polyline:  { shapeOptions: { color: '#10B981' } },
      rectangle: false,
      circle:    false,
      circlemarker: false,
    },
    edit: { featureGroup: drawnItems },
  })
  map.addControl(drawControl)

  // Evento: nueva geometría dibujada
  map.on(L.Draw.Event.CREATED, async (e) => {
    const layer = e.layer
    const geoJson = layer.toGeoJSON()

    // Abrir formulario de creación con la geometría lista
    emit('draw-created', { geometry: geoJson.geometry, layerType: e.layerType })
    drawnItems.addLayer(layer)
  })

  // Evento: mapa se mueve → recargar por bbox
  map.on('moveend', onMapMoveEnd)
  map.on('zoomend', onMapMoveEnd)

  // Cargar capas
  await mapStore.loadLayers()

  // Primera carga de features
  onMapMoveEnd()
})

onUnmounted(() => {
  map?.remove()
})

// ─── Métodos ─────────────────────────────────────────────────────────────────

function onMapMoveEnd() {
  const bounds = map.getBounds()
  const bbox = [
    bounds.getWest(),
    bounds.getSouth(),
    bounds.getEast(),
    bounds.getNorth(),
  ]
  mapStore.updateBbox(bbox)
}

// Reactivo: cuando cambien features, re-renderizar en el mapa
watch(() => mapStore.features, renderFeatures, { deep: true })

function renderFeatures(features) {
  clusterGroup.clearLayers()

  features.forEach(feature => {
    const layer = geoJsonToLeafletLayer(feature)
    if (layer) {
      layer.on('click', () => mapStore.selectFeature(feature))
      if (feature.geometry?.type === 'Point') {
        clusterGroup.addLayer(layer)
      } else {
        layer.addTo(map)
      }
    }
  })
}

function geoJsonToLeafletLayer(feature) {
  const { geometry, properties } = feature
  if (!geometry) return null

  const color = properties?.color || '#3B82F6'
  const fillOpacity = properties?.fillOpacity !== undefined ? parseFloat(properties.fillOpacity) : 0.4
  const opacity = properties?.opacity !== undefined ? parseFloat(properties.opacity) : 0.8
  const weight = properties?.weight !== undefined ? parseFloat(properties.weight) : 2

  const options = {
    color,
    fillColor: color,
    fillOpacity,
    opacity,
    weight,
  }

  if (geometry.type === 'Point') {
    const [lng, lat] = geometry.coordinates
    const is24h = properties?.horas_atencion === '24 HORAS' || color === '#EF4444'
    const size = is24h ? 16 : 12
    const html = is24h
      ? `<div style="background:${color};width:${size}px;height:${size}px;border-radius:50%;border:2px solid white;box-shadow: 0 0 8px rgba(239,68,68,0.6); animation: pulse-red 2s infinite;"></div>`
      : `<div style="background:${color};width:${size}px;height:${size}px;border-radius:50%;border:2px solid white;box-shadow: 0 1px 3px rgba(0,0,0,0.3);"></div>`
    
    const icon = L.divIcon({
      html,
      className: '',
      iconSize: [size, size],
      iconAnchor: [size / 2, size / 2],
    })
    return L.marker([lat, lng], { icon })
  }

  return L.geoJSON({ type: 'Feature', geometry, properties }, {
    style: () => options,
  })
}

// Reactivo: actualizar heatmap layers
watch(() => mapStore.heatmapLayers, updateHeatmaps, { deep: true })

async function updateHeatmaps(heatLayers) {
  if (heatLayer) {
    map.removeLayer(heatLayer)
    heatLayer = null
  }

  for (const layer of heatLayers) {
    const { data } = await axios.get(`/api/heatmap/${layer.id}`)
    if (data.points?.length) {
      heatLayer = L.heatLayer(data.points, {
        radius: 25,
        blur: 15,
        maxZoom: 17,
      }).addTo(map)
    }
  }
}

// Cambiar mapa base
function setBasemap(name) {
  Object.values(basemaps).forEach(b => {
    if (map.hasLayer(b)) map.removeLayer(b)
  })
  basemaps[name]?.addTo(map)
  mapStore.basemap = name
}

// Eventos desde hijos
const emit = defineEmits(['draw-created'])

function onFeatureDeleted() {
  onMapMoveEnd()
}

function onFeatureEdit(feature) {
  // Navegar a edición (Inertia)
}
</script>

<style scoped>
#gis-map :deep(.leaflet-control-zoom) {
  margin-top: 1rem;
  margin-left: 1rem;
}

@keyframes pulse-red {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
  }
}
</style>
