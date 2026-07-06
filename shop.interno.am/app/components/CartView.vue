<script setup lang="ts">
import { reactive, ref, watch } from 'vue'

const {
  addToCart,
  cartProducts,
  cartRecommendations,
  cartTotal,
  clearCart,
  copy,
  currentLanguageCode,
  localizedPath,
  productPath,
  removeFromCart,
  searchCraftsmen,
  selectedCraftsman: storedCraftsman,
  selectCraftsmanForCheckout,
  submitOrder,
  updateCartQuantity
} = useCatalog()

const isCheckoutOpen = ref(false)
const isCraftsmanPromptOpen = ref(false)
const router = useRouter()
const route = useRoute()
type Craftsman = { id: number | null, code: string, name: string, image?: string | null, phone?: string | null, work_region?: string | null, work_city?: string | null, work_field?: string | null, has_whatsapp?: boolean, has_viber?: boolean }
const craftsmenSuggestions = ref<Craftsman[]>([])
const selectedCraftsman = ref<Craftsman | null>(storedCraftsman.value)
const isCraftsmanLoading = ref(false)
let craftsmanSearchTimer: ReturnType<typeof setTimeout> | null = null

const checkoutForm = reactive({
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  masterCode: '',
  craftsmanName: '',
  address: ''
})

const checkoutErrors = reactive({
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  masterCode: '',
  craftsmanName: '',
  address: ''
})

const requiredCheckoutFields = ['firstName', 'lastName', 'phone', 'email', 'address'] as const

function validateCheckout() {
  let isValid = true

  Object.keys(checkoutErrors).forEach((key) => {
    checkoutErrors[key as keyof typeof checkoutErrors] = ''
  })

  requiredCheckoutFields.forEach((field) => {
    checkoutErrors[field] = checkoutForm[field].trim() ? '' : copy.value.requiredField

    if (checkoutErrors[field]) {
      isValid = false
    }
  })

  if (checkoutForm.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(checkoutForm.email.trim())) {
    checkoutErrors.email = copy.value.emailError
    isValid = false
  }

  return isValid
}

function clearCheckoutError(field: keyof typeof checkoutForm) {
  checkoutErrors[field] = ''
}

function cartOptionLabel(option: { key: string, label: string }) {
  const labels: Record<string, string> = {
    height: copy.value.optionLength,
    length: copy.value.optionLength,
    unit: copy.value.optionUnitLong,
    size: copy.value.optionSize,
    power: copy.value.optionPower
  }

  return labels[option.key] || option.label
}

function applyCraftsman(craftsman: Craftsman | null) {
  selectedCraftsman.value = craftsman
  checkoutForm.masterCode = craftsman?.code || ''
  checkoutForm.craftsmanName = craftsman?.name || ''
}

function selectCraftsman(craftsman: Craftsman) {
  applyCraftsman(craftsman)
  selectCraftsmanForCheckout(craftsman)
  craftsmenSuggestions.value = []
}

function searchCraftsman(field: 'code' | 'name') {
  selectedCraftsman.value = null
  selectCraftsmanForCheckout(null)

  if (craftsmanSearchTimer) {
    clearTimeout(craftsmanSearchTimer)
  }

  const query = field === 'code' ? checkoutForm.masterCode : checkoutForm.craftsmanName

  if (!query.trim()) {
    craftsmenSuggestions.value = []
    return
  }

  craftsmanSearchTimer = setTimeout(async () => {
    isCraftsmanLoading.value = true

    try {
      const results = await searchCraftsmen(query)
      craftsmenSuggestions.value = results

      const normalizedQuery = query.trim().toLowerCase()
      const exactMatch = results.find((craftsman) => {
        return field === 'code'
          ? craftsman.code.toLowerCase() === normalizedQuery
          : craftsman.name.toLowerCase() === normalizedQuery
      })

      if (exactMatch) {
        selectCraftsman(exactMatch)
      }
    } finally {
      isCraftsmanLoading.value = false
    }
  }, 250)
}

