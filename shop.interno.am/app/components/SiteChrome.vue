<script setup lang="ts">
import { ref } from 'vue'

withDefaults(defineProps<{ wide?: boolean, showCatalogNav?: boolean }>(), {
  wide: false,
  showCatalogNav: true
})

const {
  activeMenuKey,
  categories,
  copy,
  currentCategoryGroup,
  currentCategoryRoute,
  currentLanguage,
  currentLanguageCode,
  isCategoryPage,
  languages,
  localizedPath,
  menuGroups,
  openCategory,
  scrollProductSlider,
  searchTerm,
  selectLanguage,
  socialLinks,
  submitSearch,
  toggleMenuGroup,
  cartCount,
  recentlyAddedProductId
} = useCatalog()

const isMenuOpen = ref(false)
const isLanguageOpen = ref(false)

function chooseLanguage(language: (typeof languages)[number]) {
  selectLanguage(language)
  isLanguageOpen.value = false
}

function openMobileCategory(groupKey: string, childIndex?: number) {
  openCategory(groupKey, childIndex)
  isMenuOpen.value = false
}
</script>

<template>
  <NuxtRouteAnnouncer />

  <div class="site-shell" :class="{ 'menu-is-open': isMenuOpen }">
    <header class="topbar">
      <NuxtLink class="brand" :to="localizedPath('/')" aria-label="Interino homepage">
        <img src="/assets/brand/logo.png" alt="Interino" />
      </NuxtLink>

      <nav class="desktop-nav" aria-label="Primary">
        <NuxtLink :to="localizedPath('/')">{{ copy.home }}</NuxtLink>
        <NuxtLink :to="localizedPath('/contact')">{{ copy.contact }}</NuxtLink>
      </nav>

      <button
        class="mobile-menu"
        type="button"
        :aria-label="copy.openMenu"
        :aria-expanded="isMenuOpen"
        aria-controls="mobile-drawer"
        @click="isMenuOpen = true"
      >
        <span />
        <span />
        <span />
      </button>

      <form class="search" role="search" @submit.prevent="submitSearch">
        <img src="/assets/icons/search.svg" alt="" aria-hidden="true" />
        <input v-model="searchTerm" type="search" :placeholder="copy.search" />
      </form>

      <div class="toolbar">
        <NuxtLink class="cart-button" :class="{ 'is-pulsing': recentlyAddedProductId }" :to="localizedPath('/cart')" :aria-label="copy.cart">
          <img src="/assets/icons/cart.svg" alt="" aria-hidden="true" />
          <span v-if="cartCount" class="cart-count">{{ cartCount }}</span>
        </NuxtLink>
        <span v-if="recentlyAddedProductId" class="cart-toast">{{ copy.added }}</span>
        <div class="language-picker">
          <button
            class="language-trigger"
            type="button"
            aria-label="Language menu"
            :aria-expanded="isLanguageOpen"
            @click.stop="isLanguageOpen = !isLanguageOpen"
          >
            <img class="language-flag" :src="currentLanguage.icon" :alt="currentLanguage.label" />
            <span class="language-chevron" :class="{ 'is-open': isLanguageOpen }" aria-hidden="true" />
          </button>

          <div v-if="isLanguageOpen" class="language-menu" @click.stop>
            <button
              v-for="language in languages"
              :key="language.code"
              :class="{ 'is-selected': currentLanguage.code === language.code }"
              type="button"
              @click="chooseLanguage(language)"
            >
              <img class="language-flag" :src="language.icon" :alt="language.label" />
              {{ language.label }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <div v-if="isLanguageOpen" class="language-backdrop" aria-hidden="true" @click="isLanguageOpen = false" />
    <div v-if="isMenuOpen" class="drawer-backdrop" aria-hidden="true" @click="isMenuOpen = false" />

    <aside id="mobile-drawer" class="mobile-drawer" :aria-hidden="!isMenuOpen">
      <div class="drawer-head">
        <strong>{{ copy.menu }}</strong>
        <button type="button" :aria-label="copy.closeMenu" @click="isMenuOpen = false">×</button>
      </div>

      <nav class="drawer-nav" aria-label="Mobile menu">
        <NuxtLink :to="localizedPath('/')" @click="isMenuOpen = false">{{ copy.home }}</NuxtLink>
        <div v-for="group in menuGroups" :key="group.key" class="menu-group">
          <button class="menu-trigger" :class="{ 'is-active': currentCategoryGroup?.key === group.key }" type="button" @click="openMobileCategory(group.key)">
            <span class="sidebar-icon">⌘</span>
            {{ group.title[currentLanguageCode] }}
            <span class="chevron" :class="{ 'is-open': activeMenuKey === group.key }" aria-hidden="true" @click.stop="toggleMenuGroup(group.key)" />
          </button>

          <div v-if="activeMenuKey === group.key" class="submenu">
            <button
              v-for="(child, childIndex) in group.children[currentLanguageCode]"
              :key="child"
              :class="{ 'is-active': currentCategoryGroup?.key === group.key && currentCategoryRoute?.childIndex === childIndex }"
              type="button"
              @click="openMobileCategory(group.key, childIndex)"
            >
              {{ child }}
            </button>
          </div>
        </div>
        <NuxtLink :to="localizedPath('/contact')" @click="isMenuOpen = false">{{ copy.contact }}</NuxtLink>
      </nav>
    </aside>

    <div v-if="showCatalogNav && !isCategoryPage" class="mobile-categories" aria-label="Categories">
      <button v-for="(category, categoryIndex) in categories" :key="category" type="button" @click="openCategory(menuGroups[categoryIndex].key)">
        <span class="chip-icon">⌘</span>
        {{ category }}
      </button>
    </div>

    <div class="layout" :class="{ 'is-product-page': wide }">
      <aside v-if="showCatalogNav" class="sidebar" aria-label="Product categories">
        <div v-for="group in menuGroups" :key="group.key" class="menu-group">
          <button class="menu-trigger" :class="{ 'is-active': currentCategoryGroup?.key === group.key }" type="button" @click="openCategory(group.key)">
            <span class="sidebar-icon">⌘</span>
            {{ group.title[currentLanguageCode] }}
            <span class="chevron" :class="{ 'is-open': activeMenuKey === group.key }" aria-hidden="true" @click.stop="toggleMenuGroup(group.key)" />
          </button>

          <div v-if="activeMenuKey === group.key" class="submenu">
            <button
              v-for="(child, childIndex) in group.children[currentLanguageCode]"
              :key="child"
              :class="{ 'is-active': currentCategoryGroup?.key === group.key && currentCategoryRoute?.childIndex === childIndex }"
              type="button"
              @click="openCategory(group.key, childIndex)"
            >
              {{ child }}
            </button>
          </div>
        </div>
      </aside>

      <main class="content">
        <slot />
      </main>
    </div>

    <footer class="footer">
      <p>123 Ceiling Ave, Yerevan · +374 10 234 567</p>
      <nav class="social-links" aria-label="Social links">
        <template v-for="(link, index) in socialLinks" :key="link.label">
          <a v-if="link.external" :href="link.href" target="_blank" rel="noopener noreferrer">{{ link.label }}</a>
          <NuxtLink v-else :to="localizedPath(link.href)">{{ link.label }}</NuxtLink>
          <span v-if="index < socialLinks.length - 1" aria-hidden="true">&middot;</span>
        </template>
      </nav>
    </footer>
  </div>
</template>
