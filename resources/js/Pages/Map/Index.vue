<template>
  <Head title="Mapa GIS" />

  <!-- Layout minimalista para el mapa full-screen -->
  <div class="h-screen w-screen overflow-hidden relative font-sans text-gray-900 bg-gray-100">

    <!-- Top Navigation Bar (Flotante) -->
    <nav class="absolute top-4 left-4 right-4 z-[2000] flex justify-between items-center pointer-events-none">

      <!-- Logo / Title -->
      <div class="bg-white/90 backdrop-blur shadow-sm border border-gray-100 px-4 py-2.5 rounded-xl pointer-events-auto flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-bold text-lg shadow-inner">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd" /></svg>
        </div>
        <div>
          <h1 class="text-sm font-bold text-gray-800 leading-tight">Sistema GIS Web</h1>
          <p class="text-[10px] text-gray-500 font-medium tracking-wider uppercase">Plataforma Geográfica</p>
        </div>
      </div>

      <!-- User Menu & Actions -->
      <div class="flex items-center gap-3 pointer-events-auto">
        <!-- Switch Basemap -->
        <div class="bg-white/90 backdrop-blur shadow-sm border border-gray-100 p-1 rounded-xl flex">
          <button @click="mapStore.basemap = 'osm'" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors" :class="mapStore.basemap === 'osm' ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-800'">Mapa</button>
          <button @click="mapStore.basemap = 'satellite'" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors" :class="mapStore.basemap === 'satellite' ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-800'">Satélite</button>
          <button @click="mapStore.basemap = 'topo'" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors" :class="mapStore.basemap === 'topo' ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-800'">Topo</button>
        </div>

        <Link
          v-if="$page.props.auth.user"
          :href="route('dashboard')"
          class="bg-white/90 backdrop-blur shadow-sm border border-gray-100 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-blue-600 hover:bg-white transition-all flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          Admin Panel
        </Link>
        <Link
          v-else
          :href="route('login')"
          class="bg-blue-600 hover:bg-blue-700 text-white shadow-sm px-5 py-2.5 rounded-xl text-sm font-semibold transition-all"
        >
          Iniciar Sesión
        </Link>
      </div>
    </nav>

    <!-- Componente Principal del Mapa (Vue) -->
    <MapContainer @draw-created="handleDrawCreated" />

  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useMapStore } from '@/Stores/mapStore'
import MapContainer from '@/Components/Map/MapContainer.vue'

const mapStore = useMapStore()

function handleDrawCreated(payload) {
  const { geometry, layerType } = payload
  console.log('Nueva geometría dibujada:', layerType, geometry)
  // Aquí se abriría un modal para solicitar el nombre, descripción y layer_id a guardar.
  // Por ahora, solo lo logueamos en la consola de desarrollo.
}
</script>
