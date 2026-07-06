<script setup lang="ts">
import { computed, ref, watch } from 'vue'

const {
  addToCart,
  copy,
  currentLanguageCode,
  currentProduct,
  currentProductCategoryChild,
  currentProductCategoryGroup,
  currentProductImage,
  currentProductPriceOptions,
  detailThumbnails,
  localizedPath,
  relatedProducts,
  recentlyAddedProductId,
  scrollProductSlider,
  selectProductImage,
  similarProducts
} = useCatalog()

const touchStartX = ref(0)
const detailQuantity = ref(1)
const selectedColor = ref<any | null>(null)
const selectedPriceOptions = ref<Record<string, { id: number | string, name: string, label: string, price: string }>>({})
type DetailParameter =
  | { type: 'text', key: string, label: string, value: string }
  | { type: 'select', key: string, label: string, group: { key: string, values: Array<{ id: number | string, name: string, label: string, price: string }> } }
  | { type: 'color', key: string, label: string }

const isCurrentProductUnavailable = computed(() => Boolean(currentProduct.value?.isTemporarilyUnavailable))

const productShortDescription = computed(() => {
  const record = currentProduct.value?.shortDescription

  return record?.[currentLanguageCode.value] || record?.hy || Object.values(record || {})[0] || ''
})

const productDescription = computed(() => {
  const record = currentProduct.value?.description

  return record?.[currentLanguageCode.value] || record?.hy || Object.values(record || {})[0] || ''
})

const productColors = computed(() => {
  const colors = currentProduct.value?.options?.colors

  if (Array.isArray(colors) && colors.length) {
    return colors
      .map((color) => ({
        id: color.id ?? color.value ?? color.name,
        name: color.name || color.value || copy.value.optionColor,
        value: color.value || '#ffffff'
      }))
      .filter((color) => color.value)
  }

  const fallbackColor = currentProduct.value?.options?.color

  return fallbackColor
    ? [{ id: fallbackColor, name: copy.value.optionColor, value: fallbackColor }]
    : []
})

const selectedProductColor = computed(() => selectedColor.value || productColors.value[0] || null)
const visiblePriceOptionGroups = computed(() => currentProductPriceOptions.value
  .filter((group) => ['height', 'length', 'unit', 'size', 'power'].includes(group.key)))

const orderedPriceOptionGroups = computed(() => {
  const order = ['height', 'length', 'unit', 'size', 'power']

  return [...visiblePriceOptionGroups.value].sort((first, second) => {
    const firstIndex = order.indexOf(first.key)
    const secondIndex = order.indexOf(second.key)

    return (firstIndex === -1 ? order.length : firstIndex) - (secondIndex === -1 ? order.length : secondIndex)
  })
})

// Price from selected attribute option (first group wins)
const displayPrice = computed(() => {
  const first = Object.values(selectedPriceOptions.value)[0]

  return first ? first.price : (currentProduct.value?.price || '0')
})

const effectivePrice = computed(() => {
  const first = Object.values(selectedPriceOptions.value)[0]

  return first ? Number(first.price) : null
})

// Human-readable summary of all selected options e.g. "3 m · Meter"
const selectedOptionSummary = computed(() => {
  return Object.values(selectedPriceOptions.value)
    .map((opt) => opt.label || opt.name)
    .filter(Boolean)
    .join(' · ') || null
})

const selectedOptionDetails = computed(() => {
  return visiblePriceOptionGroups.value
    .map((group) => {
      const selected = selectedPriceOptions.value[group.key]

      return selected
        ? {
            key: group.key,
            label: attrGroupLabel(group.key),
            value: selected.label || selected.name
          }
        : null
    })
    .filter((option): option is { key: string, label: string, value: string } => Boolean(option))
})

// Labels for attribute groups — reactive to current language via copy
function attrGroupLabel(key: string): string {
  const map: Record<string, string> = {
    height: copy.value.optionLength,
    length: copy.value.optionLength,
    unit: copy.value.optionUnitLong,
    size: copy.value.optionSize,
    power: copy.value.optionPower
  }

  return map[key] || (key.charAt(0).toUpperCase() + key.slice(1))
}

