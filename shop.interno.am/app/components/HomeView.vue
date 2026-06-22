<script setup lang="ts">
const { productSections } = useCatalog()
</script>

<template>
  <section
    v-for="(section, sectionIndex) in productSections"
    :key="section.key"
    class="catalog-section"
    :aria-labelledby="`catalog-section-${section.key}`"
  >
    <template v-for="(list, listIndex) in section.lists" :key="list.key">
      <h1
        v-if="sectionIndex === 0 && listIndex === 0"
        :id="`catalog-section-${section.key}`"
      >
        {{ list.title || section.title }}
      </h1>
      <h2
        v-else
        :id="listIndex === 0 ? `catalog-section-${section.key}` : undefined"
      >
        {{ list.title || section.title }}
      </h2>

      <div class="product-grid">
        <ProductCard v-for="product in list.products" :key="`${list.key}-${product.id}-${listIndex}`" :product="product" />
      </div>
    </template>
  </section>
</template>
