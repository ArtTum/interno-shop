<script setup lang="ts">
import { computed } from 'vue'

const { copy, currentLanguageCode, localizedPath } = useCatalog()

const privacyContent = {
  hy: {
    kicker: 'Գաղտնիության քաղաքականություն',
    title: 'Ձեր տվյալների անվտանգ օգտագործումը մեզ համար կարևոր է',
    intro: 'Այս էջում ներկայացված է, թե ինչ տվյալներ կարող ենք հավաքել, ինչպես ենք օգտագործում դրանք և ինչ իրավունքներ ունեք դուք։',
    badgeTitle: 'Անվտանգ պատվերներ',
    badgeText: 'Պատվերի տվյալները մշակվում են միայն սպասարկման համար',
    summaryLabel: 'Կարճ ամփոփում',
    summaryTitle: 'Մենք օգտագործում ենք միայն անհրաժեշտ տվյալները',
    summaryText: 'Տվյալները կիրառվում են պատվերների մշակման, կապ հաստատելու և սպասարկման որակը բարելավելու համար։',
    checklist: ['Չենք վաճառում տվյալները', 'Պահում ենք անհրաժեշտ չափով', 'Կապի տվյալները միայն պատվերի համար են'],
    updated: 'Վերջին թարմացում',
    summaryAria: 'Կարճ ամփոփում',
    checklistAria: 'Հիմնական սկզբունքներ',
    sections: [
      { index: '01', icon: 'ID', title: 'Ինչ տվյալներ ենք հավաքում', text: 'Կարող ենք հավաքել ձեր անունը, հեռախոսահամարը, էլ. հասցեն, առաքման կամ կապի հասցեն և պատվերի հետ կապված նշումները։' },
      { index: '02', icon: '↗', title: 'Ինչպես ենք օգտագործում տվյալները', text: 'Տվյալներն օգտագործվում են պատվերը հաստատելու, ապրանքի հասանելիությունը ճշտելու, առաքումը կազմակերպելու և անհրաժեշտության դեպքում ձեզ հետ կապվելու համար։' },
      { index: '03', icon: '↔', title: 'Տվյալների փոխանցում', text: 'Մենք չենք վաճառում ձեր անձնական տվյալները։ Դրանք կարող են փոխանցվել միայն այն գործընկերներին, որոնք ներգրավված են պատվերի կատարման կամ առաքման գործընթացում։' },
      { index: '04', icon: '⌁', title: 'Պահպանում և անվտանգություն', text: 'Տվյալները պահվում են այնքան ժամանակ, որքան անհրաժեշտ է պատվերի սպասարկման և հաշվառման համար։ Մենք կիրառում ենք ողջամիտ միջոցներ դրանք պաշտպանելու համար։' },
      { index: '05', icon: '?', title: 'Ձեր իրավունքները', text: 'Դուք կարող եք կապ հաստատել մեզ հետ՝ ձեր տվյալները ճշտելու, թարմացնելու կամ հեռացնելու խնդրանքով։ Հարցերի դեպքում կարող եք գրել կամ զանգահարել մեզ։' }
    ]
  },
  en: {
    kicker: 'Privacy policy',
    title: 'Safe use of your data is important to us',
    intro: 'This page explains what data we may collect, how we use it, and what rights you have.',
    badgeTitle: 'Secure orders',
    badgeText: 'Order data is processed only for service purposes',
    summaryLabel: 'Short summary',
    summaryTitle: 'We use only the necessary data',
    summaryText: 'Data is used to process orders, contact customers, and improve service quality.',
    checklist: ['We do not sell data', 'We keep data only as needed', 'Contact details are used only for orders'],
    updated: 'Last updated',
    summaryAria: 'Short summary',
    checklistAria: 'Main principles',
    sections: [
      { index: '01', icon: 'ID', title: 'What data we collect', text: 'We may collect your name, phone number, email address, delivery or contact address, and notes related to the order.' },
      { index: '02', icon: '↗', title: 'How we use data', text: 'Data is used to confirm the order, check product availability, organize delivery, and contact you when needed.' },
      { index: '03', icon: '↔', title: 'Data sharing', text: 'We do not sell your personal data. It may be shared only with partners involved in order fulfillment or delivery.' },
      { index: '04', icon: '⌁', title: 'Storage and security', text: 'Data is kept for as long as needed for order service and accounting. We use reasonable measures to protect it.' },
      { index: '05', icon: '?', title: 'Your rights', text: 'You can contact us to request correction, update, or deletion of your data. If you have questions, you can write or call us.' }
    ]
  },
  ru: {
    kicker: 'Политика конфиденциальности',
    title: 'Безопасное использование ваших данных важно для нас',
    intro: 'На этой странице указано, какие данные мы можем собирать, как используем их и какие права у вас есть.',
    badgeTitle: 'Безопасные заказы',
    badgeText: 'Данные заказа обрабатываются только для обслуживания',
    summaryLabel: 'Краткое резюме',
    summaryTitle: 'Мы используем только необходимые данные',
    summaryText: 'Данные используются для обработки заказов, связи с клиентами и улучшения качества обслуживания.',
    checklist: ['Мы не продаем данные', 'Храним данные только по необходимости', 'Контактные данные используются только для заказа'],
    updated: 'Последнее обновление',
    summaryAria: 'Краткое резюме',
    checklistAria: 'Основные принципы',
    sections: [
      { index: '01', icon: 'ID', title: 'Какие данные мы собираем', text: 'Мы можем собирать ваше имя, номер телефона, адрес электронной почты, адрес доставки или связи, а также примечания к заказу.' },
      { index: '02', icon: '↗', title: 'Как мы используем данные', text: 'Данные используются для подтверждения заказа, проверки наличия товара, организации доставки и связи с вами при необходимости.' },
      { index: '03', icon: '↔', title: 'Передача данных', text: 'Мы не продаем ваши персональные данные. Они могут передаваться только партнерам, участвующим в выполнении или доставке заказа.' },
      { index: '04', icon: '⌁', title: 'Хранение и безопасность', text: 'Данные хранятся столько, сколько необходимо для обслуживания заказа и учета. Мы применяем разумные меры для их защиты.' },
      { index: '05', icon: '?', title: 'Ваши права', text: 'Вы можете связаться с нами, чтобы запросить исправление, обновление или удаление ваших данных. По вопросам можно написать или позвонить нам.' }
    ]
  }
}

