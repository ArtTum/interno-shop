<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'

type Craftsman = {
  id: number | null
  code: string
  name: string
  image?: string | null
  phone?: string | null
  work_region?: string | null
  work_city?: string | null
  work_field?: string | null
  has_whatsapp?: boolean
  has_viber?: boolean
}

const {
  cartCount,
  copy,
  fetchCraftsmen,
  localizedPath,
  selectCraftsmanForCheckout
} = useCatalog()

const router = useRouter()
const craftsmen = ref<Craftsman[]>([])
const filters = reactive({
  regions: [] as string[],
  cities: [] as string[],
  fields: [] as string[]
})
const query = reactive({
  search: '',
  region: '',
  city: '',
  field: ''
})
const isLoading = ref(false)
let searchTimer: ReturnType<typeof setTimeout> | null = null

async function loadCraftsmen() {
  isLoading.value = true

  try {
    const response = await fetchCraftsmen({
      search: query.search,
      region: query.region,
      city: query.city,
      field: query.field,
      limit: 100
    })

    craftsmen.value = response.data || []
    filters.regions = response.filters?.regions || []
    filters.cities = response.filters?.cities || []
    filters.fields = response.filters?.fields || []
  } finally {
    isLoading.value = false
  }
}

function goToCheckout() {
  router.push(`${localizedPath('/cart')}?checkout=1`)
}

function chooseCraftsman(craftsman: Craftsman) {
  selectCraftsmanForCheckout(craftsman)

  if (cartCount.value) {
    goToCheckout()
  }
}

function skipCraftsman() {
  selectCraftsmanForCheckout(null)
  goToCheckout()
}

watch(query, () => {
  if (searchTimer) {
    clearTimeout(searchTimer)
  }

  searchTimer = setTimeout(loadCraftsmen, 220)
}, { deep: true })

onMounted(loadCraftsmen)
</script>

<template>
  <section class="craftsmen-page" aria-labelledby="craftsmen-title">
    <div class="craftsmen-head">
      <div>
        <h1 id="craftsmen-title">{{ copy.craftsmenTitle }}</h1>
        <p>{{ copy.craftsmenIntro }}</p>
      </div>
      <button v-if="cartCount" class="craftsmen-skip" type="button" @click="skipCraftsman">
        {{ copy.craftsmenSkip }}
      </button>
    </div>

    <div class="craftsmen-filters">
      <label>
        <span>{{ copy.craftsmenSearch }}</span>
        <input v-model="query.search" type="search" :placeholder="copy.craftsmanSearchPlaceholder" />
      </label>
      <label>
        <span>{{ copy.craftsmenRegion }}</span>
        <select v-model="query.region">
          <option value="">{{ copy.craftsmenAll }}</option>
          <option v-for="region in filters.regions" :key="region" :value="region">{{ region }}</option>
        </select>
      </label>
      <label>
        <span>{{ copy.craftsmenCity }}</span>
        <select v-model="query.city">
          <option value="">{{ copy.craftsmenAll }}</option>
          <option v-for="city in filters.cities" :key="city" :value="city">{{ city }}</option>
        </select>
      </label>
      <label>
        <span>{{ copy.craftsmenField }}</span>
        <select v-model="query.field">
          <option value="">{{ copy.craftsmenAll }}</option>
          <option v-for="field in filters.fields" :key="field" :value="field">{{ field }}</option>
        </select>
      </label>
    </div>

    <div v-if="isLoading" class="craftsmen-loading">...</div>

    <div v-else-if="craftsmen.length" class="craftsmen-list">
      <article v-for="craftsman in craftsmen" :key="`${craftsman.id}-${craftsman.code}`" class="craftsman-row">
        <figure class="craftsman-avatar">
          <img v-if="craftsman.image" :src="craftsman.image" :alt="craftsman.name" />
          <div v-else class="craftsman-avatar-fallback">{{ craftsman.name.slice(0, 1) }}</div>
        </figure>

        <div class="craftsman-main">
          <div class="craftsman-name-row">
            <h2>{{ craftsman.name }}</h2>
            <span v-if="craftsman.work_field" class="craftsman-field-tag">{{ craftsman.work_field }}</span>
          </div>
          <div class="craftsman-meta">
            <span v-if="craftsman.work_region">{{ craftsman.work_region }}</span>
            <span v-if="craftsman.work_city" class="meta-sep">·</span>
            <span v-if="craftsman.work_city">{{ craftsman.work_city }}</span>
            <span v-if="craftsman.phone" class="meta-sep">·</span>
            <a v-if="craftsman.phone" :href="`tel:${craftsman.phone}`" class="craftsman-phone">{{ craftsman.phone }}</a>
          </div>
        </div>

        <div class="craftsman-side">
          <strong class="craftsman-code">{{ craftsman.code }}</strong>
          <div class="craftsman-badges">
            <span v-if="craftsman.has_whatsapp">WhatsApp</span>
            <span v-if="craftsman.has_viber">Viber</span>
          </div>
          <button type="button" @click="chooseCraftsman(craftsman)">
            {{ copy.craftsmenChoose }}
          </button>
        </div>
      </article>
    </div>

    <div v-else class="craftsmen-empty">
      {{ copy.craftsmenEmpty }}
    </div>
  </section>
</template>
