<template>
  <div class="fixed bottom-6 left-4 z-[999] bg-white/95 backdrop-blur shadow-lg rounded-xl overflow-hidden border border-gray-100 flex flex-col w-56 transition-all duration-300">
    <div class="px-4 py-2 bg-gray-50/80 border-b border-gray-100 flex justify-between items-center cursor-pointer" @click="isCollapsed = !isCollapsed">
      <h3 class="font-semibold text-gray-800 text-xs tracking-wide uppercase">Leyenda</h3>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="isCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
    </div>

    <div v-show="!isCollapsed" class="p-3 max-h-60 overflow-y-auto custom-scrollbar">
      <div v-if="layers.length === 0" class="text-xs text-gray-500 text-center py-2">
        Activa capas para ver la leyenda
      </div>

      <div v-for="layer in layers" :key="layer.id" class="mb-3 last:mb-0">
        <h4 class="text-xs font-semibold text-gray-700 mb-1.5">{{ layer.name }}</h4>
        
        <!-- Caso Especial: Establecimientos de Salud con Leyenda Detallada -->
        <template v-if="layer.slug === 'establecimientos-salud'">
          <div class="space-y-1.5">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full border border-white shadow-sm bg-[#EF4444] animate-pulse"></div>
              <span class="text-[11px] text-gray-700 font-medium">Emergencias y Ambulancias (24h)</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-2.5 h-2.5 rounded-full border border-white shadow-sm bg-[#3B82F6]"></div>
              <span class="text-[11px] text-gray-600">Atención Regular (6h / 12h)</span>
            </div>
          </div>
        </template>
        
        <!-- Caso Especial: Límites Distritales -->
        <template v-else-if="layer.slug === 'limites-distritales'">
          <div class="flex items-center gap-2">
            <div class="w-4 h-2.5 border border-indigo-400 bg-indigo-50/20 rounded" style="border-style: solid; border-width: 1.5px;"></div>
            <span class="text-[11px] text-gray-600">Límite Distrital</span>
          </div>
        </template>
        
        <!-- Leyenda por Defecto -->
        <template v-else>
          <div class="flex items-center gap-2">
             <div v-if="layer.type === 'marker'" class="w-3 h-3 rounded-full border border-white shadow-sm" :style="{ backgroundColor: layer.color }"></div>
             <div v-else-if="layer.type === 'polygon'" class="w-3 h-3 border border-gray-400 opacity-80" :style="{ backgroundColor: layer.color }"></div>
             <div v-else-if="layer.type === 'line'" class="w-4 h-0.5" :style="{ backgroundColor: layer.color }"></div>
             <div v-else-if="layer.type === 'heatmap'" class="w-4 h-4 rounded bg-gradient-to-r from-blue-500 via-yellow-500 to-red-500"></div>
             <span class="text-xs text-gray-600">Por defecto</span>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  layers: {
    type: Array,
    default: () => []
  }
})

const isCollapsed = ref(false)
</script>