const privacyCopy = computed(() => privacyContent[currentLanguageCode.value] ?? privacyContent.hy)
</script>

<template>
  <section class="privacy-page" aria-labelledby="privacy-title">
    <div class="privacy-hero">
      <div>
        <p>{{ privacyCopy.kicker }}</p>
        <h1 id="privacy-title">{{ privacyCopy.title }}</h1>
        <span>{{ privacyCopy.intro }}</span>
      </div>
      <div class="privacy-hero-badge">
        <span class="privacy-badge-icon" aria-hidden="true">✓</span>
        <strong>Interino</strong>
        <span>{{ privacyCopy.badgeTitle }}</span>
        <small>{{ privacyCopy.badgeText }}</small>
      </div>
    </div>

    <div class="privacy-layout">
      <aside class="privacy-summary-card" :aria-label="privacyCopy.summaryAria">
        <span class="privacy-label">{{ privacyCopy.summaryLabel }}</span>
        <h2>{{ privacyCopy.summaryTitle }}</h2>
        <p>{{ privacyCopy.summaryText }}</p>
        <div class="privacy-checklist" :aria-label="privacyCopy.checklistAria">
          <span v-for="item in privacyCopy.checklist" :key="item">{{ item }}</span>
        </div>
        <div class="privacy-update">
          <span>{{ privacyCopy.updated }}</span>
          <strong>03.06.2026</strong>
        </div>
      </aside>

      <div class="privacy-content">
        <article
          v-for="section in privacyCopy.sections"
          :key="section.index"
          class="privacy-card"
          :class="{ 'privacy-card-wide': section.index === '05' }"
        >
          <div class="privacy-card-top">
            <span class="privacy-card-index">{{ section.index }}</span>
            <span class="privacy-card-icon" aria-hidden="true">{{ section.icon }}</span>
          </div>
          <h2>{{ section.title }}</h2>
          <p>{{ section.text }}</p>
          <NuxtLink v-if="section.index === '05'" :to="localizedPath('/contact')">{{ copy.contact }}</NuxtLink>
        </article>
      </div>
    </div>
  </section>
</template>
