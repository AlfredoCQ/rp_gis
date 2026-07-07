<template>
  <div class="absolute right-4 top-20 z-[1000] flex flex-col gap-2">
    <!-- Marcador -->
    <button
      @click="toggleTool('marker')"
      class="w-10 h-10 bg-white rounded-xl shadow-md border flex items-center justify-center transition-all"
      :class="activeTool === 'marker' ? 'border-blue-500 text-blue-600 bg-blue-50 shadow-blue-500/20' : 'border-gray-100 text-gray-600 hover:text-blue-600 hover:bg-gray-50'"
      title="Dibujar Marcador"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
    </button>

    <!-- Línea -->
    <button
      @click="toggleTool('polyline')"
      class="w-10 h-10 bg-white rounded-xl shadow-md border flex items-center justify-center transition-all"
      :class="activeTool === 'polyline' ? 'border-emerald-500 text-emerald-600 bg-emerald-50 shadow-emerald-500/20' : 'border-gray-100 text-gray-600 hover:text-emerald-600 hover:bg-gray-50'"
      title="Dibujar Línea"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
    </button>

    <!-- Polígono -->
    <button
      @click="toggleTool('polygon')"
      class="w-10 h-10 bg-white rounded-xl shadow-md border flex items-center justify-center transition-all"
      :class="activeTool === 'polygon' ? 'border-purple-500 text-purple-600 bg-purple-50 shadow-purple-500/20' : 'border-gray-100 text-gray-600 hover:text-purple-600 hover:bg-gray-50'"
      title="Dibujar Polígono"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" /></svg>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  activeTool: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['tool-change'])

function toggleTool(tool) {
  emit('tool-change', tool)
  // Nota: La lógica real de activar el modo de dibujo de Leaflet.Draw se manejará desde el padre
  // llamando a las clases de control interno de Leaflet
  const drawControlClasses = {
    'marker': '.leaflet-draw-draw-marker',
    'polyline': '.leaflet-draw-draw-polyline',
    'polygon': '.leaflet-draw-draw-polygon'
  }

  // Pequeño hack para activar la herramienta programáticamente simulando el click en la UI invisible de Leaflet.Draw
  setTimeout(() => {
    const el = document.querySelector(drawControlClasses[tool])
    if (el) el.click()
  }, 10)
}
</script>

<style scoped>
/* Ocultar la barra nativa de Leaflet.Draw para usar la nuestra */
:global(.leaflet-draw-toolbar.leaflet-bar.leaflet-draw-toolbar-top) {
  display: none !important;
}
</style>
