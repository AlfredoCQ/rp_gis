import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useMapStore = defineStore('map', () => {
    // Estado del mapa
    const center = ref([-12.0566, -77.1181]) // Callao, Perú
    const zoom = ref(12)
    const basemap = ref('osm') // 'osm' | 'satellite' | 'topo'

    // Capas disponibles
    const layers = ref([])
    const activeLayers = ref(new Set())

    // Feature seleccionado
    const selectedFeature = ref(null)
    const isPopupOpen = ref(false)

    // Herramienta de dibujo activa
    const activeTool = ref(null) // 'marker' | 'polygon' | 'line' | null

    // Filtros activos
    const filters = ref({
        search: '',
        layer_id: null,
        type: null,
        status: 'active',
        category_id: null,
        date_from: null,
        date_to: null,
    })

    // BBox actual del mapa (se actualiza al mover/zoom)
    const currentBbox = ref(null) // [minLng, minLat, maxLng, maxLat]

    // Features cargadas en el mapa
    const features = ref([])
    const isLoading = ref(false)

    // ─── Computed ─────────────────────────────────────────────────────────────

    const activeLayersList = computed(() =>
        layers.value.filter(l => activeLayers.value.has(l.id))
    )

    const heatmapLayers = computed(() =>
        layers.value.filter(l => l.type === 'heatmap' && activeLayers.value.has(l.id))
    )

    // ─── Actions ──────────────────────────────────────────────────────────────

    async function loadLayers() {
        try {
            const { data } = await axios.get('/api/layers')
            layers.value = data.data ?? data
            // Activar capas visibles por defecto
            layers.value
                .filter(l => l.is_active)
                .forEach(l => activeLayers.value.add(l.id))
        } catch (e) {
            console.error('Error al cargar capas:', e)
        }
    }

    async function loadFeaturesByBbox() {
        if (!currentBbox.value) return

        isLoading.value = true
        try {
            const params = {
                bbox: currentBbox.value.join(','),
                ...Object.fromEntries(
                    Object.entries(filters.value).filter(([, v]) => v !== null && v !== '')
                ),
            }

            const { data } = await axios.get('/api/features', { params })
            features.value = data.features ?? []
        } catch (e) {
            console.error('Error al cargar features:', e)
        } finally {
            isLoading.value = false
        }
    }

    function selectFeature(feature) {
        selectedFeature.value = feature
        isPopupOpen.value = true
    }

    function closePopup() {
        selectedFeature.value = null
        isPopupOpen.value = false
    }

    function toggleLayer(layerId) {
        if (activeLayers.value.has(layerId)) {
            activeLayers.value.delete(layerId)
        } else {
            activeLayers.value.add(layerId)
        }
    }

    function setActiveTool(tool) {
        activeTool.value = activeTool.value === tool ? null : tool
    }

    function updateBbox(bbox) {
        currentBbox.value = bbox
        loadFeaturesByBbox()
    }

    function updateFilters(newFilters) {
        filters.value = { ...filters.value, ...newFilters }
        loadFeaturesByBbox()
    }

    async function deleteFeature(featureId) {
        await axios.delete(`/api/features/${featureId}`)
        features.value = features.value.filter(f => f.id !== featureId)
        closePopup()
    }

    return {
        // State
        center, zoom, basemap,
        layers, activeLayers,
        selectedFeature, isPopupOpen,
        activeTool, filters,
        currentBbox, features, isLoading,
        // Computed
        activeLayersList, heatmapLayers,
        // Actions
        loadLayers, loadFeaturesByBbox,
        selectFeature, closePopup,
        toggleLayer, setActiveTool,
        updateBbox, updateFilters, deleteFeature,
    }
})