async function submitCheckout() {
  if (!validateCheckout()) {
    return
  }

  const customer = {
    firstName: checkoutForm.firstName,
    lastName: checkoutForm.lastName,
    phone: checkoutForm.phone,
    email: checkoutForm.email,
    masterCode: checkoutForm.masterCode,
    address: checkoutForm.address
  }
  const craftsman = selectedCraftsman.value || (checkoutForm.masterCode.trim() || checkoutForm.craftsmanName.trim()
    ? { id: null, code: checkoutForm.masterCode.trim(), name: checkoutForm.craftsmanName.trim() }
    : null)

  await submitOrder(customer, craftsman)
  isCheckoutOpen.value = false
  router.push(localizedPath('/checkout-success'))
}

function continueWithoutCraftsman() {
  selectCraftsmanForCheckout(null)
  isCraftsmanPromptOpen.value = false
  isCheckoutOpen.value = true
}

function openCraftsmanPrompt() {
  isCraftsmanPromptOpen.value = true
}

function goToCraftsmen() {
  if (!cartProducts.value.length) {
    return
  }

  isCraftsmanPromptOpen.value = false
  router.push(localizedPath('/craftsmen'))
}

watch(storedCraftsman, (craftsman) => {
  applyCraftsman(craftsman)
}, { immediate: true })

watch(() => route.query.checkout, (checkout) => {
  if (checkout === '1' && cartProducts.value.length) {
    isCheckoutOpen.value = true
  }
}, { immediate: true })
</script>

