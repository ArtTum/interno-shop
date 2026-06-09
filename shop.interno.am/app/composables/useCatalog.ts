import { computed, ref, watch } from 'vue'

type ProductKind = string
type LanguageCode = string

interface SeoEntry {
  title: string
  metaTitle: string
  metaDescription: string
  metaKeywords: string
}

interface Product {
  id: number
  slug?: string
  title: Record<LanguageCode, string>
  meta?: Record<LanguageCode, SeoEntry>
  price: string
  kind: ProductKind
  image: string
  gallery?: string[]
  options?: Record<string, string>
  isNew?: boolean
  status?: boolean
  categoryKey?: string | null
  categoryChildIndex?: number | null
}

interface CartItem {
  productId: number
  quantity: number
}

interface ShopFrontendSettings {
  brandLogo: string
  footerText: string
  contactPhone: string
  contactEmail: string
  contactAddress: string
  contactMapUrl: string
  socialLinks: Array<{ label: string, href: string, external: boolean }>
}

interface ShopFrontendConfig {
  languages: Array<{ code: LanguageCode, label: string, icon: string }>
  menuGroups: Array<{
    key: string
    title: Record<LanguageCode, string>
    meta?: Record<LanguageCode, SeoEntry>
    children: Record<LanguageCode, string[]>
    childMeta?: Record<LanguageCode, SeoEntry[]>
  }>
  translations: Record<LanguageCode, Record<string, string>>
  products: Product[]
  settings: ShopFrontendSettings
  seo: Record<string, Record<LanguageCode, SeoEntry>>
  privacy?: {
    updatedAt?: string
    content?: Record<LanguageCode, any>
  }
}

const menuGroups = [
  {
    key: 'stretch',
    title: {
      hy: 'Ձգվող առաստաղներ',
      en: 'Stretch ceilings',
      ru: 'Натяжные потолки'
    },
    children: {
      hy: ['Պրոֆիլ', 'Ցանց', 'Փայտանյութ', 'Փող'],
      en: ['Profile', 'Mesh', 'Wood material', 'Tube'],
      ru: ['Профиль', 'Сетка', 'Древесина', 'Труба']
    }
  },
  {
    key: 'mdf',
    title: {
      hy: 'ՄԴՖ շրիշակ',
      en: 'MDF skirting',
      ru: 'МДФ плинтус'
    },
    children: {
      hy: ['Սպիտակ', 'Սև', 'Փայտային'],
      en: ['White', 'Black', 'Wood look'],
      ru: ['Белый', 'Черный', 'Под дерево']
    }
  },
  {
    key: 'aluminum',
    title: {
      hy: 'Ալյումինե պրոֆիլ',
      en: 'Aluminum profile',
      ru: 'Алюминиевый профиль'
    },
    children: {
      hy: ['Անկյունային', 'Լուսային', 'Մոնտաժային'],
      en: ['Corner', 'Light line', 'Mounting'],
      ru: ['Угловой', 'Световой', 'Монтажный']
    }
  },
  {
    key: 'lighting',
    title: {
      hy: 'Լուսավորություն',
      en: 'Lighting',
      ru: 'Освещение'
    },
    children: {
      hy: ['LED ժապավեն', 'Լամպեր', 'Աքսեսուարներ'],
      en: ['LED strip', 'Lamps', 'Accessories'],
      ru: ['LED лента', 'Лампы', 'Аксессуары']
    }
  }
]

const languages = [
  { code: 'hy', label: 'Հայերեն', icon: '/assets/icons/flag-hy.svg' },
  { code: 'en', label: 'English', icon: '/assets/icons/flag-en.svg' },
  { code: 'ru', label: 'Русский', icon: '/assets/icons/flag-ru.svg' }
] satisfies Array<{ code: LanguageCode, label: string, icon: string }>

const seoPages = [
  { key: 'home', title: 'Interino' },
  { key: 'contact', title: 'Contact' },
  { key: 'cart', title: 'Cart' },
  { key: 'checkout-success', title: 'Checkout success' },
  { key: 'search', title: 'Search' },
  { key: 'privacy-policy', title: 'Privacy Policy' },
  { key: 'category', title: 'Category' },
  { key: 'product', title: 'Product' }
]

const defaultSeo = Object.fromEntries(seoPages.map((page) => [
  page.key,
  Object.fromEntries(languages.map((language) => [
    language.code,
    {
      title: page.title,
      metaTitle: page.title,
      metaDescription: '',
      metaKeywords: ''
    }
  ]))
])) as Record<string, Record<LanguageCode, SeoEntry>>

