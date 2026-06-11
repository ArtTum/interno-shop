<script setup lang="ts">
const { copy, shopSettings } = useCatalog()

const contactPhone = computed(() => String(shopSettings.value.contactPhone || ''))
const contactEmail = computed(() => String(shopSettings.value.contactEmail || ''))
const contactAddress = computed(() => String(shopSettings.value.contactAddress || ''))
const contactMapUrl = computed(() => String(shopSettings.value.contactMapUrl || '#'))
const contactPhoneHref = computed(() => contactPhone.value.replace(/\s/g, ''))
</script>

<template>
  <section class="contact-page" aria-labelledby="contact-title">
    <div class="contact-hero">
      <p>{{ copy.contactHeroKicker }}</p>
      <h1 id="contact-title">{{ copy.contactHeroTitle }}</h1>
      <span>{{ copy.contactHeroText }}</span>
    </div>

    <div class="contact-layout">
      <section class="contact-card contact-info-card" :aria-label="copy.contactInfoTitle">
        <h2>{{ copy.contactInfoTitle }}</h2>
        <div class="contact-info-list">
          <a :href="`tel:${contactPhoneHref}`"><span>{{ copy.contactPhone }}</span><strong>{{ contactPhone }}</strong></a>
          <a :href="`mailto:${contactEmail}`"><span>{{ copy.contactEmail }}</span><strong>{{ contactEmail }}</strong></a>
          <a :href="contactMapUrl" target="_blank" rel="noopener noreferrer"><span>{{ copy.contactAddress }}</span><strong>{{ contactAddress }}</strong></a>
        </div>

        <div class="contact-hours">
          <span>{{ copy.contactHours }}</span>
          <strong>{{ copy.contactHoursValue }}</strong>
        </div>
      </section>

      <section class="contact-card contact-form-card" :aria-label="copy.contactFormTitle">
        <h2>{{ copy.contactFormTitle }}</h2>
        <form class="contact-form" @submit.prevent>
          <label><span>{{ copy.firstName }}</span><input type="text" :placeholder="copy.contactNamePlaceholder" /></label>
          <label><span>{{ copy.contactPhone }}</span><input type="tel" :placeholder="copy.contactPhonePlaceholder" /></label>
          <label class="contact-form-wide"><span>{{ copy.contactMessage }}</span><textarea rows="5" :placeholder="copy.contactMessagePlaceholder" /></label>
          <button type="submit">{{ copy.contactSend }}</button>
        </form>
      </section>
    </div>
  </section>
</template>
