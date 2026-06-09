<script setup lang="ts">
import { reactive, ref } from 'vue'

const {
  addToCart,
  cartProducts,
  cartRecommendations,
  cartTotal,
  clearCart,
  copy,
  currentLanguageCode,
  localizedPath,
  removeFromCart,
  submitOrder,
  updateCartQuantity
} = useCatalog()

const isCheckoutOpen = ref(false)
const router = useRouter()

const checkoutForm = reactive({
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  masterCode: '',
  address: ''
})

const checkoutErrors = reactive({
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  masterCode: '',
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

async function submitCheckout() {
  if (!validateCheckout()) {
    return
  }

  await submitOrder({ ...checkoutForm })
  isCheckoutOpen.value = false
  router.push(localizedPath('/checkout-success'))
}
</script>

<template>
  <section class="cart-page" aria-labelledby="cart-title">
    <div class="cart-main">
      <div class="cart-title-row">
        <h1 id="cart-title">{{ copy.cartTitle }}</h1>
        <button v-if="cartProducts.length" class="clear-cart" type="button" @click="clearCart">{{ copy.clearCart }}</button>
      </div>

      <div v-if="cartProducts.length" class="cart-items">
        <article v-for="item in cartProducts" :key="item.productId" class="cart-item">
          <button class="remove-cart-item" type="button" aria-label="Remove item" @click="removeFromCart(item.productId)">
            <svg width="15" height="17" viewBox="0 0 15 17" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M9 2.5C9 1.67157 8.32843 1 7.5 1C6.67157 1 6 1.67157 6 2.5H5C5 1.11929 6.11929 0 7.5 0C8.88071 0 10 1.11929 10 2.5H14.5C14.7761 2.5 15 2.72386 15 3C15 3.27614 14.7761 3.5 14.5 3.5H13.946L12.6499 14.7292C12.5335 15.7384 11.679 16.5 10.6631 16.5H4.33688C3.321 16.5 2.4665 15.7384 2.35006 14.7292L1.053 3.5H0.5C0.25454 3.5 0.0503916 3.32312 0.00805569 3.08988L0 3C0 2.72386 0.223858 2.5 0.5 2.5H9ZM12.938 3.5H2.061L3.34347 14.6146C3.40169 15.1192 3.82894 15.5 4.33688 15.5H10.6631C11.1711 15.5 11.5983 15.1192 11.6565 14.6146L12.938 3.5ZM6 6C6.24546 6 6.44961 6.15477 6.49194 6.35886L6.5 6.4375V12.5625C6.5 12.8041 6.27614 13 6 13C5.75454 13 5.55039 12.8452 5.50806 12.6411L5.5 12.5625V6.4375C5.5 6.19588 5.72386 6 6 6ZM9 6C9.24546 6 9.44961 6.15477 9.49194 6.35886L9.5 6.4375V12.5625C9.5 12.8041 9.27614 13 9 13C8.75454 13 8.55039 12.8452 8.50806 12.6411L8.5 12.5625V6.4375C8.5 6.19588 8.72386 6 9 6Z" fill="#E64E48"/>
            </svg>
          </button>

          <div class="cart-item-media">
            <img :src="item.product.image" :alt="item.product.title[currentLanguageCode]" />
          </div>

          <div class="cart-item-info">
            <h2>{{ item.product.title[currentLanguageCode] }}</h2>
            <dl class="cart-item-options">
              <div><dt>{{ copy.optionType }}</dt><dd>{{ item.product.options?.type || copy.optionTypeName }}</dd></div>
              <div><dt>{{ copy.optionUnit }}</dt><dd>{{ item.product.options?.unit || '111' }}</dd></div>
              <div><dt>{{ copy.optionCode }}</dt><dd>{{ item.product.options?.code || '111' }}</dd></div>
              <div><dt>{{ copy.optionColor }}</dt><dd><span class="cart-color-dot" :style="{ background: item.product.options?.color || undefined }" /></dd></div>
              <div><dt>{{ copy.optionMaterial }}</dt><dd>{{ item.product.options?.material || copy.materialName }}</dd></div>
            </dl>
          </div>

          <div class="cart-item-actions">
            <div class="quantity-control" aria-label="Quantity">
              <button type="button" @click="updateCartQuantity(item.productId, -1)">&minus;</button>
              <span>{{ item.quantity }}</span>
              <button type="button" @click="updateCartQuantity(item.productId, 1)">+</button>
            </div>
            <div class="cart-line-price">
              <small>{{ copy.linePrice }}</small>
              <strong>{{ Number(item.product.price) * item.quantity }} <span aria-hidden="true">&#1423;</span></strong>
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
        <button type="button" @click="isCheckoutOpen = true">{{ copy.pay }}</button>
      </div>

      <div class="cart-recommendations">
        <h2>{{ copy.recommendationsTitle }}</h2>
        <article v-for="product in cartRecommendations" :key="product.id" class="cart-recommendation">
          <img :src="product.image" :alt="product.title[currentLanguageCode]" />
          <div>
            <h3>{{ product.title[currentLanguageCode] }}</h3>
            <p>{{ product.price }} <span aria-hidden="true">&#1423;</span></p>
            <button type="button" @click="addToCart(product)">{{ copy.addToCartLong }}</button>
          </div>
        </article>
      </div>
    </aside>

    <Teleport to="body">
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
              <input v-model="checkoutForm.phone" type="tel" placeholder="+374" required @input="clearCheckoutError('phone')" />
              <small v-if="checkoutErrors.phone">{{ checkoutErrors.phone }}</small>
            </label>

            <label :class="{ 'has-error': checkoutErrors.email }">
              <span>{{ copy.email }}</span>
              <input v-model="checkoutForm.email" type="email" placeholder="example@mail.com" required @input="clearCheckoutError('email')" />
              <small v-if="checkoutErrors.email">{{ checkoutErrors.email }}</small>
            </label>

            <label>
              <span>{{ copy.masterCode }}</span>
              <input v-model="checkoutForm.masterCode" type="text" :placeholder="copy.masterCode" />
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
