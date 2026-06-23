import { computed, ref, watch } from 'vue'

type ProductKind = string
type LanguageCode = string

interface ProductColor {
  id?: number | string
  name: string
  value: string
}

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
  shortDescription?: Record<LanguageCode, string>
  description?: Record<LanguageCode, string>
  meta?: Record<LanguageCode, SeoEntry>
  price: string
  kind: ProductKind
  image: string
  gallery?: string[]
  options?: Record<string, any>
  priceOptions?: Record<string, Array<{ id: number, name: string, value?: string | null, label: string, price: string }>>
  isNew?: boolean
  isTemporarilyUnavailable?: boolean
  status?: boolean
  categoryKey?: string | null
  categoryChildIndex?: number | null
}

interface Craftsman {
  id: number | null
  code: string
  name: string
  image?: string | null
  phone?: string | null
  work_region?: string | null
  work_city?: string | null
  work_field?: string | null
  has_whatsapp?: boolean
  has_viber?: boolean
}

interface CraftsmenResponse {
  data?: Craftsman[]
  filters?: {
    regions?: string[]
    cities?: string[]
    fields?: string[]
  }
}

interface CartItem {
  productId: number
  quantity: number
  color?: ProductColor | null
  effectivePrice?: number | null
  selectedOptionLabel?: string | null
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
  homeSections?: Array<{
    id?: string
    selections: Array<{
      id?: string
      categoryKey: string
      childIndex?: number | null
      limit?: number | null
    }>
  }>
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
  { key: 'craftsmen', title: 'Craftsmen' },
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
    craftsmanCode: 'Արհեստավորի կոդ',
    craftsmanName: 'Արհեստավորի անուն-ազգանուն',
    craftsmanSearchPlaceholder: 'Գրեք կոդը կամ անունը',
    craftsmen: 'Արհեստավորներ',
    craftsmenTitle: 'Ընտրեք արհեստավորին',
    craftsmenIntro: 'Ընտրեք աշխատողին, որպեսզի checkout-ում կոդն ու անունը լրացվեն ավտոմատ։',
    craftsmenSearch: 'Որոնել անունով կամ կոդով',
    craftsmenRegion: 'Մարզ',
    craftsmenCity: 'Քաղաք',
    craftsmenField: 'Ոլորտ',
    craftsmenAll: 'Բոլորը',
    craftsmenChoose: 'Ընտրել',
    craftsmenSkip: 'Բաց թողնել',
    craftsmenEmpty: 'Արհեստավորներ չգտնվեցին',
    craftsmenPhone: 'Հեռախոս',
    selectedCraftsman: 'Ընտրված արհեստավոր',
    home: 'Գլխավոր',
    lastName: 'Ազգանուն',
    linePrice: 'Գին',
    masterCode: 'Վարպետի կոդ',
    materialName: 'Նյութի անուն',
    menu: 'Մենյու',
    new: 'Նորույթ',
    nextProducts: 'Հաջորդ ապրանքներ',
    openMenu: 'Բացել մենյուն',
    emailPlaceholder: 'example@mail.com',
    loading: 'Բեռնվում է…',
    optionCode: 'կոդ',
    optionColor: 'Գույն',
    optionHeight: 'Բարձրություն',
    optionMaterial: 'Նյութ',
    optionPiece: 'Հատիկ',
    optionPower: 'Հզորություն',
    optionQuantity: 'Քանակ',
    optionSize: 'Չափ',
    optionType: 'Տեսակ',
    optionTypeName: 'Տեսակի անուն',
    optionUnit: 'Չափ. միավոր',
    optionUnitLong: 'Չափի միավ.',
    pay: 'Վճարել',
    phonePlaceholder: '+374 __ ___ ___',
    removeItem: 'Հեռացնել',
    viber: 'Viber',
    whatsApp: 'WhatsApp',
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
    social: 'Instagram · Facebook · Privacy Policy',
    temporarilyUnavailable: 'Ժամանակավորապես բացակայում է'
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
    craftsmanCode: 'Craftsman code',
    craftsmanName: 'Craftsman full name',
    craftsmanSearchPlaceholder: 'Type code or name',
    craftsmen: 'Craftsmen',
    craftsmenTitle: 'Choose a craftsman',
    craftsmenIntro: 'Choose a worker so their code and name are filled automatically at checkout.',
    craftsmenSearch: 'Search by name or code',
    craftsmenRegion: 'Region',
    craftsmenCity: 'City',
    craftsmenField: 'Field',
    craftsmenAll: 'All',
    craftsmenChoose: 'Choose',
    craftsmenSkip: 'Skip',
    craftsmenEmpty: 'No craftsmen found',
    craftsmenPhone: 'Phone',
    selectedCraftsman: 'Selected craftsman',
    home: 'Home',
    lastName: 'Last name',
    linePrice: 'Price',
    masterCode: 'Master code',
    materialName: 'Material name',
    menu: 'Menu',
    new: 'New',
    nextProducts: 'Next products',
    openMenu: 'Open menu',
    emailPlaceholder: 'example@mail.com',
    loading: 'Loading…',
    optionCode: 'Code',
    optionColor: 'Color',
    optionHeight: 'Height',
    optionMaterial: 'Material',
    optionPiece: 'Piece',
    optionPower: 'Power',
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
    phonePlaceholder: '+374 __ ___ ___',
    removeItem: 'Remove',
    viber: 'Viber',
    whatsApp: 'WhatsApp',
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
    social: 'Instagram · Facebook · Privacy Policy',
    temporarilyUnavailable: 'Temporarily unavailable'
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
    craftsmanCode: 'Код мастера',
    craftsmanName: 'Имя и фамилия мастера',
    craftsmanSearchPlaceholder: 'Введите код или имя',
    craftsmen: 'Мастера',
    craftsmenTitle: 'Выберите мастера',
    craftsmenIntro: 'Выберите работника, чтобы код и имя автоматически заполнились при оформлении.',
    craftsmenSearch: 'Поиск по имени или коду',
    craftsmenRegion: 'Область',
    craftsmenCity: 'Город',
    craftsmenField: 'Сфера',
    craftsmenAll: 'Все',
    craftsmenChoose: 'Выбрать',
    craftsmenSkip: 'Пропустить',
    craftsmenEmpty: 'Мастера не найдены',
    craftsmenPhone: 'Телефон',
    selectedCraftsman: 'Выбранный мастер',
    home: 'Главная',
    lastName: 'Фамилия',
    linePrice: 'Цена',
    masterCode: 'Код мастера',
    materialName: 'Название материала',
    menu: 'Меню',
    new: 'Новинка',
    nextProducts: 'Следующие товары',
    openMenu: 'Открыть меню',
    emailPlaceholder: 'example@mail.com',
    loading: 'Загрузка…',
    optionCode: 'Код',
    optionColor: 'Цвет',
    optionHeight: 'Высота',
    optionMaterial: 'Материал',
    optionPiece: 'Штука',
    optionPower: 'Мощность',
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
    phonePlaceholder: '+374 __ ___ ___',
    removeItem: 'Удалить',
    viber: 'Viber',
    whatsApp: 'WhatsApp',
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
    social: 'Instagram · Facebook · Privacy Policy',
    temporarilyUnavailable: 'Временно отсутствует'
  }
} satisfies Record<LanguageCode, Record<string, string>>
const products: Product[] = []

