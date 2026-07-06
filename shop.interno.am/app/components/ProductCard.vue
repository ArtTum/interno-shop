<script setup lang="ts">
defineProps<{ product: any }>()

const { addToCart, copy, currentLanguageCode, openProduct, productPath, recentlyAddedProductId } = useCatalog()
</script>

<template>
  <article
    class="product-card"
    role="link"
    tabindex="0"
    :aria-label="product.title[currentLanguageCode] || product.title.hy"
    @click="openProduct(product)"
    @keydown.enter="openProduct(product)"
    @keydown.space.prevent="openProduct(product)"
  >
    <div class="badges" :class="{ 'is-unavailable': product.isTemporarilyUnavailable }">
      <span v-if="product.isTemporarilyUnavailable" class="unavailable">{{ copy.outOfStock }}</span>
      <span v-else-if="product.isNew" class="new">{{ copy.new }}</span>
    </div>

    <NuxtLink class="product-art" :to="productPath(product)" @click.stop>
      <img :src="product.image" :alt="product.title[currentLanguageCode] || product.title.hy" />
    </NuxtLink>

    <NuxtLink class="product-title-link" :to="productPath(product)" @click.stop>
      <p>{{ product.title[currentLanguageCode] || product.title.hy }}</p>
    </NuxtLink>

    <!-- Color swatches -->
    <div v-if="product.options?.colors?.length > 1" class="card-colors" @click.stop>
      <span
        v-for="color in product.options.colors.slice(0, 6)"
        :key="color.id"
        class="card-color-dot"
        :style="{ background: color.value }"
        :title="color.name"
      />
      <span v-if="product.options.colors.length > 6" class="card-color-more">+{{ product.options.colors.length - 6 }}</span>
    </div>

    <div class="product-actions">
      <span class="price">{{ product.price }} <span aria-hidden="true">&#1423;</span></span>
      <button type="button" :disabled="product.isTemporarilyUnavailable" :class="{ 'is-added': recentlyAddedProductId === product.id }" @click.stop="addToCart(product)">
        {{ recentlyAddedProductId === product.id ? copy.added : copy.add }}
      </button>
    </div>
  </article>
</template>
