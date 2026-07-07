<template>
  <div class="absolute left-4 top-20 z-[1000] w-64 bg-white/95 backdrop-blur shadow-lg rounded-xl overflow-hidden border border-gray-100 flex flex-col max-h-[calc(100vh-120px)] transition-all duration-300" :class="{ '-translate-x-full opacity-0': !isOpen }">
    <!-- Cabecera -->
    <div class="px-4 py-3 bg-gray-50/80 border-b border-gray-100 flex justify-between items-center">
      <h3 class="font-semibold text-gray-800 text-sm tracking-wide uppercase">Capas del Mapa</h3>
      <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>

    <!-- Lista de capas -->
    <div class="p-3 overflow-y-auto flex-1 space-y-1 custom-scrollbar">
      <div v-if="layers.length === 0" class="text-xs text-gray-500 text-center py-4">
        No hay capas disponibles
      </div>

      <label
        v-for="layer in layers"
        :key="layer.id"
        class="flex items-center gap-3 p-2 hover:bg-blue-50/50 rounded-lg cursor-pointer transition-colors group"
      >
        <div class="relative flex items-center justify-center">
          <input
            type="checkbox"
            :checked="activeLayers.has(layer.id)"
            @change="$emit('toggle', layer.id)"
            class="peer h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition-shadow"
          />
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <!-- Icono indicativo del tipo -->
            <div class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: layer.color || '#3B82F6' }"></div>
            <span class="text-sm font-medium text-gray-700 truncate group-hover:text-blue-700 transition-colors">
              {{ layer.name }}
            </span>
          </div>
          <p class="text-[10px] text-gray-400 truncate mt-0.5 ml-4">
            {{ layer.type === 'heat_point' ? 'Mapa de calor' : (layer.type === 'polygon' ? 'Polígonos' : (layer.type === 'line' ? 'Líneas' : 'Marcadores')) }}
          </p>
        </div>
      </label>
    </div>
  </div>

  <!-- Botón para abrir el panel si está cerrado -->
  <button
    v-if="!isOpen"
    @click="isOpen = true"
    class="absolute left-4 top-20 z-[999] bg-white p-2 rounded-xl shadow-md border border-gray-100 text-gray-600 hover:text-blue-600 hover:shadow-lg transition-all"
    title="Capas"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
  </button>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  layers: {
    type: Array,
    default: () => []
  },
  activeLayers: {
    type: Set,
    required: true
  }
})

defineEmits(['toggle'])

const isOpen = ref(true)
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 20px;
}
</style>
