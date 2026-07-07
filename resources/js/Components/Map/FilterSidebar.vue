<template>
  <div class="fixed inset-y-0 right-0 w-80 bg-white shadow-[-4px_0_15px_rgba(0,0,0,0.05)] border-l border-gray-100 z-[1001] flex flex-col transition-transform duration-300" :class="{ 'translate-x-full': !isOpen }">

    <!-- Header -->
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
      <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
        Filtros Espaciales
      </h2>
      <button @click="isOpen = false" class="text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-5 space-y-6 custom-scrollbar">

      <!-- Búsqueda texto libre -->
      <div>
        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Búsqueda general</label>
        <div class="relative">
          <input
            v-model="localFilters.search"
            type="text"
            placeholder="Buscar por nombre o descripción..."
            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            @input="debouncedUpdate"
          >
          <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
      </div>

      <!-- Filtro por Estado -->
      <div>
        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Estado</label>
        <div class="flex bg-gray-100 rounded-lg p-1">
          <button
            v-for="status in [{val: '', label: 'Todos'}, {val: 'active', label: 'Activos'}, {val: 'inactive', label: 'Inactivos'}]"
            :key="status.val"
            @click="localFilters.status = status.val; applyFilters()"
            class="flex-1 py-1.5 text-xs font-medium rounded-md transition-colors"
            :class="localFilters.status === status.val ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
          >
            {{ status.label }}
          </button>
        </div>
      </div>

      <!-- Filtro por Tipo Geométrico -->
      <div>
        <label class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Tipo Geométrico</label>
        <select
          v-model="localFilters.type"
          @change="applyFilters"
          class="w-full py-2 px-3 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
        >
          <option :value="null">Todos los tipos</option>
          <option value="marker">Marcadores</option>
          <option value="polygon">Polígonos</option>
          <option value="line">Líneas</option>
        </select>
      </div>

    </div>
  </div>

  <!-- Botón Flotante para abrir Filtros -->
  <button
    v-if="!isOpen"
    @click="isOpen = true"
    class="absolute right-4 top-4 z-[999] bg-white px-4 py-2 rounded-xl shadow-md border border-gray-100 text-gray-700 hover:text-blue-600 hover:shadow-lg flex items-center gap-2 text-sm font-medium transition-all"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
    Filtros
    <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-blue-500"></span>
  </button>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  filters: {
    type: Object,
    required: true
  },
  layers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update'])

const isOpen = ref(false)
const localFilters = ref({ ...props.filters })

let debounceTimer = null

const hasActiveFilters = computed(() => {
  return localFilters.value.search !== '' ||
         localFilters.value.type !== null ||
         localFilters.value.status !== 'active'
})

function debouncedUpdate() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    applyFilters()
  }, 400)
}

function applyFilters() {
  emit('update', { ...localFilters.value })
}

watch(() => props.filters, (newVal) => {
  localFilters.value = { ...newVal }
}, { deep: true })
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