<template>
  <section class="cart-page" aria-labelledby="cart-title">
    <div class="cart-main">
      <div class="cart-title-row">
        <h1 id="cart-title">{{ copy.cartTitle }}</h1>
        <button v-if="cartProducts.length" class="clear-cart" type="button" @click="clearCart">{{ copy.clearCart }}</button>
      </div>

      <div v-if="cartProducts.length" class="cart-items">
        <article v-for="item in cartProducts" :key="item.key" class="cart-item">
          <button class="remove-cart-item" type="button" :aria-label="copy.removeItem" @click="removeFromCart(item.key)">
            <svg width="15" height="17" viewBox="0 0 15 17" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M9 2.5C9 1.67157 8.32843 1 7.5 1C6.67157 1 6 1.67157 6 2.5H5C5 1.11929 6.11929 0 7.5 0C8.88071 0 10 1.11929 10 2.5H14.5C14.7761 2.5 15 2.72386 15 3C15 3.27614 14.7761 3.5 14.5 3.5H13.946L12.6499 14.7292C12.5335 15.7384 11.679 16.5 10.6631 16.5H4.33688C3.321 16.5 2.4665 15.7384 2.35006 14.7292L1.053 3.5H0.5C0.25454 3.5 0.0503916 3.32312 0.00805569 3.08988L0 3C0 2.72386 0.223858 2.5 0.5 2.5H9ZM12.938 3.5H2.061L3.34347 14.6146C3.40169 15.1192 3.82894 15.5 4.33688 15.5H10.6631C11.1711 15.5 11.5983 15.1192 11.6565 14.6146L12.938 3.5ZM6 6C6.24546 6 6.44961 6.15477 6.49194 6.35886L6.5 6.4375V12.5625C6.5 12.8041 6.27614 13 6 13C5.75454 13 5.55039 12.8452 5.50806 12.6411L5.5 12.5625V6.4375C5.5 6.19588 5.72386 6 6 6ZM9 6C9.24546 6 9.44961 6.15477 9.49194 6.35886L9.5 6.4375V12.5625C9.5 12.8041 9.27614 13 9 13C8.75454 13 8.55039 12.8452 8.50806 12.6411L8.5 12.5625V6.4375C8.5 6.19588 8.72386 6 9 6Z" fill="#E64E48"/>
            </svg>
          </button>

          <NuxtLink class="cart-item-media" :to="productPath(item.product)">
            <img :src="item.product.image" :alt="item.product.title[currentLanguageCode]" />
          </NuxtLink>

          <div class="cart-item-info">
            <h2>
              <NuxtLink class="cart-product-title-link" :to="productPath(item.product)">
                {{ item.product.title[currentLanguageCode] }}
              </NuxtLink>
            </h2>

            <dl class="cart-item-options">
              <div><dt>{{ copy.optionCode }}</dt><dd>{{ item.product.options?.code || '—' }}</dd></div>
              <div
                v-for="option in item.selectedOptions || []"
                :key="`${item.key}-${option.key}`"
              >
                <dt>{{ cartOptionLabel(option) }}</dt>
                <dd>{{ option.value || '—' }}</dd>
              </div>
              <div v-if="!item.selectedOptions?.length && item.selectedOptionLabel">
                <dt>{{ copy.selectedOptions }}</dt>
                <dd>{{ item.selectedOptionLabel }}</dd>
              </div>
              <div><dt>{{ copy.optionType }}</dt><dd>{{ item.product.options?.type || '—' }}</dd></div>
              <div>
                <dt>{{ copy.optionColor }}</dt>
                <dd>
                  <span
                    class="cart-color-dot"
                    :style="{ background: item.color?.value || item.product.options?.color || undefined }"
                    :title="item.color?.name || copy.optionColor"
                  />
                  <span v-if="item.color?.name" class="cart-color-name">{{ item.color.name }}</span>
                </dd>
              </div>
              <div><dt>{{ copy.optionMaterial }}</dt><dd>{{ item.product.options?.material || '—' }}</dd></div>
            </dl>
          </div>

          <div class="cart-item-actions">
            <div class="quantity-control" aria-label="Quantity">
              <button type="button" @click="updateCartQuantity(item.key, -1)">&minus;</button>
              <span>{{ item.quantity }}</span>
              <button type="button" @click="updateCartQuantity(item.key, 1)">+</button>
            </div>
            <div class="cart-line-price">
              <small>{{ copy.linePrice }}</small>
              <strong>{{ (item.effectivePrice != null ? item.effectivePrice : Number(item.product.price)) * item.quantity }} <span aria-hidden="true">&#1423;</span></strong>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="empty-cart">
        <h2>{{ copy.emptyCartTitle }}</h2>
        <NuxtLink :to="localizedPath('/')">{{ copy.home }}</NuxtLink>
      </div>
    </div>

    <aside class="cart-side">
      <div class="cart-total-card">
        <div>
          <span style="margin-right: 10px;">{{ copy.paymentDue }}</span>
          <strong>{{ cartTotal }} <span aria-hidden="true">&#1423;</span></strong>
        </div>
        <button type="button" @click="openCraftsmanPrompt">{{ copy.pay }}</button>
      </div>

      <div class="cart-recommendations">
        <h2>{{ copy.recommendationsTitle }}</h2>
        <article v-for="product in cartRecommendations" :key="product.id" class="cart-recommendation">
          <NuxtLink class="cart-recommendation-media" :to="productPath(product)">
            <img :src="product.image" :alt="product.title[currentLanguageCode]" />
          </NuxtLink>
          <div>
            <h3>
              <NuxtLink class="cart-recommendation-title-link" :to="productPath(product)">
                {{ product.title[currentLanguageCode] }}
              </NuxtLink>
            </h3>
            <p>{{ product.price }} <span aria-hidden="true">&#1423;</span></p>
            <button type="button" :disabled="product.isTemporarilyUnavailable" @click="addToCart(product)">
              {{ copy.addToCartLong }}
            </button>
          </div>
        </article>
      </div>
    </aside>

    <Teleport to="body">
      <div v-if="isCraftsmanPromptOpen" class="craftsman-prompt-modal" role="dialog" aria-modal="true" aria-labelledby="craftsman-prompt-title">
        <button class="craftsman-prompt-backdrop" type="button" :aria-label="copy.closeDialog" @click="isCraftsmanPromptOpen = false" />
        <section class="craftsman-prompt-dialog">
          <h2 id="craftsman-prompt-title">{{ copy.selectCraftsmanQuestion }}</h2>
          <div class="craftsman-prompt-actions">
            <button type="button" @click="goToCraftsmen">{{ copy.yes }}</button>
            <button type="button" @click="continueWithoutCraftsman">{{ copy.no }}</button>
          </div>
        </section>
      </div>

      <div v-if="isCheckoutOpen" class="checkout-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title">
        <button class="checkout-backdrop" type="button" :aria-label="copy.closeDialog" @click="isCheckoutOpen = false" />

        <section class="checkout-dialog">
          <button class="checkout-close" type="button" :aria-label="copy.closeDialog" @click="isCheckoutOpen = false">×</button>
          <h2 id="checkout-title">{{ copy.checkoutTitle }}</h2>

          <form class="checkout-form" novalidate @submit.prevent="submitCheckout">
            <label :class="{ 'has-error': checkoutErrors.firstName }">
              <span>{{ copy.firstName }}</span>
              <input v-model="checkoutForm.firstName" type="text" :placeholder="copy.firstNamePlaceholder" required @input="clearCheckoutError('firstName')" />
              <small v-if="checkoutErrors.firstName">{{ checkoutErrors.firstName }}</small>
            </label>

            <label :class="{ 'has-error': checkoutErrors.lastName }">
              <span>{{ copy.lastName }}</span>
              <input v-model="checkoutForm.lastName" type="text" :placeholder="copy.lastName" required @input="clearCheckoutError('lastName')" />
              <small v-if="checkoutErrors.lastName">{{ checkoutErrors.lastName }}</small>
            </label>

            <label :class="{ 'has-error': checkoutErrors.phone }">
              <span>{{ copy.phone }}</span>
              <input v-model="checkoutForm.phone" type="tel" :placeholder="copy.phonePlaceholder" required @input="clearCheckoutError('phone')" />
              <small v-if="checkoutErrors.phone">{{ checkoutErrors.phone }}</small>
            </label>

            <label :class="{ 'has-error': checkoutErrors.email }">
              <span>{{ copy.email }}</span>
              <input v-model="checkoutForm.email" type="email" :placeholder="copy.emailPlaceholder" required @input="clearCheckoutError('email')" />
              <small v-if="checkoutErrors.email">{{ checkoutErrors.email }}</small>
            </label>

            <label>
              <span>{{ copy.craftsmanCode }}</span>
              <input v-model="checkoutForm.masterCode" type="text" :placeholder="copy.craftsmanCodePlaceholder" autocomplete="off" @input="searchCraftsman('code')" />
            </label>

            <label class="checkout-autocomplete">
              <span>{{ copy.craftsmanName }}</span>
              <input v-model="checkoutForm.craftsmanName" type="text" :placeholder="copy.craftsmanNamePlaceholder" autocomplete="off" @input="searchCraftsman('name')" />
              <small v-if="isCraftsmanLoading" class="checkout-loading">...</small>
              <div v-if="craftsmenSuggestions.length" class="craftsman-suggestions">
                <button v-for="craftsman in craftsmenSuggestions" :key="`${craftsman.id}-${craftsman.code}`" type="button" @click="selectCraftsman(craftsman)">
                  <strong>{{ craftsman.code }}</strong>
                  <span>{{ craftsman.name }}</span>
                </button>
              </div>
            </label>

            <p class="checkout-note">
              {{ copy.checkoutDeliveryNote }}
            </p>

            <label class="checkout-wide" :class="{ 'has-error': checkoutErrors.address }">
              <span>{{ copy.address }}</span>
              <input v-model="checkoutForm.address" type="text" :placeholder="copy.addressPlaceholder" required @input="clearCheckoutError('address')" />
              <small v-if="checkoutErrors.address">{{ checkoutErrors.address }}</small>
            </label>

            <div class="checkout-footer">
              <span>{{ copy.paymentDueAmount }} <strong>{{ cartTotal }} <span aria-hidden="true">&#1423;</span></strong></span>
              <button type="submit">{{ copy.pay }}</button>
            </div>
          </form>
        </section>
      </div>
    </Teleport>
  </section>
</template>
