<script setup lang="ts">
const { copy, localizedPath, scrollProductSlider, searchRecommendations, searchResults } = useCatalog()
</script>

<template>
  <section class="search-page" aria-labelledby="search-title">
    <section v-if="searchResults.length" class="catalog-section search-results-section">
      <h1 id="search-title">{{ copy.searchResultsTitle }}</h1>
      <div class="product-grid search-results-grid">
        <ProductCard v-for="product in searchResults" :key="product.id" :product="product" />
      </div>
    </section>

    <template v-else>
      <section class="search-empty" aria-labelledby="search-title">
        <h1 id="search-title">{{ copy.searchEmptyTitle }}</h1>
        <NuxtLink :to="localizedPath('/')">{{ copy.searchHomeLink }}</NuxtLink>
      </section>

      <section class="related-section" aria-labelledby="search-recommendations-title">
        <div class="slider-head">
          <h2 id="search-recommendations-title">{{ copy.searchRecommendations }}</h2>
          <div class="slider-controls" :aria-label="copy.sliderControls">
            <button type="button" :aria-label="copy.previousProducts" @click="scrollProductSlider('search-recommendations-slider', 'previous')">
              <span class="slider-chevron is-previous" aria-hidden="true" />
            </button>
            <button type="button" :aria-label="copy.nextProducts" @click="scrollProductSlider('search-recommendations-slider', 'next')">
              <span class="slider-chevron is-next" aria-hidden="true" />
            </button>
          </div>
        </div>
        <div id="search-recommendations-slider" class="product-grid related-grid product-slider">
          <ProductCard v-for="product in searchRecommendations" :key="product.id" :product="product" />
        </div>
      </section>
    </template>
  </section>
</template>
