<script setup lang="ts">
const { categoryProducts, copy, currentCategoryChild, currentCategoryChildren, currentCategoryGroup, currentLanguageCode } = useCatalog()
</script>

<template>
  <section v-if="currentCategoryGroup" class="catalog-section category-page" aria-labelledby="category-title">
    <nav class="category-breadcrumb" aria-label="Category breadcrumb">
      <span>{{ currentCategoryGroup.title[currentLanguageCode] || currentCategoryGroup.title.hy }}</span>
      <span v-if="currentCategoryChild || currentCategoryChildren[0]" aria-hidden="true">›</span>
      <strong id="category-title">{{ currentCategoryChild || currentCategoryChildren[0] || currentCategoryGroup.title[currentLanguageCode] || currentCategoryGroup.title.hy }}</strong>
    </nav>

    <div class="category-products-panel">
      <div v-if="categoryProducts.length" class="product-grid category-product-grid">
        <ProductCard v-for="product in categoryProducts" :key="product.id" :product="product" />
      </div>
      <div v-else class="category-empty">
        {{ copy.categoryEmpty }}
      </div>
    </div>
  </section>
</template>
