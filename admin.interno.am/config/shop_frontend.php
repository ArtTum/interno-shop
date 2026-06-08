<?php

return [
    'languages' => [
        ['code' => 'hy', 'label' => 'Հայերեն', 'icon' => '/assets/icons/flag-hy.svg'],
        ['code' => 'en', 'label' => 'English', 'icon' => '/assets/icons/flag-en.svg'],
        ['code' => 'ru', 'label' => 'Русский', 'icon' => '/assets/icons/flag-ru.svg'],
    ],
    'menuGroups' => [
        [
            'key' => 'stretch',
            'title' => ['hy' => 'Ձգվող առաստաղներ', 'en' => 'Stretch ceilings', 'ru' => 'Натяжные потолки'],
            'children' => [
                'hy' => ['Պրոֆիլ', 'Ցանց', 'Փայտանյութ', 'Փող'],
                'en' => ['Profile', 'Mesh', 'Wood material', 'Tube'],
                'ru' => ['Профиль', 'Сетка', 'Древесина', 'Труба'],
            ],
        ],
        [
            'key' => 'mdf',
            'title' => ['hy' => 'ՄԴՖ շրիշակ', 'en' => 'MDF skirting', 'ru' => 'МДФ плинтус'],
            'children' => [
                'hy' => ['Սպիտակ', 'Սև', 'Փայտային'],
                'en' => ['White', 'Black', 'Wood look'],
                'ru' => ['Белый', 'Черный', 'Под дерево'],
            ],
        ],
        [
            'key' => 'aluminum',
            'title' => ['hy' => 'Ալյումինե պրոֆիլ', 'en' => 'Aluminum profile', 'ru' => 'Алюминиевый профиль'],
            'children' => [
                'hy' => ['Անկյունային', 'Լուսային', 'Մոնտաժային'],
                'en' => ['Corner', 'Light line', 'Mounting'],
                'ru' => ['Угловой', 'Световой', 'Монтажный'],
            ],
        ],
        [
            'key' => 'lighting',
            'title' => ['hy' => 'Լուսավորություն', 'en' => 'Lighting', 'ru' => 'Освещение'],
            'children' => [
                'hy' => ['LED ժապավեն', 'Լամպեր', 'Աքսեսուարներ'],
                'en' => ['LED strip', 'Lamps', 'Accessories'],
                'ru' => ['LED лента', 'Лампы', 'Аксессуары'],
            ],
        ],
    ],
    'translations' => [
        'hy' => [
            'add' => 'Ավելացնել', 'added' => 'Ավելացվեց', 'addToCartLong' => 'Ավելացնել զամբյուղ', 'address' => 'Առաքման հասցե', 'addressPlaceholder' => 'ք. Երևան...', 'cart' => 'Զամբյուղ', 'cartTitle' => 'Զամբյուղը', 'checkoutSuccessHome' => 'Գլխավոր էջ', 'checkoutSuccessTitle' => 'Վճարումը հաջողությամբ կատարվել է, դուք կստանաք էլեկտրոնային հաղորդագրություն...', 'checkoutDeliveryNote' => 'Ապրանքի առաքման համար մեր աշխատակիցը կկապվի Ձեզ հետ և կհստակեցնի հասցեն։', 'checkoutTitle' => 'Անձնական տվյալներ', 'clearCart' => 'Մաքրել զամբյուղը', 'closeDialog' => 'Փակել պատուհանը', 'closeMenu' => 'Փակել մենյուն', 'contact' => 'Կապ մեզ հետ', 'contactAddress' => 'Հասցե', 'contactEmail' => 'Էլ. հասցե', 'contactFormTitle' => 'Ուղարկել հաղորդագրություն', 'contactHeroKicker' => 'Կապ մեզ հետ', 'contactHeroText' => 'Գրեք մեզ կամ զանգահարեք, և մենք կօգնենք ընտրել ճիշտ նյութը, չափը և քանակը։', 'contactHeroTitle' => 'Մենք պատրաստ ենք օգնել ձեր պատվերի հարցում', 'contactHours' => 'Աշխատանքային ժամեր', 'contactHoursValue' => 'Երկ - Շաբ · 10:00 - 19:00', 'contactInfoTitle' => 'Կոնտակտային տվյալներ', 'contactMessage' => 'Հաղորդագրություն', 'contactMessagePlaceholder' => 'Գրեք ինչ ապրանք կամ չափ է պետք', 'contactNamePlaceholder' => 'Ձեր անունը', 'contactPhone' => 'Հեռախոս', 'contactPhonePlaceholder' => '+374 __ ___ ___', 'contactSend' => 'Ուղարկել', 'email' => 'Էլեկտրոնային հասցե', 'emailError' => 'Էլ. հասցեն սխալ է', 'emptyCartTitle' => 'Զամբյուղը դատարկ է', 'firstName' => 'Անուն', 'firstNamePlaceholder' => 'Անուն', 'home' => 'Գլխավոր', 'lastName' => 'Ազգանուն', 'linePrice' => 'Գին', 'masterCode' => 'Վարպետի կոդ', 'materialName' => 'Նյութի անուն', 'menu' => 'Մենյու', 'new' => 'Նորույթ', 'nextProducts' => 'Հաջորդ ապրանքներ', 'openMenu' => 'Բացել մենյուն', 'optionCode' => 'կոդ', 'optionColor' => 'Գույն', 'optionHeight' => 'Բարձրություն', 'optionMaterial' => 'Նյութ', 'optionPiece' => 'Հատիկ', 'optionQuantity' => 'Քանակ', 'optionSize' => 'Չափ', 'optionType' => 'Տեսակ', 'optionTypeName' => 'Տեսակի անուն', 'optionUnit' => 'Չափ. միավոր', 'optionUnitLong' => 'Չափի միավ.', 'pay' => 'Վճարել', 'paymentDue' => 'Գումար', 'paymentDueAmount' => 'Վճարման ենթակա գումար՝', 'phone' => 'Հեռախոսահամար', 'previousProducts' => 'Նախորդ ապրանքներ', 'privacyPolicy' => 'Գաղտնիության քաղաքականություն', 'profile' => 'Պրոֆիլ', 'recommendationsTitle' => 'Ձեզ կարող է դուր գալ', 'relatedProducts' => 'Փոխկապակցված ապրանքներ', 'requiredField' => 'Պարտադիր դաշտ է', 'search' => 'Որոնել', 'searchEmptyTitle' => 'Որոնման արդյունքները չգտնվեցին', 'searchHomeLink' => 'Գլխավոր էջ', 'searchRecommendations' => 'Առաջարկվող ապրանքներ', 'searchResultsTitle' => 'Որոնման արդյունքներ', 'similarProducts' => 'Նման ապրանքներ', 'sliderControls' => 'Սլայդերի կառավարում', 'social' => 'Instagram · Facebook · Privacy Policy'
        ],
        'en' => [
            'add' => 'Add', 'added' => 'Added', 'addToCartLong' => 'Add to cart', 'address' => 'Delivery address', 'addressPlaceholder' => 'Yerevan...', 'cart' => 'Cart', 'cartTitle' => 'Cart', 'checkoutSuccessHome' => 'Home page', 'checkoutSuccessTitle' => 'Payment was completed successfully. You will receive an email notification...', 'checkoutDeliveryNote' => 'For product delivery, our employee will contact you and confirm the address.', 'checkoutTitle' => 'Personal details', 'clearCart' => 'Clear cart', 'closeDialog' => 'Close dialog', 'closeMenu' => 'Close menu', 'contact' => 'Contact us', 'contactAddress' => 'Address', 'contactEmail' => 'Email', 'contactFormTitle' => 'Send a message', 'contactHeroKicker' => 'Contact us', 'contactHeroText' => 'Write to us or call, and we will help you choose the right material, size, and quantity.', 'contactHeroTitle' => 'We are ready to help with your order', 'contactHours' => 'Working hours', 'contactHoursValue' => 'Mon - Sat · 10:00 - 19:00', 'contactInfoTitle' => 'Contact details', 'contactMessage' => 'Message', 'contactMessagePlaceholder' => 'Write what product or size you need', 'contactNamePlaceholder' => 'Your name', 'contactPhone' => 'Phone', 'contactPhonePlaceholder' => '+374 __ ___ ___', 'contactSend' => 'Send', 'email' => 'Email address', 'emailError' => 'Email address is invalid', 'emptyCartTitle' => 'Cart is empty', 'firstName' => 'First name', 'firstNamePlaceholder' => 'First name', 'home' => 'Home', 'lastName' => 'Last name', 'linePrice' => 'Price', 'masterCode' => 'Master code', 'materialName' => 'Material name', 'menu' => 'Menu', 'new' => 'New', 'nextProducts' => 'Next products', 'openMenu' => 'Open menu', 'optionCode' => 'Code', 'optionColor' => 'Color', 'optionHeight' => 'Height', 'optionMaterial' => 'Material', 'optionPiece' => 'Piece', 'optionQuantity' => 'Quantity', 'optionSize' => 'Size', 'optionType' => 'Type', 'optionTypeName' => 'Type name', 'optionUnit' => 'Unit', 'optionUnitLong' => 'Unit', 'pay' => 'Pay', 'paymentDue' => 'Payment due', 'paymentDueAmount' => 'Payment due:', 'phone' => 'Phone number', 'previousProducts' => 'Previous products', 'privacyPolicy' => 'Privacy Policy', 'profile' => 'Profile', 'recommendationsTitle' => 'You may also like', 'relatedProducts' => 'Related products', 'requiredField' => 'Required field', 'search' => 'Search', 'searchEmptyTitle' => 'No search results found', 'searchHomeLink' => 'Home page', 'searchRecommendations' => 'Recommended products', 'searchResultsTitle' => 'Search results', 'similarProducts' => 'Similar products', 'sliderControls' => 'Slider controls', 'social' => 'Instagram · Facebook · Privacy Policy'
        ],
        'ru' => [
            'add' => 'Добавить', 'added' => 'Добавлено', 'addToCartLong' => 'Добавить в корзину', 'address' => 'Адрес доставки', 'addressPlaceholder' => 'г. Ереван...', 'cart' => 'Корзина', 'cartTitle' => 'Корзина', 'checkoutSuccessHome' => 'Главная страница', 'checkoutSuccessTitle' => 'Оплата успешно выполнена, вы получите электронное уведомление...', 'checkoutDeliveryNote' => 'Для доставки товара наш сотрудник свяжется с вами и уточнит адрес.', 'checkoutTitle' => 'Личные данные', 'clearCart' => 'Очистить корзину', 'closeDialog' => 'Закрыть окно', 'closeMenu' => 'Закрыть меню', 'contact' => 'Связаться', 'contactAddress' => 'Адрес', 'contactEmail' => 'Эл. почта', 'contactFormTitle' => 'Отправить сообщение', 'contactHeroKicker' => 'Свяжитесь с нами', 'contactHeroText' => 'Напишите или позвоните нам, и мы поможем выбрать правильный материал, размер и количество.', 'contactHeroTitle' => 'Мы готовы помочь с вашим заказом', 'contactHours' => 'Рабочие часы', 'contactHoursValue' => 'Пн - Сб · 10:00 - 19:00', 'contactInfoTitle' => 'Контактные данные', 'contactMessage' => 'Сообщение', 'contactMessagePlaceholder' => 'Напишите, какой товар или размер вам нужен', 'contactNamePlaceholder' => 'Ваше имя', 'contactPhone' => 'Телефон', 'contactPhonePlaceholder' => '+374 __ ___ ___', 'contactSend' => 'Отправить', 'email' => 'Электронная почта', 'emailError' => 'Неверный адрес эл. почты', 'emptyCartTitle' => 'Корзина пуста', 'firstName' => 'Имя', 'firstNamePlaceholder' => 'Имя', 'home' => 'Главная', 'lastName' => 'Фамилия', 'linePrice' => 'Цена', 'masterCode' => 'Код мастера', 'materialName' => 'Название материала', 'menu' => 'Меню', 'new' => 'Новинка', 'nextProducts' => 'Следующие товары', 'openMenu' => 'Открыть меню', 'optionCode' => 'Код', 'optionColor' => 'Цвет', 'optionHeight' => 'Высота', 'optionMaterial' => 'Материал', 'optionPiece' => 'Штука', 'optionQuantity' => 'Количество', 'optionSize' => 'Размер', 'optionType' => 'Тип', 'optionTypeName' => 'Название типа', 'optionUnit' => 'Ед. изм.', 'optionUnitLong' => 'Ед. изм.', 'pay' => 'Оплатить', 'paymentDue' => 'К оплате', 'paymentDueAmount' => 'Сумма к оплате:', 'phone' => 'Номер телефона', 'previousProducts' => 'Предыдущие товары', 'privacyPolicy' => 'Политика конфиденциальности', 'profile' => 'Профиль', 'recommendationsTitle' => 'Вам может понравиться', 'relatedProducts' => 'Связанные товары', 'requiredField' => 'Обязательное поле', 'search' => 'Поиск', 'searchEmptyTitle' => 'Результаты поиска не найдены', 'searchHomeLink' => 'Главная страница', 'searchRecommendations' => 'Рекомендуемые товары', 'searchResultsTitle' => 'Результаты поиска', 'similarProducts' => 'Похожие товары', 'sliderControls' => 'Управление слайдером', 'social' => 'Instagram · Facebook · Privacy Policy'
        ],
    ],
    'products' => array_map(function ($product) {
        return array_merge([
            'options' => ['code' => '111', 'size' => 'Պրոֆիլ', 'quantity' => '1', 'type' => 'Տեսակ', 'unit' => '111', 'piece' => '44', 'height' => '111', 'material' => 'Նյութի անուն', 'color' => '#1f1f1f'],
        ], $product);
    }, [
        ['id' => 1, 'title' => ['hy' => 'Ալյումինե, երկաթե հիմք նուրբ սև գույնով', 'en' => 'Aluminum profile with a refined black finish', 'ru' => 'Алюминиевый профиль с черным покрытием'], 'price' => '15000', 'kind' => 'black', 'image' => '/assets/products/profile-black.png', 'isNew' => true],
        ['id' => 2, 'title' => ['hy' => 'Արծաթագույն պրոֆիլ ձգվող առաստաղների համար', 'en' => 'Silver profile for stretch ceiling systems', 'ru' => 'Серебристый профиль для натяжных потолков'], 'price' => '15000', 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png', 'isNew' => true],
        ['id' => 3, 'title' => ['hy' => 'Սև անկյունային պրոֆիլ ճշգրիտ ամրացման համար', 'en' => 'Black corner profile for precise installation', 'ru' => 'Черный угловой профиль для точного монтажа'], 'price' => '15000', 'kind' => 'black', 'image' => '/assets/products/profile-black.png', 'isNew' => true],
        ['id' => 4, 'title' => ['hy' => 'Արծաթագույն ամուր պրոֆիլ լուսային գծերի համար', 'en' => 'Durable silver profile for light lines', 'ru' => 'Прочный серебристый профиль для световых линий'], 'price' => '15000', 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png', 'isNew' => true],
        ['id' => 5, 'title' => ['hy' => 'Փայտային պանել պատերի և առաստաղի հարդարման համար', 'en' => 'Wood-look panel for wall and ceiling finishes', 'ru' => 'Панель под дерево для стен и потолков'], 'price' => '15000', 'kind' => 'wood', 'image' => '/assets/products/panel-wood.png', 'isNew' => true],
        ['id' => 6, 'title' => ['hy' => 'Սև պրոֆիլ մինիմալ ինտերիերի լուծումների համար', 'en' => 'Black profile for minimal interior solutions', 'ru' => 'Черный профиль для минималистичных интерьеров'], 'price' => '15000', 'kind' => 'black', 'image' => '/assets/products/profile-black.png', 'isNew' => true],
        ['id' => 7, 'title' => ['hy' => 'Արծաթագույն մոնտաժային պրոֆիլ արագ տեղադրման համար', 'en' => 'Silver mounting profile for fast installation', 'ru' => 'Серебристый монтажный профиль для быстрой установки'], 'price' => '15000', 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png', 'isNew' => true],
        ['id' => 8, 'title' => ['hy' => 'Սև պրոֆիլ թաքնված ամրացման համակարգերի համար', 'en' => 'Black profile for concealed mounting systems', 'ru' => 'Черный профиль для скрытых систем крепления'], 'price' => '15000', 'kind' => 'black', 'image' => '/assets/products/profile-black.png', 'isNew' => true],
        ['id' => 9, 'title' => ['hy' => 'Արծաթագույն պրոֆիլ LED լուսավորության ալիքով', 'en' => 'Silver profile with LED lighting channel', 'ru' => 'Серебристый профиль с каналом для LED'], 'price' => '15000', 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png', 'isNew' => true],
        ['id' => 10, 'title' => ['hy' => 'Փայտային դեկորատիվ պանել տաք երանգով', 'en' => 'Warm-tone decorative wood-look panel', 'ru' => 'Декоративная панель под дерево теплого оттенка'], 'price' => '15000', 'kind' => 'wood', 'image' => '/assets/products/panel-wood.png', 'isNew' => true],
    ]),
    'settings' => [
        'brandLogo' => '/assets/brand/logo.png',
        'footerText' => '123 Ceiling Ave, Yerevan · +374 10 234 567',
        'contactPhone' => '+374 10 234 567',
        'contactEmail' => 'info@interino.am',
        'contactAddress' => '123 Ceiling Ave, Yerevan',
        'contactMapUrl' => 'https://maps.google.com/?q=Yerevan',
        'socialLinks' => [
            ['label' => 'Instagram', 'href' => 'https://www.instagram.com/', 'external' => true],
            ['label' => 'Facebook', 'href' => 'https://www.facebook.com/', 'external' => true],
        ],
    ],
    'privacy' => [
        'updatedAt' => '03.06.2026',
        'content' => [
            'hy' => [
                'kicker' => 'Գաղտնիության քաղաքականություն', 'title' => 'Ձեր տվյալների անվտանգ օգտագործումը մեզ համար կարևոր է', 'intro' => 'Այս էջում ներկայացված է, թե ինչ տվյալներ կարող ենք հավաքել, ինչպես ենք օգտագործում դրանք և ինչ իրավունքներ ունեք դուք։', 'badgeTitle' => 'Անվտանգ պատվերներ', 'badgeText' => 'Պատվերի տվյալները մշակվում են միայն սպասարկման համար', 'summaryLabel' => 'Կարճ ամփոփում', 'summaryTitle' => 'Մենք օգտագործում ենք միայն անհրաժեշտ տվյալները', 'summaryText' => 'Տվյալները կիրառվում են պատվերների մշակման, կապ հաստատելու և սպասարկման որակը բարելավելու համար։', 'checklist' => ['Չենք վաճառում տվյալները', 'Պահում ենք անհրաժեշտ չափով', 'Կապի տվյալները միայն պատվերի համար են'], 'updated' => 'Վերջին թարմացում', 'summaryAria' => 'Կարճ ամփոփում', 'checklistAria' => 'Հիմնական սկզբունքներ',
                'sections' => [
                    ['index' => '01', 'icon' => 'ID', 'title' => 'Ինչ տվյալներ ենք հավաքում', 'text' => 'Կարող ենք հավաքել ձեր անունը, հեռախոսահամարը, էլ. հասցեն, առաքման կամ կապի հասցեն և պատվերի հետ կապված նշումները։'],
                    ['index' => '02', 'icon' => '↗', 'title' => 'Ինչպես ենք օգտագործում տվյալները', 'text' => 'Տվյալներն օգտագործվում են պատվերը հաստատելու, ապրանքի հասանելիությունը ճշտելու, առաքումը կազմակերպելու և անհրաժեշտության դեպքում ձեզ հետ կապվելու համար։'],
                    ['index' => '03', 'icon' => '↔', 'title' => 'Տվյալների փոխանցում', 'text' => 'Մենք չենք վաճառում ձեր անձնական տվյալները։ Դրանք կարող են փոխանցվել միայն այն գործընկերներին, որոնք ներգրավված են պատվերի կատարման կամ առաքման գործընթացում։'],
                    ['index' => '04', 'icon' => '⌁', 'title' => 'Պահպանում և անվտանգություն', 'text' => 'Տվյալները պահվում են այնքան ժամանակ, որքան անհրաժեշտ է պատվերի սպասարկման և հաշվառման համար։ Մենք կիրառում ենք ողջամիտ միջոցներ դրանք պաշտպանելու համար։'],
                    ['index' => '05', 'icon' => '?', 'title' => 'Ձեր իրավունքները', 'text' => 'Դուք կարող եք կապ հաստատել մեզ հետ՝ ձեր տվյալները ճշտելու, թարմացնելու կամ հեռացնելու խնդրանքով։ Հարցերի դեպքում կարող եք գրել կամ զանգահարել մեզ։'],
                ],
            ],
            'en' => [
                'kicker' => 'Privacy policy', 'title' => 'Safe use of your data is important to us', 'intro' => 'This page explains what data we may collect, how we use it, and what rights you have.', 'badgeTitle' => 'Secure orders', 'badgeText' => 'Order data is processed only for service purposes', 'summaryLabel' => 'Short summary', 'summaryTitle' => 'We use only the necessary data', 'summaryText' => 'Data is used to process orders, contact customers, and improve service quality.', 'checklist' => ['We do not sell data', 'We keep data only as needed', 'Contact details are used only for orders'], 'updated' => 'Last updated', 'summaryAria' => 'Short summary', 'checklistAria' => 'Main principles',
                'sections' => [
                    ['index' => '01', 'icon' => 'ID', 'title' => 'What data we collect', 'text' => 'We may collect your name, phone number, email address, delivery or contact address, and notes related to the order.'],
                    ['index' => '02', 'icon' => '↗', 'title' => 'How we use data', 'text' => 'Data is used to confirm the order, check product availability, organize delivery, and contact you when needed.'],
                    ['index' => '03', 'icon' => '↔', 'title' => 'Data sharing', 'text' => 'We do not sell your personal data. It may be shared only with partners involved in order fulfillment or delivery.'],
                    ['index' => '04', 'icon' => '⌁', 'title' => 'Storage and security', 'text' => 'Data is kept for as long as needed for order service and accounting. We use reasonable measures to protect it.'],
                    ['index' => '05', 'icon' => '?', 'title' => 'Your rights', 'text' => 'You can contact us to request correction, update, or deletion of your data. If you have questions, you can write or call us.'],
                ],
            ],
            'ru' => [
                'kicker' => 'Политика конфиденциальности', 'title' => 'Безопасное использование ваших данных важно для нас', 'intro' => 'На этой странице указано, какие данные мы можем собирать, как используем их и какие права у вас есть.', 'badgeTitle' => 'Безопасные заказы', 'badgeText' => 'Данные заказа обрабатываются только для обслуживания', 'summaryLabel' => 'Краткое резюме', 'summaryTitle' => 'Мы используем только необходимые данные', 'summaryText' => 'Данные используются для обработки заказов, связи с клиентами и улучшения качества обслуживания.', 'checklist' => ['Мы не продаем данные', 'Храним данные только по необходимости', 'Контактные данные используются только для заказа'], 'updated' => 'Последнее обновление', 'summaryAria' => 'Краткое резюме', 'checklistAria' => 'Основные принципы',
                'sections' => [
                    ['index' => '01', 'icon' => 'ID', 'title' => 'Какие данные мы собираем', 'text' => 'Мы можем собирать ваше имя, номер телефона, адрес электронной почты, адрес доставки или связи, а также примечания к заказу.'],
                    ['index' => '02', 'icon' => '↗', 'title' => 'Как мы используем данные', 'text' => 'Данные используются для подтверждения заказа, проверки наличия товара, организации доставки и связи с вами при необходимости.'],
                    ['index' => '03', 'icon' => '↔', 'title' => 'Передача данных', 'text' => 'Мы не продаем ваши персональные данные. Они могут передаваться только партнерам, участвующим в выполнении или доставке заказа.'],
                    ['index' => '04', 'icon' => '⌁', 'title' => 'Хранение и безопасность', 'text' => 'Данные хранятся столько, сколько необходимо для обслуживания заказа и учета. Мы применяем разумные меры для их защиты.'],
                    ['index' => '05', 'icon' => '?', 'title' => 'Ваши права', 'text' => 'Вы можете связаться с нами, чтобы запросить исправление, обновление или удаление ваших данных. По вопросам можно написать или позвонить нам.'],
                ],
            ],
        ],
    ],
];