const recentlyAddedProductId = ref<number | null>(null)
let addToCartFeedbackTimer: ReturnType<typeof setTimeout> | null = null

function normalizeProductColor(color: any): ProductColor | null {
  if (!color) {
    return null
  }

  if (typeof color === 'string') {
    return {
      id: color,
      name: color,
      value: color
    }
  }

  const value = color.value || color.color || ''
  const name = color.name || value

  if (!value && !name) {
    return null
  }

  return {
    id: color.id ?? value ?? name,
    name,
    value: value || '#ffffff'
  }
}

function productColors(product?: Product | null): ProductColor[] {
  const colors = product?.options?.colors

  if (Array.isArray(colors) && colors.length) {
    return colors
      .map((color) => normalizeProductColor(color))
      .filter((color): color is ProductColor => Boolean(color))
  }

  const fallbackColor = product?.options?.color

  return fallbackColor
    ? [normalizeProductColor(fallbackColor)].filter((color): color is ProductColor => Boolean(color))
    : []
}

function colorKey(color?: ProductColor | null) {
  return String(color?.id ?? color?.value ?? color?.name ?? 'default')
}

function cartItemKey(item: Pick<CartItem, 'productId' | 'color' | 'effectivePrice'>) {
  return `${item.productId}:${colorKey(item.color)}:${item.effectivePrice ?? ''}`
}

