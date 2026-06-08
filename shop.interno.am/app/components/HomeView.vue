<script setup lang="ts">
const { currentLanguageCode, menuGroups, products, secondaryProducts } = useCatalog()
</script>

<template>
  <section class="catalog-section" aria-labelledby="stretch-title">
    <h1 id="stretch-title">{{ menuGroups[0].title[currentLanguageCode] }}</h1>
    <h2>{{ menuGroups[0].children[currentLanguageCode][0] }}</h2>

    <div class="product-grid">
      <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
  </section>

  <section class="catalog-section" aria-labelledby="mdf-title">
    <h2 id="mdf-title">{{ menuGroups[1].title[currentLanguageCode] }}</h2>

    <div class="product-grid short-grid">
      <ProductCard v-for="product in secondaryProducts" :key="product.id" :product="product" />
    </div>
  </section>
</template>
