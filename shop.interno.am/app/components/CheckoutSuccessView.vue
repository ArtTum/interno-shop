<script setup lang="ts">
const { copy, localizedPath } = useCatalog()
</script>

<template>
  <section class="checkout-success-page" aria-labelledby="checkout-success-title">
    <div class="checkout-success-card">
      <h1 id="checkout-success-title">{{ copy.checkoutSuccessTitle }}</h1>
      <NuxtLink :to="localizedPath('/')">{{ copy.checkoutSuccessHome }}</NuxtLink>
    </div>
  </section>
</template>