const defaultSettings: ShopFrontendSettings = {
  brandLogo: '/assets/brand/logo.png',
  footerText: '123 Ceiling Ave, Yerevan · +374 10 234 567',
  contactPhone: '+374 10 234 567',
  contactEmail: 'info@interino.am',
  contactAddress: '123 Ceiling Ave, Yerevan',
  contactMapUrl: 'https://maps.google.com/?q=Yerevan',
  socialLinks: [
    { label: 'Instagram', href: 'https://www.instagram.com/', external: true },
    { label: 'Facebook', href: 'https://www.facebook.com/', external: true }
  ]
}

const translations = {
  hy: {
    add: 'Ավելացնել',
    added: 'Ավելացվեց',
    addToCartLong: 'Ավելացնել զամբյուղ',
    address: 'Առաքման հասցե',
    addressPlaceholder: 'ք. Երևան...',
    cart: 'Զամբյուղ',
    cartTitle: 'Զամբյուղը',
    checkoutSuccessHome: 'Գլխավոր էջ',
    checkoutSuccessTitle: 'Վճարումը հաջողությամբ կատարվել է, դուք կստանաք էլեկտրոնային հաղորդագրություն...',
    checkoutDeliveryNote: 'Ապրանքի առաքման համար մեր աշխատակիցը կկապվի Ձեզ հետ և կհստակեցնի հասցեն։',
    checkoutTitle: 'Անձնական տվյալներ',
    clearCart: 'Մաքրել զամբյուղը',
    closeDialog: 'Փակել պատուհանը',
    closeMenu: 'Փակել մենյուն',
    contact: 'Կապ մեզ հետ',
    contactAddress: 'Հասցե',
    contactEmail: 'Էլ. հասցե',
    contactFormTitle: 'Ուղարկել հաղորդագրություն',
    contactHeroKicker: 'Կապ մեզ հետ',
    contactHeroText: 'Գրեք մեզ կամ զանգահարեք, և մենք կօգնենք ընտրել ճիշտ նյութը, չափը և քանակը։',
    contactHeroTitle: 'Մենք պատրաստ ենք օգնել ձեր պատվերի հարցում',
    contactHours: 'Աշխատանքային ժամեր',
    contactHoursValue: 'Երկ - Շաբ · 10:00 - 19:00',
    contactInfoTitle: 'Կոնտակտային տվյալներ',
    contactMessage: 'Հաղորդագրություն',
    contactMessagePlaceholder: 'Գրեք ինչ ապրանք կամ չափ է պետք',
    contactNamePlaceholder: 'Ձեր անունը',
    contactPhone: 'Հեռախոս',
    contactPhonePlaceholder: '+374 __ ___ ___',
    contactSend: 'Ուղարկել',
    email: 'Էլեկտրոնային հասցե',
    emailError: 'Էլ. հասցեն սխալ է',
    emptyCartTitle: 'Զամբյուղը դատարկ է',
    firstName: 'Անուն',
    firstNamePlaceholder: 'Անուն',
    home: 'Գլխավոր',
    lastName: 'Ազգանուն',
    linePrice: 'Գին',
    masterCode: 'Վարպետի կոդ',
    materialName: 'Նյութի անուն',
    menu: 'Մենյու',
    new: 'Նորույթ',
    nextProducts: 'Հաջորդ ապրանքներ',
    openMenu: 'Բացել մենյուն',
    optionCode: 'կոդ',
    optionColor: 'Գույն',
    optionHeight: 'Բարձրություն',
    optionMaterial: 'Նյութ',
    optionPiece: 'Հատիկ',
    optionQuantity: 'Քանակ',
    optionSize: 'Չափ',
    optionType: 'Տեսակ',
    optionTypeName: 'Տեսակի անուն',
    optionUnit: 'Չափ. միավոր',
    optionUnitLong: 'Չափի միավ.',
    pay: 'Վճարել',
    paymentDue: 'Գումար',
    paymentDueAmount: 'Վճարման ենթակա գումար՝',
    phone: 'Հեռախոսահամար',
    previousProducts: 'Նախորդ ապրանքներ',
    privacyPolicy: 'Գաղտնիության քաղաքականություն',
    profile: 'Պրոֆիլ',
    recommendationsTitle: 'Ձեզ կարող է դուր գալ',
    relatedProducts: 'Փոխկապակցված ապրանքներ',
    requiredField: 'Պարտադիր դաշտ է',
    search: 'Որոնել',
    searchEmptyTitle: 'Որոնման արդյունքները չգտնվեցին',
    searchHomeLink: 'Գլխավոր էջ',
    searchRecommendations: 'Առաջարկվող ապրանքներ',
    searchResultsTitle: 'Որոնման արդյունքներ',
    similarProducts: 'Նման ապրանքներ',
    sliderControls: 'Սլայդերի կառավարում',
    social: 'Instagram · Facebook · Privacy Policy'
  },
  en: {
    add: 'Add',
    added: 'Added',
    addToCartLong: 'Add to cart',
    address: 'Delivery address',
    addressPlaceholder: 'Yerevan...',
    cart: 'Cart',
    cartTitle: 'Cart',
    checkoutSuccessHome: 'Home page',
    checkoutSuccessTitle: 'Payment was completed successfully. You will receive an email notification...',
    checkoutDeliveryNote: 'For product delivery, our employee will contact you and confirm the address.',
    checkoutTitle: 'Personal details',
    clearCart: 'Clear cart',
    closeDialog: 'Close dialog',
    closeMenu: 'Close menu',
    contact: 'Contact us',
    contactAddress: 'Address',
    contactEmail: 'Email',
    contactFormTitle: 'Send a message',
    contactHeroKicker: 'Contact us',
    contactHeroText: 'Write to us or call, and we will help you choose the right material, size, and quantity.',
    contactHeroTitle: 'We are ready to help with your order',
    contactHours: 'Working hours',
    contactHoursValue: 'Mon - Sat · 10:00 - 19:00',
    contactInfoTitle: 'Contact details',
    contactMessage: 'Message',
    contactMessagePlaceholder: 'Write what product or size you need',
    contactNamePlaceholder: 'Your name',
    contactPhone: 'Phone',
    contactPhonePlaceholder: '+374 __ ___ ___',
    contactSend: 'Send',
    email: 'Email address',
    emailError: 'Email address is invalid',
    emptyCartTitle: 'Cart is empty',
    firstName: 'First name',
    firstNamePlaceholder: 'First name',
    home: 'Home',
    lastName: 'Last name',
    linePrice: 'Price',
    masterCode: 'Master code',
    materialName: 'Material name',
    menu: 'Menu',
    new: 'New',
    nextProducts: 'Next products',
    openMenu: 'Open menu',
    optionCode: 'Code',
    optionColor: 'Color',
    optionHeight: 'Height',
    optionMaterial: 'Material',
    optionPiece: 'Piece',
    optionQuantity: 'Quantity',
    optionSize: 'Size',
    optionType: 'Type',
    optionTypeName: 'Type name',
    optionUnit: 'Unit',
    optionUnitLong: 'Unit',
    pay: 'Pay',
    paymentDue: 'Payment due',
    paymentDueAmount: 'Payment due:',
    phone: 'Phone number',
    previousProducts: 'Previous products',
    privacyPolicy: 'Privacy Policy',
    profile: 'Profile',
    recommendationsTitle: 'You may also like',
    relatedProducts: 'Related products',
    requiredField: 'Required field',
    search: 'Search',
    searchEmptyTitle: 'No search results found',
    searchHomeLink: 'Home page',
    searchRecommendations: 'Recommended products',
    searchResultsTitle: 'Search results',
    similarProducts: 'Similar products',
    sliderControls: 'Slider controls',
    social: 'Instagram · Facebook · Privacy Policy'
  },
  ru: {
    add: 'Добавить',
    added: 'Добавлено',
    addToCartLong: 'Добавить в корзину',
    address: 'Адрес доставки',
    addressPlaceholder: 'г. Ереван...',
    cart: 'Корзина',
    cartTitle: 'Корзина',
    checkoutSuccessHome: 'Главная страница',
    checkoutSuccessTitle: 'Оплата успешно выполнена, вы получите электронное уведомление...',
    checkoutDeliveryNote: 'Для доставки товара наш сотрудник свяжется с вами и уточнит адрес.',
    checkoutTitle: 'Личные данные',
    clearCart: 'Очистить корзину',
    closeDialog: 'Закрыть окно',
    closeMenu: 'Закрыть меню',
    contact: 'Связаться',
    contactAddress: 'Адрес',
    contactEmail: 'Эл. почта',
    contactFormTitle: 'Отправить сообщение',
    contactHeroKicker: 'Свяжитесь с нами',
    contactHeroText: 'Напишите или позвоните нам, и мы поможем выбрать правильный материал, размер и количество.',
    contactHeroTitle: 'Мы готовы помочь с вашим заказом',
    contactHours: 'Рабочие часы',
    contactHoursValue: 'Пн - Сб · 10:00 - 19:00',
    contactInfoTitle: 'Контактные данные',
    contactMessage: 'Сообщение',
    contactMessagePlaceholder: 'Напишите, какой товар или размер вам нужен',
    contactNamePlaceholder: 'Ваше имя',
    contactPhone: 'Телефон',
    contactPhonePlaceholder: '+374 __ ___ ___',
    contactSend: 'Отправить',
    email: 'Электронная почта',
    emailError: 'Неверный адрес эл. почты',
    emptyCartTitle: 'Корзина пуста',
    firstName: 'Имя',
    firstNamePlaceholder: 'Имя',
    home: 'Главная',
    lastName: 'Фамилия',
    linePrice: 'Цена',
    masterCode: 'Код мастера',
    materialName: 'Название материала',
    menu: 'Меню',
    new: 'Новинка',
    nextProducts: 'Следующие товары',
    openMenu: 'Открыть меню',
    optionCode: 'Код',
    optionColor: 'Цвет',
    optionHeight: 'Высота',
    optionMaterial: 'Материал',
    optionPiece: 'Штука',
    optionQuantity: 'Количество',
    optionSize: 'Размер',
    optionType: 'Тип',
    optionTypeName: 'Название типа',
    optionUnit: 'Ед. изм.',
    optionUnitLong: 'Ед. изм.',
    pay: 'Оплатить',
    paymentDue: 'К оплате',
    paymentDueAmount: 'Сумма к оплате:',
    phone: 'Номер телефона',
    previousProducts: 'Предыдущие товары',
    privacyPolicy: 'Политика конфиденциальности',
    profile: 'Профиль',
    recommendationsTitle: 'Вам может понравиться',
    relatedProducts: 'Связанные товары',
    requiredField: 'Обязательное поле',
    search: 'Поиск',
    searchEmptyTitle: 'Результаты поиска не найдены',
    searchHomeLink: 'Главная страница',
    searchRecommendations: 'Рекомендуемые товары',
    searchResultsTitle: 'Результаты поиска',
    similarProducts: 'Похожие товары',
    sliderControls: 'Управление слайдером',
    social: 'Instagram · Facebook · Privacy Policy'
  }
} satisfies Record<LanguageCode, Record<string, string>>
const products: Product[] = [
  {
    id: 1,
    title: {
      hy: 'Ալյումինե, երկաթե հիմք նուրբ սև գույնով',
      en: 'Aluminum profile with a refined black finish',
      ru: 'Алюминиевый профиль с черным покрытием'
    },
    price: '15000',
    kind: 'black',
    image: '/assets/products/profile-black.png',
    isNew: true
  },
  {
    id: 2,
    title: {
      hy: 'Արծաթագույն պրոֆիլ ձգվող առաստաղների համար',
      en: 'Silver profile for stretch ceiling systems',
      ru: 'Серебристый профиль для натяжных потолков'
    },
    price: '15000',
    kind: 'silver',
    image: '/assets/products/profile-silver.png',
    isNew: true
  },
  {
    id: 3,
    title: {
      hy: 'Սև անկյունային պրոֆիլ ճշգրիտ ամրացման համար',
      en: 'Black corner profile for precise installation',
      ru: 'Черный угловой профиль для точного монтажа'
    },
    price: '15000',
    kind: 'black',
    image: '/assets/products/profile-black.png',
    isNew: true
  },
  {
    id: 4,
    title: {
      hy: 'Արծաթագույն ամուր պրոֆիլ լուսային գծերի համար',
      en: 'Durable silver profile for light lines',
      ru: 'Прочный серебристый профиль для световых линий'
    },
    price: '15000',
    kind: 'silver',
    image: '/assets/products/profile-silver.png',
    isNew: true
  },
  {
    id: 5,
    title: {
      hy: 'Փայտային պանել պատերի և առաստաղի հարդարման համար',
      en: 'Wood-look panel for wall and ceiling finishes',
      ru: 'Панель под дерево для стен и потолков'
    },
    price: '15000',
    kind: 'wood',
    image: '/assets/products/panel-wood.png',
    isNew: true
  },
  {
    id: 6,
    title: {
      hy: 'Սև պրոֆիլ մինիմալ ինտերիերի լուծումների համար',
      en: 'Black profile for minimal interior solutions',
      ru: 'Черный профиль для минималистичных интерьеров'
    },
    price: '15000',
    kind: 'black',
    image: '/assets/products/profile-black.png',
    isNew: true
  },
  {
    id: 7,
    title: {
      hy: 'Արծաթագույն մոնտաժային պրոֆիլ արագ տեղադրման համար',
      en: 'Silver mounting profile for fast installation',
      ru: 'Серебристый монтажный профиль для быстрой установки'
    },
    price: '15000',
    kind: 'silver',
    image: '/assets/products/profile-silver.png',
    isNew: true
  },
  {
    id: 8,
    title: {
      hy: 'Սև պրոֆիլ թաքնված ամրացման համակարգերի համար',
      en: 'Black profile for concealed mounting systems',
      ru: 'Черный профиль для скрытых систем крепления'
    },
    price: '15000',
    kind: 'black',
    image: '/assets/products/profile-black.png',
    isNew: true
  },
  {
    id: 9,
    title: {
      hy: 'Արծաթագույն պրոֆիլ LED լուսավորության ալիքով',
      en: 'Silver profile with LED lighting channel',
      ru: 'Серебристый профиль с каналом для LED'
    },
    price: '15000',
    kind: 'silver',
    image: '/assets/products/profile-silver.png',
    isNew: true
  },
  {
    id: 10,
    title: {
      hy: 'Փայտային դեկորատիվ պանել տաք երանգով',
      en: 'Warm-tone decorative wood-look panel',
      ru: 'Декоративная панель под дерево теплого оттенка'
    },
    price: '15000',
    kind: 'wood',
    image: '/assets/products/panel-wood.png',
    isNew: true
  }
]

