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

  Array.from({ length: detailQuantity.value }).forEach(() => addToCart(currentProduct.value!, selectedProductColor.value))
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

        <div class="mobile-detail-price">{{ currentProduct.price }} <span aria-hidden="true">&#1423;</span></div>

        <div class="product-detail-art" @touchstart.passive="startProductTouch" @touchend.passive="finishProductTouch">
          <img :src="currentProductImage" :alt="currentProduct.title[currentLanguageCode] || currentProduct.title.hy" />
        </div>
      </div>

      <aside class="product-detail-info">
        <h1 id="product-title">{{ currentProduct.title[currentLanguageCode] || currentProduct.title.hy }}</h1>
        <p v-if="productShortDescription" class="product-short-description">{{ productShortDescription }}</p>
        <div v-if="productDescription" class="product-description" v-html="productDescription"></div>
        <p v-if="isCurrentProductUnavailable" class="detail-unavailable">{{ copy.temporarilyUnavailable }}</p>

        <div v-if="currentProductPriceOptions.length" class="price-options">
          <div v-for="group in currentProductPriceOptions" :key="group.key" class="price-option-group">
            <span>{{ group.label }}</span>
            <div class="price-option-list">
              <button v-for="option in group.values" :key="`${group.key}-${option.id}`" type="button">
                <strong>{{ option.label || option.name }}</strong>
                <small>{{ option.price }} <span aria-hidden="true">&#1423;</span></small>
              </button>
            </div>
          </div>
        </div>

        <form class="product-options" @submit.prevent>
          <label class="option-field option-wide">
            <span>{{ copy.optionCode }}</span>
            <input type="text" :value="currentProduct.options?.code || copy.optionCode" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionSize }}</span>
            <select>
              <option>{{ currentProduct.options?.size || copy.profile }}</option>
            </select>
          </label>

          <label class="option-field">
            <span>{{ copy.optionQuantity }}</span>
            <input type="text" :value="currentProduct.options?.quantity || '1'" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionType }}</span>
            <select>
              <option>{{ currentProduct.options?.type || copy.optionType }}</option>
            </select>
          </label>

          <label class="option-field">
            <span>{{ copy.optionUnitLong }}</span>
            <input type="text" :value="currentProduct.options?.unit || copy.optionUnit" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionPiece }}</span>
            <input type="text" :value="currentProduct.options?.piece || '44'" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionHeight }}</span>
            <input type="text" :value="currentProduct.options?.height || '111'" />
          </label>

          <div class="color-options" aria-label="Colors">
            <span>{{ copy.optionColor }}</span>
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
          </div>
        </form>

        <div class="purchase-row">
          <div class="detail-price">{{ currentProduct.price }} <span aria-hidden="true">&#1423;</span></div>

          <div class="purchase-actions">
            <div class="quantity-control" aria-label="Quantity">
              <button type="button" @click="updateDetailQuantity(-1)">&minus;</button>
              <span>{{ detailQuantity }}</span>
              <button type="button" @click="updateDetailQuantity(1)">+</button>
            </div>
            <button type="button" :disabled="isCurrentProductUnavailable" :class="{ 'is-added': recentlyAddedProductId === currentProduct.id }" @click="addCurrentProductToCart">
              {{ isCurrentProductUnavailable ? copy.temporarilyUnavailable : (recentlyAddedProductId === currentProduct.id ? copy.added : copy.add) }}
            </button>
          </div>
        </div>
      </aside>
    </div>

    <section class="related-section" aria-labelledby="related-title">
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