export function useCatalog() {
  const route = useRoute()
  const router = useRouter()
  const runtimeConfig = useRuntimeConfig()
  const frontApiBase = String(runtimeConfig.public.frontApiBase || 'http://127.0.0.1:8000').replace(/\/$/, '')
  const frontApiUrl = (path: string) => `${frontApiBase}${path}`
  const adminAssetUrl = (path?: string | null) => {
    if (!path) {
      return ''
    }

    if (path.startsWith('/assets/craftsmen/')) {
      return `${frontApiBase}${path}`
    }

    if (/^(https?:)?\/\//.test(path) || path.startsWith('data:') || path.startsWith('/assets/')) {
      return path
    }

    return path.startsWith('/') ? `${frontApiBase}${path}` : `${frontApiBase}/${path.replace(/^\/+/, '')}`
  }
  const normalizeProductAssets = (product: Product): Product => ({
    ...product,
    image: adminAssetUrl(product.image),
    gallery: product.gallery?.map((path) => adminAssetUrl(path)).filter(Boolean)
  })
  const normalizeCraftsmanAssets = (craftsman: Craftsman): Craftsman => ({
    ...craftsman,
    image: adminAssetUrl(craftsman.image)
  })
  const mergedTranslations = (remoteTranslations?: Record<LanguageCode, Record<string, string>>) => {
    return Object.fromEntries(languages.map((language) => [
      language.code,
      {
        ...(translations[language.code] || {}),
        ...(remoteTranslations?.[language.code] || {})
      }
    ])) as Record<LanguageCode, Record<string, string>>
  }
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
  const selectedCraftsman = useCookie<Craftsman | null>('interino-selected-craftsman', {
    default: () => null,
    sameSite: 'lax'
  })
  const activeMenuKey = useState<string | null>('active-menu-key', () => null)
  const searchTerm = useState<string>('search-term', () => '')
  const selectedProductImage = useState<string | null>('selected-product-image', () => null)
  const homeRandomSeed = useState<number>('home-random-seed', () => Math.random())

  const catalogConfig = computed<ShopFrontendConfig>(() => ({
    languages: remoteCatalog.value?.languages?.length ? remoteCatalog.value.languages : languages,
    menuGroups: remoteCatalog.value?.menuGroups?.length ? remoteCatalog.value.menuGroups : menuGroups,
    translations: mergedTranslations(remoteCatalog.value?.translations),
    products: (remoteCatalog.value?.products?.length ? remoteCatalog.value.products : products).map(normalizeProductAssets),
    settings: {
      ...defaultSettings,
      ...(remoteCatalog.value?.settings || {}),
      brandLogo: adminAssetUrl(remoteCatalog.value?.settings?.brandLogo || defaultSettings.brandLogo)
    },
    seo: { ...defaultSeo, ...(remoteCatalog.value?.seo || {}) },
    homeSections: Array.isArray(remoteCatalog.value?.homeSections) ? remoteCatalog.value.homeSections : undefined,
    privacy: remoteCatalog.value?.privacy
  }))
  const availableLanguages = computed(() => catalogConfig.value.languages)
  const menuGroupList = computed(() => catalogConfig.value.menuGroups)
  const localizedRecordValue = (record: Record<string, any> | undefined, fallback = '') => {
    if (!record) {
      return fallback
    }

    return record[currentLanguageCode.value] ?? record.hy ?? Object.values(record)[0] ?? fallback
  }
  const groupChildren = (group: ShopFrontendConfig['menuGroups'][number]) => {
    return group.children[currentLanguageCode.value] ?? group.children.hy ?? Object.values(group.children)[0] ?? []
  }
  const primaryProducts = computed(() => catalogConfig.value.products
    .map((product, index) => ({ product, index }))
    .sort((first, second) => {
      const availabilityDiff = Number(Boolean(first.product.isTemporarilyUnavailable)) - Number(Boolean(second.product.isTemporarilyUnavailable))

      return availabilityDiff || first.index - second.index
    })
    .map((item) => item.product))
  const productsByGroupKey = computed(() => {
    const grouped = new Map<string, Product[]>()

    primaryProducts.value.forEach((product) => {
      const key = product.categoryKey || '__uncategorized__'
      grouped.set(key, [...(grouped.get(key) || []), product])
    })

    return grouped
  })
  const hashNumber = (value: string) => {
    let hash = 2166136261

    for (let index = 0; index < value.length; index++) {
      hash ^= value.charCodeAt(index)
      hash = Math.imul(hash, 16777619)
    }

    return hash >>> 0
  }
  const shuffledProducts = (items: Product[], key: string, limit?: number | null) => {
    const sorted = [...items].sort((first, second) => {
      const availabilityDiff = Number(Boolean(first.isTemporarilyUnavailable)) - Number(Boolean(second.isTemporarilyUnavailable))

      if (availabilityDiff) {
        return availabilityDiff
      }

      return hashNumber(`${homeRandomSeed.value}:${key}:${first.id}`) - hashNumber(`${homeRandomSeed.value}:${key}:${second.id}`)
    })
    const normalizedLimit = Number(limit || 0)

    return normalizedLimit > 0 ? sorted.slice(0, normalizedLimit) : sorted
  }
  const selectionProducts = (selection: { categoryKey: string, childIndex?: number | null, limit?: number | null }, key: string) => {
    const products = primaryProducts.value.filter((product) => {
      if (product.categoryKey !== selection.categoryKey) {
        return false
      }

      return selection.childIndex === null
        || selection.childIndex === undefined
        || product.categoryChildIndex === selection.childIndex
    })

    return shuffledProducts(products, key, selection.limit)
  }
  const selectionTitle = (selection: { categoryKey: string, childIndex?: number | null }) => {
    const group = menuGroupList.value.find((item) => item.key === selection.categoryKey)

    if (!group) {
      return ''
    }

    const groupTitle = localizedRecordValue(group.title)

    if (typeof selection.childIndex === 'number') {
      const childTitle = groupChildren(group)[selection.childIndex]

      return childTitle ? `${groupTitle} › ${childTitle}` : groupTitle
    }

    return groupTitle
  }
  const sectionTitle = (selection: { categoryKey: string, childIndex?: number | null }) => {
    const group = menuGroupList.value.find((item) => item.key === selection.categoryKey)

    return group ? localizedRecordValue(group.title) : selectionTitle(selection)
  }
  const productSections = computed(() => {
    const homeSections = (catalogConfig.value.homeSections || [])
      .reduce<Array<{ key: string, title: string, children: string[], products: Product[], lists: Array<{ key: string, title: string, products: Product[] }> }>>((items, section, sectionIndex) => {
        const selections = (section.selections || [])
          .filter((selection) => selection?.categoryKey)
          .slice(0, sectionIndex === 0 ? 2 : 1)

        const lists = selections
          .map((selection, selectionIndex) => {
            const key = `${section.id || sectionIndex}-${selection.id || selectionIndex}-${selection.categoryKey}-${selection.childIndex ?? 'all'}`

            return {
              key,
              title: selectionTitle(selection),
              products: selectionProducts(selection, key)
            }
          })
          .filter((list) => list.products.length)

        if (!lists.length) {
          return items
        }

        items.push({
          key: section.id || `home-section-${sectionIndex}`,
          title: lists.length === 1 ? lists[0].title : sectionTitle(selections[0]),
          children: lists.map((list) => list.title),
          products: lists.flatMap((list) => list.products),
          lists
        })

        return items
      }, [])

    if (homeSections.length) {
      return homeSections
    }

    const sections = menuGroupList.value
      .map((group) => {
        const groupProducts = shuffledProducts(productsByGroupKey.value.get(group.key) || [], group.key, 8)

        return {
          key: group.key,
          title: localizedRecordValue(group.title),
          children: groupChildren(group),
          products: groupProducts,
          lists: [{
            key: group.key,
            title: groupChildren(group)[0] || localizedRecordValue(group.title),
            products: groupProducts
          }]
        }
      })
      .filter((section) => section.products.length)

    if (sections.length) {
      return sections
    }

    return [{
      key: 'all-products',
      title: 'Products',
      children: [],
      products: shuffledProducts(primaryProducts.value, 'all-products'),
      lists: [{
        key: 'all-products',
        title: 'Products',
        products: shuffledProducts(primaryProducts.value, 'all-products')
      }]
    }]
  })
  const secondaryProductList = computed(() => productSections.value[1]?.products || [])
  const shopSettings = computed(() => catalogConfig.value.settings)
  const shopPrivacy = computed(() => catalogConfig.value.privacy)
  const currentLanguage = computed(() => availableLanguages.value.find((language) => language.code === currentLanguageCode.value) ?? availableLanguages.value[0])
  const copy = computed(() => catalogConfig.value.translations[currentLanguageCode.value] ?? translations[currentLanguageCode.value])
  const socialLinks = computed(() => [
    ...(shopSettings.value.socialLinks || []),
    { label: copy.value.privacyPolicy, href: '/privacy-policy', external: false }
  ])
  const categories = computed(() => menuGroupList.value.map((group) => localizedRecordValue(group.title)))
  const allProducts = computed(() => primaryProducts.value)
  const cartKeyForItem = (item: CartItem) => {
    const product = allProducts.value.find((candidate) => candidate.id === item.productId)
    const color = item.color || productColors(product)[0] || null

    return cartItemKey({ productId: item.productId, color, effectivePrice: item.effectivePrice })
  }
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
  const currentProductImage = computed(() => selectedProductImage.value || currentProduct.value?.image || currentProduct.value?.gallery?.[0] || '')
  const detailThumbnails = computed(() => {
    if (!currentProduct.value) {
      return []
    }

    return Array.from(new Set([
      currentProduct.value.image,
      ...(currentProduct.value.gallery || [])
    ].filter(Boolean))).slice(0, 8)
  })
  const relatedProducts = computed(() => primaryProducts.value.filter((product) => product.id !== currentProduct.value?.id))
  const similarProducts = computed(() => allProducts.value.filter((product) => product.id !== currentProduct.value?.id))
  const searchResults = computed(() => {
    const query = searchQuery.value.toLowerCase()

    if (!query) {
      return []
    }

    return allProducts.value.filter((product) => {
      const searchable = [product.kind, ...Object.values(product.title || {})].join(' ').toLowerCase()

      return searchable.includes(query)
    })
  })
  const searchRecommendations = computed(() => primaryProducts.value.slice(0, 8))
  const cartCount = computed(() => cartItems.value.reduce((total, item) => total + item.quantity, 0))
  const cartProducts = computed(() => {
    return cartItems.value
      .map((item) => {
        const product = allProducts.value.find((candidate) => candidate.id === item.productId)
        const color = item.color || productColors(product)[0] || null

        return product ? { ...item, color, key: cartKeyForItem(item), product } : null
      })
      .filter((item): item is CartItem & { key: string, product: Product } => Boolean(item))
  })
  const cartTotal = computed(() => {
    return cartProducts.value.reduce((total, item) => {
      const price = item.effectivePrice != null ? item.effectivePrice : Number(item.product.price)

      return total + price * item.quantity
    }, 0)
  })
  const cartRecommendations = computed(() => allProducts.value
    .filter((product) => !product.isTemporarilyUnavailable && !cartItems.value.some((item) => item.productId === product.id))
    .slice(0, 4))
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

    return groupChildren(currentCategoryGroup.value)[routeValue.childIndex] ?? null
  })
  const currentCategoryChildren = computed(() => currentCategoryGroup.value ? groupChildren(currentCategoryGroup.value) : [])
  const currentProductCategoryGroup = computed(() => {
    if (!currentProduct.value?.categoryKey) {
      return null
    }

    return menuGroupList.value.find((group) => group.key === currentProduct.value?.categoryKey) ?? null
  })
  const currentProductCategoryChild = computed(() => {
    if (!currentProductCategoryGroup.value || typeof currentProduct.value?.categoryChildIndex !== 'number') {
      return null
    }

    return groupChildren(currentProductCategoryGroup.value)[currentProduct.value.categoryChildIndex] ?? null
  })
  const currentProductPriceOptions = computed(() => {
    if (!currentProduct.value?.priceOptions) {
      return []
    }

    return Object.entries(currentProduct.value.priceOptions)
      .map(([key, values]) => ({
        key,
        label: key
          .split('_')
          .filter(Boolean)
          .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
          .join(' '),
        values: Array.isArray(values) ? values : []
      }))
      .filter((group) => group.values.length)
  })
  const isCategoryPage = computed(() => Boolean(currentCategoryGroup.value))
  const currentSeoPageKey = computed(() => {
    const path = withoutLanguagePrefix(route.path)

    if (path === '/') return 'home'
    if (path === '/contact') return 'contact'
    if (path === '/cart') return 'cart'
    if (path === '/checkout-success') return 'checkout-success'
    if (path === '/search') return 'search'
    if (path === '/craftsmen') return 'craftsmen'
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
      : (
          currentCategoryGroup.value?.childMeta?.[currentLanguageCode.value]?.[currentCategoryRoute.value?.childIndex ?? -1]
          ?? currentCategoryGroup.value?.childMeta?.hy?.[currentCategoryRoute.value?.childIndex ?? -1]
        )
    const seo = catalogConfig.value.seo[currentSeoPageKey.value]?.[currentLanguageCode.value]
      ?? catalogConfig.value.seo[currentSeoPageKey.value]?.hy
      ?? defaultSeo[currentSeoPageKey.value]?.[currentLanguageCode.value]
      ?? defaultSeo.home[currentLanguageCode.value]
      ?? defaultSeo.home.hy
    const dynamicTitle = currentProduct.value?.title?.[currentLanguageCode.value]
      || currentCategoryChild.value
      || localizedRecordValue(currentCategoryGroup.value?.title)
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

  function addToCart(product: Product, selectedColor: ProductColor | null = null, effectivePrice?: number | null, selectedOptionLabel?: string | null) {
    if (product.isTemporarilyUnavailable) {
      return
    }

    const color = selectedColor || productColors(product)[0] || null
    const priceToStore = effectivePrice != null ? effectivePrice : null
    const key = cartItemKey({ productId: product.id, color, effectivePrice: priceToStore })
    const existingItem = cartItems.value.find((item) => cartKeyForItem(item) === key)

    if (existingItem) {
      existingItem.quantity += 1
      cartItems.value = [...cartItems.value]
    } else {
      cartItems.value = [...cartItems.value, { productId: product.id, quantity: 1, color, effectivePrice: priceToStore, selectedOptionLabel: selectedOptionLabel ?? null }]
    }

    recentlyAddedProductId.value = product.id

    if (addToCartFeedbackTimer) {
      clearTimeout(addToCartFeedbackTimer)
    }

    addToCartFeedbackTimer = setTimeout(() => {
      recentlyAddedProductId.value = null
    }, 1200)
  }

  function updateCartQuantity(key: string, change: number) {
    cartItems.value = cartItems.value
      .map((item) => cartKeyForItem(item) === key ? { ...item, quantity: item.quantity + change } : item)
      .filter((item) => item.quantity > 0)
  }

  function removeFromCart(key: string) {
    cartItems.value = cartItems.value.filter((item) => cartKeyForItem(item) !== key)
  }

  function clearCart() {
    cartItems.value = []
  }

  function openCategory(groupKey: string, childIndex?: number) {
    router.push(categoryPath(groupKey, childIndex))
  }

  function menuGroupTitle(group: ShopFrontendConfig['menuGroups'][number]) {
    return localizedRecordValue(group.title)
  }

  function menuGroupChildren(group: ShopFrontendConfig['menuGroups'][number]) {
    return groupChildren(group)
  }

  function submitSearch() {
    const query = searchTerm.value.trim()

    router.push(query ? `${localizedPath('/search')}?q=${encodeURIComponent(query)}` : localizedPath('/search'))
  }

  function selectProductImage(image: string) {
    selectedProductImage.value = image
  }

  async function fetchCraftsmen(params: { search?: string, region?: string, city?: string, field?: string, limit?: number } = {}): Promise<CraftsmenResponse> {
    const response = await $fetch<CraftsmenResponse>(frontApiUrl('/api/front/craftsmen'), {
      query: {
        search: params.search?.trim() || undefined,
        region: params.region || undefined,
        city: params.city || undefined,
        field: params.field || undefined,
        limit: params.limit || 100
      }
    })

    return {
      data: (response?.data || []).map(normalizeCraftsmanAssets),
      filters: response?.filters || { regions: [], cities: [], fields: [] }
    }
  }

  async function searchCraftsmen(search: string): Promise<Craftsman[]> {
    const query = search.trim()

    if (!query) {
      return []
    }

    const response = await fetchCraftsmen({ search: query, limit: 10 })

    return response.data || []
  }

  function selectCraftsmanForCheckout(craftsman: Craftsman | null) {
    selectedCraftsman.value = craftsman ? normalizeCraftsmanAssets(craftsman) : null
  }

  async function submitOrder(customer: Record<string, string>, craftsman: Craftsman | null = null) {
    const payload = {
      customer,
      craftsman,
      items: cartProducts.value.map((item) => ({
        productId: item.productId,
        quantity: item.quantity,
        color: item.color,
        effectivePrice: item.effectivePrice ?? null,
        selectedOptionLabel: item.selectedOptionLabel ?? null,
        product: item.product
      })),
      total: cartTotal.value,
      language: currentLanguageCode.value
    }

    await $fetch(frontApiUrl('/api/front/orders'), {
      method: 'POST',
      body: new URLSearchParams({
        payload: JSON.stringify(payload)
      })
    })

    clearCart()
    selectedCraftsman.value = null
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
    currentCategoryChildren,
    currentCategoryGroup,
    currentCategoryRoute,
    currentLanguage,
    currentLanguageCode,
    currentProduct,
    currentProductCategoryChild,
    currentProductCategoryGroup,
    currentProductImage,
    currentProductPriceOptions,
    currentSeo,
    detailThumbnails,
    isCartPage,
    isCategoryPage,
    isContactPage,
    isSearchPage,
    languages: availableLanguages,
    localizedPath,
    menuGroupChildren,
    menuGroupTitle,
    menuGroups: menuGroupList,
    openCategory,
    openProduct,
    productSections,
    products: primaryProducts,
    recentlyAddedProductId,
    relatedProducts,
    removeFromCart,
    searchRecommendations,
    searchResults,
    searchTerm,
    secondaryProducts: secondaryProductList,
    selectedCraftsman,
    selectLanguage,
    selectCraftsmanForCheckout,
    selectProductImage,
    similarProducts,
    shopSettings,
    shopPrivacy,
    socialLinks,
    submitSearch,
    submitOrder,
    fetchCraftsmen,
    searchCraftsmen,
    toggleMenuGroup,
    updateCartQuantity,
    addToCart,
    scrollProductSlider
  }
}
