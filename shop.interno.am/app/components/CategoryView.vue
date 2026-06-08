<script setup lang="ts">
const { currentCategoryChild, currentCategoryGroup, currentLanguageCode, products } = useCatalog()
</script>

<template>
  <section v-if="currentCategoryGroup" class="catalog-section category-page" aria-labelledby="category-title">
    <nav class="category-breadcrumb" aria-label="Category breadcrumb">
      <span>{{ currentCategoryGroup.title[currentLanguageCode] }}</span>
      <span aria-hidden="true">›</span>
      <strong id="category-title">{{ currentCategoryChild || currentCategoryGroup.children[currentLanguageCode][0] }}</strong>
    </nav>

    <div class="category-products-panel">
      <div class="product-grid category-product-grid">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>
    </div>
  </section>
</template>
