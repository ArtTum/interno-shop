<script setup lang="ts">
const { copy } = useCatalog()
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
          <a href="tel:+37410234567"><span>{{ copy.contactPhone }}</span><strong>+374 10 234 567</strong></a>
          <a href="mailto:info@interino.am"><span>{{ copy.contactEmail }}</span><strong>info@interino.am</strong></a>
          <a href="https://maps.google.com/?q=Yerevan" target="_blank" rel="noopener noreferrer"><span>{{ copy.contactAddress }}</span><strong>123 Ceiling Ave, Yerevan</strong></a>
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
