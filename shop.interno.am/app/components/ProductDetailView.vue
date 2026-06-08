<script setup lang="ts">
import { ref } from 'vue'

const {
  addToCart,
  copy,
  currentLanguageCode,
  currentProduct,
  currentProductImage,
  detailThumbnails,
  localizedPath,
  menuGroups,
  relatedProducts,
  recentlyAddedProductId,
  scrollProductSlider,
  selectProductImage,
  similarProducts
} = useCatalog()

const touchStartX = ref(0)
const detailQuantity = ref(1)

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
  if (!currentProduct.value) {
    return
  }

  Array.from({ length: detailQuantity.value }).forEach(() => addToCart(currentProduct.value!))
}
</script>

<template>
  <section v-if="currentProduct" class="product-detail" aria-labelledby="product-title">
    <nav class="product-breadcrumb" aria-label="Breadcrumb">
      <NuxtLink :to="localizedPath('/')">{{ copy.home }}</NuxtLink>
      <span aria-hidden="true">&rsaquo;</span>
      <span>{{ menuGroups[0].title[currentLanguageCode] }}</span>
      <span aria-hidden="true">&rsaquo;</span>
      <strong>{{ menuGroups[0].children[currentLanguageCode][0] }}</strong>
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
            <img :src="thumb" :alt="currentProduct.title[currentLanguageCode]" />
          </button>
        </div>

        <div class="mobile-detail-price">{{ currentProduct.price }} <span aria-hidden="true">&#1423;</span></div>

        <div class="product-detail-art" @touchstart.passive="startProductTouch" @touchend.passive="finishProductTouch">
          <img :src="currentProductImage" :alt="currentProduct.title[currentLanguageCode]" />
        </div>
      </div>

      <aside class="product-detail-info">
        <h1 id="product-title">{{ currentProduct.title[currentLanguageCode] }}</h1>

        <form class="product-options" @submit.prevent>
          <label class="option-field option-wide">
            <span>{{ copy.optionCode }}</span>
            <input type="text" :value="copy.optionCode" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionSize }}</span>
            <select>
              <option>{{ copy.profile }}</option>
            </select>
          </label>

          <label class="option-field">
            <span>{{ copy.optionQuantity }}</span>
            <input type="text" value="1" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionType }}</span>
            <select>
              <option>{{ copy.optionType }}</option>
            </select>
          </label>

          <label class="option-field">
            <span>{{ copy.optionUnitLong }}</span>
            <input type="text" :value="copy.optionUnit" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionPiece }}</span>
            <input type="text" value="44" />
          </label>

          <label class="option-field">
            <span>{{ copy.optionHeight }}</span>
            <input type="text" value="111" />
          </label>

          <div class="color-options" aria-label="Colors">
            <span>{{ copy.optionColor }}</span>
            <button type="button" class="color-dot color-white" aria-label="White" />
            <button type="button" class="color-dot color-cream" aria-label="Cream" />
            <button type="button" class="color-dot color-gray" aria-label="Gray" />
            <button type="button" class="color-dot color-dark" aria-label="Dark" />
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
            <button type="button" :class="{ 'is-added': recentlyAddedProductId === currentProduct.id }" @click="addCurrentProductToCart">
              {{ recentlyAddedProductId === currentProduct.id ? copy.added : copy.add }}
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
