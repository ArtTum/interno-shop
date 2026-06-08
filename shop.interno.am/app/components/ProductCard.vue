<script setup lang="ts">
defineProps<{ product: any }>()

const { addToCart, copy, currentLanguageCode, openProduct, recentlyAddedProductId } = useCatalog()
</script>

<template>
  <article
    class="product-card"
    role="link"
    tabindex="0"
    :aria-label="product.title[currentLanguageCode]"
    @click="openProduct(product)"
    @keydown.enter="openProduct(product)"
    @keydown.space.prevent="openProduct(product)"
  >
    <div class="badges">
      <span v-if="product.isNew" class="new">{{ copy.new }}</span>
    </div>

    <div class="product-art">
      <img :src="product.image" :alt="product.title[currentLanguageCode]" />
    </div>

    <p>{{ product.title[currentLanguageCode] }}</p>
    <div class="product-actions">
      <span class="price">{{ product.price }} <span aria-hidden="true">&#1423;</span></span>
      <button type="button" :class="{ 'is-added': recentlyAddedProductId === product.id }" @click.stop="addToCart(product)">
        {{ recentlyAddedProductId === product.id ? copy.added : copy.add }}
      </button>
    </div>
  </article>
</template>