const productParameters = computed<DetailParameter[]>(() => {
  const product = currentProduct.value
  const options = product?.options || {}
  const params: DetailParameter[] = []
  const addTextParam = (key: string, label: string, value?: string | null) => {
    if (value) {
      params.push({ type: 'text', key, label, value })
    }
  }
  const addSelectParam = (group: (typeof orderedPriceOptionGroups.value)[number] | undefined) => {
    if (group) {
      params.push({ type: 'select', key: `price-${group.key}`, label: attrGroupLabel(group.key), group })
    }
  }

  addTextParam('code', copy.value.optionCode, options.code)
  addTextParam('material', copy.value.optionMaterial, options.material)
  addTextParam('type', copy.value.optionType, options.type)
  addTextParam('size', copy.value.optionSize, options.size)
  addTextParam('height', copy.value.optionHeight, options.height)

  addSelectParam(orderedPriceOptionGroups.value.find((group) => ['height', 'length'].includes(group.key)))
  addSelectParam(orderedPriceOptionGroups.value.find((group) => group.key === 'unit'))

  orderedPriceOptionGroups.value
    .filter((group) => !['height', 'length', 'unit'].includes(group.key))
    .forEach(addSelectParam)

  if (productColors.value.length) {
    params.push({ type: 'color', key: 'color', label: copy.value.optionColor })
  }

  return params
})

watch(
  productColors,
  (colors) => {
    if (!colors.length) {
      selectedColor.value = null
      return
    }

    const selectedId = selectedColor.value?.id
    const hasSelectedColor = colors.some((color) => color.id === selectedId)

    if (!hasSelectedColor) {
      selectedColor.value = colors[0]
    }
  },
  { immediate: true }
)

watch(
  visiblePriceOptionGroups,
  (groups) => {
    const newSelections: typeof selectedPriceOptions.value = {}

    for (const group of groups) {
      const existing = selectedPriceOptions.value[group.key]
      const hasExisting = existing && group.values.some((v) => String(v.id) === String(existing.id))
      const target = hasExisting ? existing : group.values[0]

      if (target) {
        newSelections[group.key] = target
      }
    }

    selectedPriceOptions.value = newSelections
  },
  { immediate: true, deep: true }
)

function selectPriceOption(groupKey: string, optionId: string | undefined) {
  if (!optionId) return
  const group = visiblePriceOptionGroups.value.find((g) => g.key === groupKey)

  if (!group) return

  const option = group.values.find((v) => String(v.id) === String(optionId))

  if (option) {
    selectedPriceOptions.value = { ...selectedPriceOptions.value, [groupKey]: option }
  }
}

function showProductImage(offset: number) {
  if (!detailThumbnails.value.length) {
    return
  }

  const currentIndex = detailThumbnails.value.findIndex((thumb) => thumb === currentProductImage.value)
  const safeIndex = currentIndex >= 0 ? currentIndex : 0
  const nextIndex = (safeIndex + offset + detailThumbnails.value.length) % detailThumbnails.value.length

  selectProductImage(detailThumbnails.value[nextIndex])
}

function startProductTouch(event: TouchEvent) {
  touchStartX.value = event.changedTouches[0]?.clientX || 0
}

function finishProductTouch(event: TouchEvent) {
  const touchEndX = event.changedTouches[0]?.clientX || 0
  const distance = touchEndX - touchStartX.value

  if (Math.abs(distance) < 36) {
    return
  }

  showProductImage(distance < 0 ? 1 : -1)
}

function updateDetailQuantity(change: number) {
  detailQuantity.value = Math.max(1, detailQuantity.value + change)
}

function addCurrentProductToCart() {
  if (!currentProduct.value || isCurrentProductUnavailable.value) {
    return
  }

  Array.from({ length: detailQuantity.value }).forEach(() =>
    addToCart(
      currentProduct.value!,
      selectedProductColor.value,
      effectivePrice.value,
      selectedOptionSummary.value,
      selectedOptionDetails.value
    )
  )
}
</script>

