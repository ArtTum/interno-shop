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
        <p v-if="isCurrentProductUnavailable" class="detail-unavailable">{{ copy.temporarilyUnavailable }}</p>
        <form class="product-code-options" @submit.prevent>
          <label class="option-field option-wide">
            <span>{{ copy.optionCode }}</span>
            <input type="text" :value="currentProduct.options?.code || copy.optionCode" disabled />
          </label>
        </form>

        <div v-if="visiblePriceOptionGroups.length" class="price-options">
          <div v-for="group in visiblePriceOptionGroups" :key="group.key" class="price-option-group">
            <label :for="`attr-${group.key}`" class="price-option-label">{{ attrGroupLabel(group.key) }}</label>
            <select
              :id="`attr-${group.key}`"
              class="price-option-select"
              :value="selectedPriceOptions[group.key]?.id"
              @change="selectPriceOption(group.key, ($event.target as HTMLSelectElement).value)"
            >
              <option
                v-for="option in group.values"
                :key="option.id"
                :value="option.id"
              >
                {{ option.label || option.name }} — {{ Number(option.price).toLocaleString() }} &#1423;
              </option>
            </select>
          </div>
        </div>

        <form class="product-options" @submit.prevent>
          <label v-if="currentProduct.options?.size" class="option-field">
            <span>{{ copy.optionSize }}</span>
            <input type="text" :value="currentProduct.options.size" disabled />
          </label>

          <label v-if="currentProduct.options?.height" class="option-field">
            <span>{{ copy.optionHeight }}</span>
            <input type="text" :value="currentProduct.options.height" disabled />
          </label>

          <label class="option-field">
            <span>{{ copy.optionType }}</span>
            <input type="text" :value="currentProduct.options?.type || '—'" disabled />
          </label>

          <label class="option-field">
            <span>{{ copy.optionMaterial }}</span>
            <input type="text" :value="currentProduct.options?.material || '—'" disabled />
          </label>

          <div v-if="productColors.length" class="color-options" aria-label="Colors">
            <span>{{ copy.optionColor }}</span>
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
        </form>

        <div class="purchase-row">
          <div class="detail-price">
            {{ Number(displayPrice).toLocaleString() }} <span aria-hidden="true">&#1423;</span>
          </div>

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
              {{ isCurrentProductUnavailable ? copy.temporarilyUnavailable : (recentlyAddedProductId === currentProduct.id ? copy.added : copy.add) }}
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
