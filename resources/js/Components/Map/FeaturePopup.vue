<template>
  <div class="absolute inset-0 bg-black/10 backdrop-blur-sm z-[2000] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-full">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50" :style="headerStyle">
        <h2 class="text-lg font-semibold text-gray-800 truncate flex items-center gap-2">
          <span class="w-3 h-3 rounded-full inline-block" :style="{ backgroundColor: featureColor }"></span>
          {{ feature.name || 'Sin Título' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-700 bg-white/50 rounded-full p-1 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-5 overflow-y-auto custom-scrollbar flex-1">
        <div class="space-y-4">
          <!-- Descripción Básica -->
          <div v-if="feature.description">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Descripción</h4>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ feature.description }}</p>
          </div>

          <!-- Detalles Geométricos -->
          <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
            <div>
               <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipo</h4>
               <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 capitalize">
                 {{ feature.type }}
               </span>
            </div>
            <div>
               <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Estado</h4>
               <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="statusClasses">
                 {{ feature.status }}
               </span>
            </div>
          </div>

          <!-- Área para polígonos -->
           <div v-if="feature.area_m2" class="pt-2 border-t border-gray-100">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Área Calculada</h4>
            <p class="text-sm font-medium text-gray-800">
              {{ (feature.area_m2).toLocaleString('es-CO', { maximumFractionDigits: 2 }) }} m²
              <span class="text-xs text-gray-400 ml-1 font-normal">({{ (feature.area_m2 / 10000).toLocaleString('es-CO', { maximumFractionDigits: 4 }) }} ha)</span>
            </p>
          </div>

          <!-- Propiedades Dinámicas (JSONB) -->
          <div v-if="hasProperties" class="pt-4 border-t border-gray-100">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Datos Adicionales</h4>
            <dl class="space-y-3">
              <div v-for="(value, key) in displayProperties" :key="key" class="grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500 truncate" :title="key">{{ formatKey(key) }}</dt>
                <dd class="text-sm text-gray-900 col-span-2 break-words font-medium">{{ value }}</dd>
              </div>
            </dl>
          </div>

        </div>
      </div>

      <!-- Actions Footer -->
      <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
        <button
           @click="$emit('edit', feature)"
           class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
          Editar
        </button>
        <button
           @click="confirmDelete"
           class="px-4 py-2 text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          Eliminar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  feature: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'edit', 'deleted'])

const featureColor = computed(() => props.feature.color || '#3B82F6')

const headerStyle = computed(() => {
  // Un sutil gradiente arriba usando el color principal del feature
  return {
    borderTop: `4px solid ${featureColor.value}`,
  }
})

const statusClasses = computed(() => {
  const status = props.feature.status
  if (status === 'active') return 'bg-emerald-100 text-emerald-800'
  if (status === 'inactive') return 'bg-gray-200 text-gray-800'
  if (status === 'draft') return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-800'
})

// Lógica de propiedades (JSONB)
const excludeKeys = ['id', 'layer_id', 'type', 'name', 'description', 'color', 'icon', 'status', 'category_id', 'created_at', 'distance_m']

const displayProperties = computed(() => {
  if (!props.feature.properties) return {}

  return Object.entries(props.feature.properties)
    .filter(([key]) => !excludeKeys.includes(key))
    .reduce((obj, [key, val]) => {
      obj[key] = val
      return obj
    }, {})
})

const hasProperties = computed(() => Object.keys(displayProperties.value).length > 0)

function formatKey(key) {
  // Convertir camelCase o snake_case a algo más legible
  const result = key.replace(/([A-Z])/g, " $1").replace(/_/g, ' ')
  return result.charAt(0).toUpperCase() + result.slice(1)
}

function confirmDelete() {
  if (confirm(`¿Estás seguro de eliminar "${props.feature.name}"?`)) {
    emit('deleted', props.feature.id)
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 20px;
}
</style>