<template>
  <section v-if="currentProduct" class="product-detail" aria-labelledby="product-title">
    <nav class="product-breadcrumb" aria-label="Breadcrumb">
      <NuxtLink :to="localizedPath('/')">{{ copy.home }}</NuxtLink>
      <span aria-hidden="true">&rsaquo;</span>
      <span>{{ currentProductCategoryGroup?.title?.[currentLanguageCode] || currentProductCategoryGroup?.title?.hy || copy.relatedProducts }}</span>
      <template v-if="currentProductCategoryChild">
        <span aria-hidden="true">&rsaquo;</span>
        <strong>{{ currentProductCategoryChild }}</strong>
      </template>
    </nav>

    <div class="product-detail-card">
      <div class="product-gallery">
        <div class="product-thumbs" aria-label="Product gallery">
          <button
            v-for="(thumb, index) in detailThumbnails"
            :key="`${thumb}-${index}`"
            type="button"
            :class="{ 'is-active': thumb === currentProductImage }"
            :aria-pressed="thumb === currentProductImage"
            @click="selectProductImage(thumb)"
          >
            <img :src="thumb" :alt="currentProduct.title[currentLanguageCode] || currentProduct.title.hy" />
          </button>
        </div>

        <div class="mobile-detail-price">{{ displayPrice }} <span aria-hidden="true">&#1423;</span></div>

        <div class="product-detail-art" @touchstart.passive="startProductTouch" @touchend.passive="finishProductTouch">
          <img :src="currentProductImage" :alt="currentProduct.title[currentLanguageCode] || currentProduct.title.hy" />
        </div>
      </div>

      <aside class="product-detail-info">
        <h1 id="product-title">{{ currentProduct.title[currentLanguageCode] || currentProduct.title.hy }}</h1>
        <p v-if="productShortDescription" class="product-short-description">{{ productShortDescription }}</p>
        <div v-if="productDescription" class="product-description" v-html="productDescription"></div>
        <form v-if="productParameters.length" class="product-parameters" @submit.prevent>
          <template v-for="parameter in productParameters" :key="parameter.key">
            <label v-if="parameter.type === 'text'" class="option-field">
              <span>{{ parameter.label }}</span>
              <input type="text" :value="parameter.value" disabled />
            </label>

            <div v-else-if="parameter.type === 'select'" class="price-option-group">
              <label :for="`attr-${parameter.group.key}`" class="price-option-label">{{ parameter.label }}</label>
              <select
                :id="`attr-${parameter.group.key}`"
                class="price-option-select"
                :value="selectedPriceOptions[parameter.group.key]?.id"
                @change="selectPriceOption(parameter.group.key, ($event.target as HTMLSelectElement).value)"
              >
                <option
                  v-for="option in parameter.group.values"
                  :key="option.id"
                  :value="option.id"
                >
                  {{ option.label || option.name }} — {{ Number(option.price).toLocaleString() }} &#1423;
                </option>
              </select>
            </div>

            <div v-else class="color-options" aria-label="Colors">
              <span>{{ parameter.label }}</span>
              <div class="color-dots-row">
                <button
                  v-for="color in productColors"
                  :key="color.id"
                  type="button"
                  class="color-dot"
                  :class="{ 'is-active': selectedProductColor?.id === color.id }"
                  :style="{ background: color.value }"
                  :aria-label="color.name"
                  :aria-pressed="selectedProductColor?.id === color.id"
                  :title="color.name"
                  @click="selectedColor = color"
                />
                <span v-if="selectedProductColor" class="color-name-label">{{ selectedProductColor.name }}</span>
              </div>
            </div>
          </template>
        </form>

        <div class="purchase-row">
          <div class="detail-price">
            {{ Number(displayPrice).toLocaleString() }} <span aria-hidden="true">&#1423;</span>
          </div>

          <span v-if="isCurrentProductUnavailable" class="detail-stock-status">{{ copy.outOfStock }}</span>

          <div class="purchase-actions">
            <div class="quantity-control" aria-label="Quantity">
              <button type="button" @click="updateDetailQuantity(-1)">&minus;</button>
              <span>{{ detailQuantity }}</span>
              <button type="button" @click="updateDetailQuantity(1)">+</button>
            </div>
            <button
              type="button"
              :disabled="isCurrentProductUnavailable"
              :class="{ 'is-added': recentlyAddedProductId === currentProduct.id }"
              @click="addCurrentProductToCart"
            >
              {{ recentlyAddedProductId === currentProduct.id ? copy.added : copy.add }}
            </button>
          </div>
        </div>
      </aside>
    </div>

    <section v-if="relatedProducts.length" class="related-section" aria-labelledby="related-title">
      <div class="slider-head">
        <h2 id="related-title">{{ copy.relatedProducts }}</h2>
        <div class="slider-controls" :aria-label="copy.sliderControls">
          <button type="button" :aria-label="copy.previousProducts" @click="scrollProductSlider('related-slider', 'previous')"><span class="slider-chevron is-previous" aria-hidden="true" /></button>
          <button type="button" :aria-label="copy.nextProducts" @click="scrollProductSlider('related-slider', 'next')"><span class="slider-chevron is-next" aria-hidden="true" /></button>
        </div>
      </div>
      <div id="related-slider" class="product-grid related-grid product-slider">
        <ProductCard v-for="product in relatedProducts" :key="product.id" :product="product" />
      </div>
    </section>

    <section class="related-section" aria-labelledby="similar-title">
      <div class="slider-head">
        <h2 id="similar-title">{{ copy.similarProducts }}</h2>
        <div class="slider-controls" :aria-label="copy.sliderControls">
          <button type="button" :aria-label="copy.previousProducts" @click="scrollProductSlider('similar-slider', 'previous')"><span class="slider-chevron is-previous" aria-hidden="true" /></button>
          <button type="button" :aria-label="copy.nextProducts" @click="scrollProductSlider('similar-slider', 'next')"><span class="slider-chevron is-next" aria-hidden="true" /></button>
        </div>
      </div>
      <div id="similar-slider" class="product-grid related-grid product-slider">
        <ProductCard v-for="product in similarProducts" :key="product.id" :product="product" />
      </div>
    </section>
  </section>
</template>
