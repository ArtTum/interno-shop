<script setup>
import { onMounted, onBeforeUnmount, ref, watch, computed } from 'vue'
import * as echarts from 'echarts/core'
import { MapChart } from 'echarts/charts'
import { TooltipComponent, VisualMapComponent, TitleComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

import worldJson from './world.json'

import isoCountries from 'i18n-iso-countries'
import enLocale from 'i18n-iso-countries/langs/en.json'
const store = useStore();
isoCountries.registerLocale(enLocale)

import axios from 'axios'
import {useStore} from "vuex";

echarts.use([MapChart, TooltipComponent, VisualMapComponent, TitleComponent, CanvasRenderer])

// ── Props / Emits
const props = defineProps({
    metric: { type: String, default: 'sales' },
    pageData: { type: Object, required: true },
    params: { type: Object, required: true },
})
const emit = defineEmits(['region-click', 'country-click', 'error', 'regions-by-country'])

// ── State
const chartEl = ref(null)
let chart = null
const currentLevel = ref('world')
const currentCountryName = ref('')
const loading = ref(false)
const errorText = ref('')
let resizeObserver = null

// ── Helpers
const metricKey = computed(() =>
    ['sales', 'orders', 'aov'].includes(props.metric) ? props.metric : 'sales'
)
const labelForMetric = () =>
    metricKey.value === 'orders' ? 'Orders' : (metricKey.value === 'aov' ? 'AOV' : 'Sales')
const formatCurrency = n =>
    (typeof n === 'number' && !Number.isNaN(n))
        ? new Intl.NumberFormat('en-US', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(n)
        : '—'
const formatNumber = n =>
    (typeof n === 'number' && !Number.isNaN(n)) ? n.toLocaleString() : '—'

// Names present in your world.json
const WORLD_NAMES = new Set((worldJson.features || []).map(f => f?.properties?.name).filter(Boolean))

function iso2ToName(iso2) {
    if (!iso2) return null
    const aliasMap = {
        US: ['United States', 'United States of America'],
        RU: ['Russia', 'Russian Federation'],
        CZ: ['Czech Republic', 'Czechia'],
        CD: ['Democratic Republic of the Congo', 'Congo (Kinshasa)'],
        CG: ['Republic of the Congo', 'Congo (Brazzaville)'],
        CI: ["Cote d'Ivoire", "Côte d’Ivoire", 'Ivory Coast'],
        SY: ['Syria', 'Syrian Arab Republic'],
        KR: ['South Korea', 'Republic of Korea'],
        KP: ['North Korea', "Dem. People's Republic of Korea"],
        AM: ['Armenia'],
        GB: ['United Kingdom', 'UK'],
    }
    const libName = isoCountries.getName(iso2, 'en') || null
    const candidates = [...(aliasMap[iso2] || []), libName].filter(Boolean)
    for (const c of candidates) if (WORLD_NAMES.has(c)) return c
    return null
}

function nameToIso2(name) {
    if (!name) return null
    let a2 = isoCountries.getAlpha2Code(name, 'en')
    if (a2) return a2
    const rev = {
        'United States of America': 'US',
        'United States': 'US',
        'Russia': 'RU',
        'Russian Federation': 'RU',
        'Czechia': 'CZ',
        'Czech Republic': 'CZ',
        "Cote d'Ivoire": 'CI',
        'Côte d’Ivoire': 'CI',
        'Democratic Republic of the Congo': 'CD',
        'Congo (Kinshasa)': 'CD',
        'Republic of the Congo': 'CG',
        'Congo (Brazzaville)': 'CG',
        'Syria': 'SY',
        'Syrian Arab Republic': 'SY',
        'South Korea': 'KR',
        'Republic of Korea': 'KR',
        'North Korea': 'KP',
        "Dem. People's Republic of Korea": 'KP',
        'UK': 'GB',
        'United Kingdom': 'GB',
        'Kosovo': 'XK',
    }
    return rev[name] || null
}

function pickRegionId(iso2, props = {}) {
    const candidates = [
        props.iso_3166_2, props.ISO_3166_2, props.iso3166_2,
        props.hasc, props.HASC, props.HASC_1,
        props.code, props.CODE, props.CODE_1,
        props.id, props.ID, props.ID_1
    ].filter(Boolean)

    let raw = candidates[0] || ''
    if (!raw) return null

    raw = String(raw).trim().replace(/\./g, '-').toUpperCase()
    if (!raw.includes('-') && raw.length <= 3) raw = `${iso2.toUpperCase()}-${raw}`
    return raw
}

function buildWorldSeriesData() {
    const src = props.pageData?.data?.preparingData || {}
    const items = []
    for (const [iso2, s] of Object.entries(src)) {
        const name = iso2ToName(iso2)
        if (!name) continue
        const value = metricKey.value === 'aov'
            ? Number(s.aov || 0)
            : (metricKey.value === 'orders' ? Number(s.orders || 0) : Number(s.sales || 0))
        if (value > 0) items.push({ name, value, iso2 })
    }
    return items
}

function countryTooltipByName(name) {
    const src = props.pageData?.data?.preparingData || {}
    const hit = Object.entries(src).find(([iso2]) => iso2ToName(iso2) === name)
    if (!hit) return 'No data'
    const [, s] = hit
    const main = metricKey.value === 'aov' ? formatCurrency(s.aov) : formatNumber(s[metricKey.value])
    return `<div class="tw-text-sm">
    <div><b>${s.name}</b></div>
    <div>${labelForMetric()}: ${main}</div>
    <hr class="tw-my-1 tw-border-gray-200"/>
    <div>Sales: ${formatCurrency(s.sales)}</div>
    <div>Orders: ${formatNumber(s.orders)}</div>
    <div>AOV: ${formatCurrency(s.aov)}</div>
  </div>`
}

// ── Renderers
async function renderWorld() {
    echarts.registerMap('world', worldJson)

    const data = buildWorldSeriesData()
    const values = data.map(d => +d.value || 0)
    const maxVal = Math.max(...values, 10)

    const option = {
        title: { text: `${labelForMetric()} by Country`, left: 'center' },
        tooltip: {
            trigger: 'item',
            confine: true,
            formatter: (p) => p.data ? countryTooltipByName(p.name) : 'No data',
        },
        visualMap: {
            min: 1,
            max: maxVal,
            calculable: true,
            orient: 'horizontal',
            left: 'center',
            bottom: 10,
            inRange: { color: ['#93c5fd', '#1d4ed8'] },
            outOfRange: { color: '#e5e7eb' }
        },
        series: [{
            type: 'map',
            map: 'world',
            roam: true,
            layoutCenter: ['50%', '55%'],
            zoom: 1.4,
            layoutSize: '100%',
            scaleLimit: { min: 0.8, max: 10 },
            itemStyle: { borderColor: '#9aa4b2', borderWidth: 0.6, areaColor: '#e5e7eb' }, // base white
            emphasis: { itemStyle: { borderColor: '#475569', borderWidth: 0.8 }, label: { show: false } },
            data
        }]
    }

    chart.setOption(option, { notMerge: true, replaceMerge: ['series', 'visualMap'] })

    chart.off('click')
    chart.on('click', async params => {
        const clickedName = params?.name
        const iso2 = nameToIso2(clickedName)
        if (!iso2) return
        currentLevel.value = iso2
        currentCountryName.value = clickedName
        emit('country-click', { iso2, name: clickedName })
        try {
            await renderCountry(iso2, clickedName)
        } catch {
            currentLevel.value = 'world'
            currentCountryName.value = ''
        }
    })
}

async function renderCountry(iso2, displayName) {
    try {
        const resOfRegions = await store.dispatch('analytic/regionsByCountry', {...props.params, country_code: iso2});
        const regionStats = resOfRegions.data;

        const res = await fetch(`/mapRegions/${iso2.toLowerCase()}High.json`, {
            headers: { 'Accept': 'application/json' },
            cache: 'force-cache'
        })
        const geo = await res.json()

        for (const f of (geo?.features || [])) {
            f.properties = f.properties || {}
            f.properties.label =
                f.properties.label ||
                f.properties.name ||
                f.properties.NAME_1 ||
                f.properties.name_1 ||
                f.properties.shapeName ||
                f.properties.region ||
                f.properties.admin1 ||
                ''
            const id = pickRegionId(iso2, f.properties)
            f.properties.id = id || f.properties.id || f.properties.label
        }

        echarts.registerMap(iso2, geo)

        const statsById = (props.pageData?.data?.regionStats?.[iso2]) || regionStats[iso2] || {}

        // ✅ No-data regions → value: null
        const data = (geo.features || []).map(f => {
            const id = f.properties.id
            const s  = id ? statsById[id] : null
            const value = s
                ? (metricKey.value === 'aov'
                    ? Number(s.aov || 0)
                    : (metricKey.value === 'orders' ? Number(s.orders || 0) : Number(s.sales || 0)))
                : null
            return {
                name: id,
                value,
                label: f.properties.label || id
            }
        })

        const values = data.map(d => +d.value || 0).filter(v => v > 0)
        const maxVal = Math.max(...values, 10)

        const option = {
            title: { text: `${displayName} — ${labelForMetric()} by Region`, left: 'center' },
            tooltip: {
                trigger: 'item',
                confine: true,
                formatter: (p) => {
                    const id = p?.name
                    const label = p?.data?.label || id
                    const s = statsById[id]
                    if (!s) return `<b>${label}</b><br><small>${id}</small><br>No data`
                    const main = metricKey.value === 'aov' ? formatCurrency(s.aov) : formatNumber(parseInt(s[metricKey.value]))
                    return `<div class="tw-text-sm">
            <div><b>${label}</b> <span class="tw-text-gray-500">(${id})</span></div>
            <div>${labelForMetric()}: ${main}</div>
            <hr class="tw-my-1 tw-border-gray-200"/>
            <div>Sales: ${formatCurrency(parseInt(s.sales))}</div>
            <div>Orders: ${formatNumber(s.orders)}</div>
            <div>AOV: ${formatCurrency(parseInt(s.aov))}</div>
          </div>`
                }
            },
            visualMap: {
                min: 1, max: maxVal, calculable: true, orient: 'horizontal', left: 'center', bottom: 10,
                inRange: { color: ['#93c5fd', '#1d4ed8'] }, outOfRange: { color: '#e5e7eb' }
            },
            series: [{
                type: 'map',
                map: iso2,
                nameProperty: 'id',
                roam: true,
                layoutCenter: ['50%', '55%'],
                zoom: 0.9,
                layoutSize: '100%',
                scaleLimit: { min: 0.8, max: 10 },
                itemStyle: { borderColor: '#9aa4b2', borderWidth: 0.6, areaColor: '#e5e7eb' }, // base white
                emphasis: { itemStyle: { borderColor: '#475569', borderWidth: 0.8 }, label: { show: false } },
                data
            }]
        }

        chart.setOption(option, { notMerge: true, replaceMerge: ['series', 'visualMap'] })

        chart.off('click')
        chart.on('click', params => {
            if (!params?.name) return
            emit('region-click', { iso2, regionId: params.name, regionLabel: params?.data?.label || params.name })
        })
    } catch (err) {
        console.error('Failed to load geo data:', err)
        const msg = `Failed to load regions for ${iso2}: ${err?.message || err}`
        errorText.value = msg
        emit('error', msg)
    }
}

// ── Lifecycle
function initChart() {
    if (!chartEl.value) return
    chart = echarts.init(chartEl.value)
    resizeObserver = new ResizeObserver(() => chart?.resize())
    resizeObserver.observe(chartEl.value)
    renderWorld()
}
onMounted(initChart)
onBeforeUnmount(() => {
    if (resizeObserver && chartEl.value) resizeObserver.unobserve(chartEl.value)
    resizeObserver = null
    chart && chart.dispose()
})

watch(() => props.metric, () => {
    if (!chart) return
    if (currentLevel.value !== 'world') {
        renderCountry(currentLevel.value, currentCountryName.value)
    } else {
        renderWorld()
    }
})

async function backToWorld() {
    currentLevel.value = 'world'
    currentCountryName.value = ''
    await renderWorld()
}
</script>

<template>
    <div class="w-full">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-semibold">
        <span v-if="currentLevel !== 'world'" class="text-gray-500">
          <button
              v-if="currentLevel !== 'world'"
              @click="backToWorld"
              class="px-3 py-1.5 rounded-xl border border-gray-300 hover:bg-gray-50 text-sm"
          >
            ← Back to world
          </button>
        </span>
            </h2>
            <div class="flex items-center gap-2">
                <slot name="controls"></slot>
            </div>
        </div>

        <div class="relative">
            <div ref="chartEl" class="w-full h-[720px] rounded-2xl border border-gray-200 shadow-sm" />
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/60 rounded-2xl">
                <div class="animate-pulse text-gray-600 text-sm">Loading map…</div>
            </div>
            <div v-if="errorText" class="absolute bottom-3 left-3 right-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg p-2">
                {{ errorText }}
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ECharts tooltip HTML can use Tailwind utility classes (prefixed here as tw-*) */
</style>