const recentlyAddedProductId = ref<number | null>(null)
let addToCartFeedbackTimer: ReturnType<typeof setTimeout> | null = null

export function useCatalog() {
  const route = useRoute()
  const router = useRouter()
  const runtimeConfig = useRuntimeConfig()
  const frontApiBase = String(runtimeConfig.public.frontApiBase || '').replace(/\/$/, '')
  const frontApiUrl = (path: string) => `${frontApiBase}${path}`
  const { data: remoteCatalog } = useFetch<any>(frontApiUrl('/api/front/shop'), {
    key: 'front-shop-config',
    transform: (response: any) => response?.data ?? null,
    default: () => null
  })
  const currentLanguageCode = useCookie<LanguageCode>('interino-locale', {
    default: () => 'hy',
    sameSite: 'lax'
  })
  const cartItems = useCookie<CartItem[]>('interino-cart', {
    default: () => [],
    sameSite: 'lax'
  })
  const activeMenuKey = useState<string | null>('active-menu-key', () => null)
  const searchTerm = useState<string>('search-term', () => '')
  const selectedProductImage = useState<string | null>('selected-product-image', () => null)

  const catalogConfig = computed<ShopFrontendConfig>(() => ({
    languages: remoteCatalog.value?.languages?.length ? remoteCatalog.value.languages : languages,
    menuGroups: remoteCatalog.value?.menuGroups?.length ? remoteCatalog.value.menuGroups : menuGroups,
    translations: remoteCatalog.value?.translations || translations,
    products: remoteCatalog.value?.products?.length ? remoteCatalog.value.products : products,
    settings: { ...defaultSettings, ...(remoteCatalog.value?.settings || {}) },
    seo: { ...defaultSeo, ...(remoteCatalog.value?.seo || {}) },
    privacy: remoteCatalog.value?.privacy
  }))
  const availableLanguages = computed(() => catalogConfig.value.languages)
  const menuGroupList = computed(() => catalogConfig.value.menuGroups)
  const primaryProducts = computed(() => catalogConfig.value.products)
  const secondaryProductList = computed(() => primaryProducts.value.slice(0, 5).map((product) => ({
    ...product,
    id: product.id + 20
  })))
  const shopSettings = computed(() => catalogConfig.value.settings)
  const shopPrivacy = computed(() => catalogConfig.value.privacy)
  const currentLanguage = computed(() => availableLanguages.value.find((language) => language.code === currentLanguageCode.value) ?? availableLanguages.value[0])
  const copy = computed(() => catalogConfig.value.translations[currentLanguageCode.value] ?? translations[currentLanguageCode.value])
  const socialLinks = computed(() => [
    ...(shopSettings.value.socialLinks || []),
    { label: copy.value.privacyPolicy, href: '/privacy-policy', external: false }
  ])
  const categories = computed(() => menuGroupList.value.map((group) => group.title[currentLanguageCode.value]))
  const allProducts = computed(() => [...primaryProducts.value, ...secondaryProductList.value])
  const isCartPage = computed(() => withoutLanguagePrefix(route.path) === '/cart')
  const isContactPage = computed(() => withoutLanguagePrefix(route.path) === '/contact')
  const isSearchPage = computed(() => withoutLanguagePrefix(route.path) === '/search')
  const searchQuery = computed(() => {
    const query = route.query.q

    return typeof query === 'string' ? query.trim() : ''
  })
  const currentProduct = computed(() => {
    const match = withoutLanguagePrefix(route.path).match(/^\/products\/(\d+)$/)
    const productId = match ? Number(match[1]) : null

    return allProducts.value.find((product) => product.id === productId) ?? null
  })
  const currentProductImage = computed(() => selectedProductImage.value || currentProduct.value?.image || '')
  const detailThumbnails = computed(() => {
    if (!currentProduct.value) {
      return []
    }

    return [
      ...(currentProduct.value.gallery?.length ? currentProduct.value.gallery : [currentProduct.value.image]),
      ...primaryProducts.value
        .filter((product) => product.id !== currentProduct.value?.id)
        .map((product) => product.image)
    ].slice(0, 4)
  })
  const relatedProducts = computed(() => primaryProducts.value.filter((product) => product.id !== currentProduct.value?.id))
  const similarProducts = computed(() => allProducts.value.filter((product) => product.id !== currentProduct.value?.id))
  const searchResults = computed(() => {
    const query = searchQuery.value.toLowerCase()

    if (!query) {
      return []
    }

    return allProducts.value.filter((product) => {
      const searchable = [product.kind, product.title.hy, product.title.en, product.title.ru].join(' ').toLowerCase()

      return searchable.includes(query)
    })
  })
  const searchRecommendations = computed(() => primaryProducts.value.slice(0, 8))
  const cartCount = computed(() => cartItems.value.reduce((total, item) => total + item.quantity, 0))
  const cartProducts = computed(() => {
    return cartItems.value
      .map((item) => {
        const product = allProducts.value.find((candidate) => candidate.id === item.productId)

        return product ? { ...item, product } : null
      })
      .filter((item): item is CartItem & { product: Product } => Boolean(item))
  })
  const cartTotal = computed(() => {
    return cartProducts.value.reduce((total, item) => total + Number(item.product.price) * item.quantity, 0)
  })
  const cartRecommendations = computed(() => allProducts.value.filter((product) => !cartItems.value.some((item) => item.productId === product.id)).slice(0, 4))
  const currentCategoryRoute = computed(() => {
    const match = withoutLanguagePrefix(route.path).match(/^\/categories\/([^/]+)(?:\/(\d+))?$/)

    if (!match) {
      return null
    }

    return {
      groupKey: match[1],
      childIndex: match[2] ? Number(match[2]) : null
    }
  })
  const currentCategoryGroup = computed(() => {
    return menuGroupList.value.find((group) => group.key === currentCategoryRoute.value?.groupKey) ?? null
  })
  const currentCategoryChild = computed(() => {
    const routeValue = currentCategoryRoute.value

    if (!currentCategoryGroup.value || !routeValue || routeValue.childIndex === null) {
      return null
    }

    return currentCategoryGroup.value.children[currentLanguageCode.value][routeValue.childIndex] ?? null
  })
  const isCategoryPage = computed(() => Boolean(currentCategoryGroup.value))
  const currentSeoPageKey = computed(() => {
    const path = withoutLanguagePrefix(route.path)

    if (path === '/') return 'home'
    if (path === '/contact') return 'contact'
    if (path === '/cart') return 'cart'
    if (path === '/checkout-success') return 'checkout-success'
    if (path === '/search') return 'search'
    if (path === '/privacy-policy') return 'privacy-policy'
    if (path.startsWith('/categories/')) return 'category'
    if (path.startsWith('/products/')) return 'product'

    return 'home'
  })
  const currentSeo = computed(() => {
    const productSeo = currentProduct.value?.meta?.[currentLanguageCode.value]
      ?? currentProduct.value?.meta?.hy
    const categorySeo = currentCategoryRoute.value?.childIndex === null
      ? currentCategoryGroup.value?.meta?.[currentLanguageCode.value]
      : currentCategoryGroup.value?.childMeta?.[currentLanguageCode.value]?.[currentCategoryRoute.value?.childIndex ?? -1]
    const seo = catalogConfig.value.seo[currentSeoPageKey.value]?.[currentLanguageCode.value]
      ?? catalogConfig.value.seo[currentSeoPageKey.value]?.hy
      ?? defaultSeo[currentSeoPageKey.value]?.[currentLanguageCode.value]
      ?? defaultSeo.home[currentLanguageCode.value]
      ?? defaultSeo.home.hy
    const dynamicTitle = currentProduct.value?.title?.[currentLanguageCode.value]
      || currentCategoryChild.value
      || currentCategoryGroup.value?.title?.[currentLanguageCode.value]
      || seo.title

    return {
      title: productSeo?.title || categorySeo?.title || seo.title || dynamicTitle,
      metaTitle: productSeo?.metaTitle || categorySeo?.metaTitle || seo.metaTitle || dynamicTitle,
      metaDescription: productSeo?.metaDescription || categorySeo?.metaDescription || seo.metaDescription || '',
      metaKeywords: productSeo?.metaKeywords || categorySeo?.metaKeywords || seo.metaKeywords || ''
    }
  })
  const categoryProducts = computed(() => {
    const routeValue = currentCategoryRoute.value

    if (!routeValue) {
      return primaryProducts.value
    }

    const filtered = primaryProducts.value.filter((product) => {
      if (product.categoryKey !== routeValue.groupKey) {
        return false
      }

      return routeValue.childIndex === null || product.categoryChildIndex === routeValue.childIndex
    })

    return primaryProducts.value.some((product) => product.categoryKey) ? filtered : primaryProducts.value
  })

  function isLanguageCode(value: string | undefined): value is LanguageCode {
    return Boolean(value && availableLanguages.value.some((language) => language.code === value))
  }

  function getRouteLanguage(path: string): LanguageCode | null {
    const firstSegment = path.split('/').filter(Boolean)[0]

    return isLanguageCode(firstSegment) ? firstSegment : null
  }

  function withoutLanguagePrefix(path: string) {
    const parts = path.split('/').filter(Boolean)

    if (isLanguageCode(parts[0])) {
      parts.shift()
    }

    return parts.length ? `/${parts.join('/')}` : '/'
  }

  function localizedPath(path = '/') {
    const cleanPath = path.startsWith('/') ? path : `/${path}`

    return cleanPath === '/'
      ? `/${currentLanguageCode.value}`
      : `/${currentLanguageCode.value}${cleanPath}`
  }

  function productPath(product: Product) {
    return localizedPath(`/products/${product.id}`)
  }

  function categoryPath(groupKey: string, childIndex?: number) {
    return localizedPath(`/categories/${groupKey}${typeof childIndex === 'number' ? `/${childIndex}` : ''}`)
  }

  function openProduct(product: Product) {
    router.push(productPath(product))
  }

  function addToCart(product: Product) {
    const existingItem = cartItems.value.find((item) => item.productId === product.id)

    if (existingItem) {
      existingItem.quantity += 1
      cartItems.value = [...cartItems.value]
    } else {
      cartItems.value = [...cartItems.value, { productId: product.id, quantity: 1 }]
    }

    recentlyAddedProductId.value = product.id

    if (addToCartFeedbackTimer) {
      clearTimeout(addToCartFeedbackTimer)
    }

    addToCartFeedbackTimer = setTimeout(() => {
      recentlyAddedProductId.value = null
    }, 1200)
  }

  function updateCartQuantity(productId: number, change: number) {
    cartItems.value = cartItems.value
      .map((item) => item.productId === productId ? { ...item, quantity: item.quantity + change } : item)
      .filter((item) => item.quantity > 0)
  }

  function removeFromCart(productId: number) {
    cartItems.value = cartItems.value.filter((item) => item.productId !== productId)
  }

  function clearCart() {
    cartItems.value = []
  }

  function openCategory(groupKey: string, childIndex?: number) {
    router.push(categoryPath(groupKey, childIndex))
  }

  function submitSearch() {
    const query = searchTerm.value.trim()

    router.push(query ? `${localizedPath('/search')}?q=${encodeURIComponent(query)}` : localizedPath('/search'))
  }

  function selectProductImage(image: string) {
    selectedProductImage.value = image
  }

  async function submitOrder(customer: Record<string, string>) {
    await $fetch(frontApiUrl('/api/front/orders'), {
      method: 'POST',
      body: {
        customer,
        items: cartProducts.value.map((item) => ({
          productId: item.productId,
          quantity: item.quantity,
          product: item.product
        })),
        total: cartTotal.value,
        language: currentLanguageCode.value
      }
    })

    clearCart()
  }

  function scrollProductSlider(id: string, direction: 'previous' | 'next') {
    const slider = document.getElementById(id)

    if (!slider) {
      return
    }

    const firstCard = slider.querySelector<HTMLElement>('.product-card')
    const gap = Number.parseFloat(window.getComputedStyle(slider).gap || '0')
    const scrollGap = Number.isFinite(gap) ? gap : 14
    const scrollDistance = firstCard ? firstCard.offsetWidth + scrollGap : slider.clientWidth

    slider.scrollBy({
      left: direction === 'next' ? scrollDistance : -scrollDistance,
      behavior: 'smooth'
    })
  }

  function localizedCurrentPath(code: LanguageCode) {
    const suffix = route.fullPath.slice(route.path.length)
    const cleanPath = withoutLanguagePrefix(route.path)
    const localized = cleanPath === '/' ? `/${code}` : `/${code}${cleanPath}`

    return `${localized}${suffix}`
  }

  function toggleMenuGroup(key: string) {
    activeMenuKey.value = activeMenuKey.value === key ? null : key
  }

  function selectLanguage(language: (typeof languages)[number]) {
    currentLanguageCode.value = language.code
    router.push(localizedCurrentPath(language.code))
  }

  watch(
    () => route.path,
    (path) => {
      const routeLanguage = getRouteLanguage(path)

      if (routeLanguage && routeLanguage !== currentLanguageCode.value) {
        currentLanguageCode.value = routeLanguage
      }
    },
    { immediate: true }
  )

  watch(
    () => currentProduct.value?.id,
    () => {
      selectedProductImage.value = null
    }
  )

  watch(
    () => currentCategoryGroup.value?.key,
    (groupKey) => {
      if (groupKey) {
        activeMenuKey.value = groupKey
      }
    },
    { immediate: true }
  )

  watch(
    () => route.query.q,
    (query) => {
      searchTerm.value = typeof query === 'string' ? query : ''
    },
    { immediate: true }
  )

  useHead(() => {
    const seo = currentSeo.value
    const title = seo.metaTitle || seo.title
    const meta = [
      seo.metaDescription ? { name: 'description', content: seo.metaDescription } : null,
      seo.metaKeywords ? { name: 'keywords', content: seo.metaKeywords } : null,
      { property: 'og:title', content: title },
      seo.metaDescription ? { property: 'og:description', content: seo.metaDescription } : null
    ].filter(Boolean)

    return {
      title,
      htmlAttrs: { lang: currentLanguageCode.value },
      meta
    }
  })

  return {
    activeMenuKey,
    allProducts,
    cartCount,
    cartProducts,
    cartRecommendations,
    cartTotal,
    categoryProducts,
    categories,
    categoryPath,
    clearCart,
    copy,
    currentCategoryChild,
    currentCategoryGroup,
    currentCategoryRoute,
    currentLanguage,
    currentLanguageCode,
    currentProduct,
    currentProductImage,
    currentSeo,
    detailThumbnails,
    isCartPage,
    isCategoryPage,
    isContactPage,
    isSearchPage,
    languages: availableLanguages,
    localizedPath,
    menuGroups: menuGroupList,
    openCategory,
    openProduct,
    products: primaryProducts,
    recentlyAddedProductId,
    relatedProducts,
    removeFromCart,
    searchRecommendations,
    searchResults,
    searchTerm,
    secondaryProducts: secondaryProductList,
    selectLanguage,
    selectProductImage,
    similarProducts,
    shopSettings,
    shopPrivacy,
    socialLinks,
    submitSearch,
    submitOrder,
    toggleMenuGroup,
    updateCartQuantity,
    addToCart,
    scrollProductSlider
  }
}
