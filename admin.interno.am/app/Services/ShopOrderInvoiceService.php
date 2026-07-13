<?php

namespace App\Services;

use App\Models\ShopOrder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ShopOrderInvoiceService
{
    private const LANGUAGES = ['hy', 'en', 'ru'];

    public function makePdf(ShopOrder $order): string
    {
        $tempDir = storage_path('app/mpdf-temp');

        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'tempDir' => $tempDir,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->SetTitle($this->invoiceNumber($order));
        $mpdf->WriteHTML($this->renderHtml($order));

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    public function renderHtml(ShopOrder $order): string
    {
        return View::make('shop.orders.invoice', $this->viewData($order))->render();
    }

    public function sendInvoice(ShopOrder $order): void
    {
        $pdf = $this->makePdf($order);
        $filename = $this->filename($order);
        $data = $this->viewData($order);

        foreach ($this->recipients($order) as $recipient) {
            Mail::send('emails.shop_order_invoice', $data, function ($message) use ($recipient, $order, $pdf, $filename, $data) {
                $message
                    ->to($recipient)
                    ->subject($this->subject($order, $data['language']))
                    ->attachData($pdf, $filename, ['mime' => 'application/pdf']);
            });
        }
    }

    public function filename(ShopOrder $order): string
    {
        return sprintf('interno-invoice-%s.pdf', str_pad((string)$order->id, 6, '0', STR_PAD_LEFT));
    }

    public function viewData(ShopOrder $order): array
    {
        $language = $this->language($order->language);
        $labels = $this->labels($language);

        return [
            'order' => $order,
            'language' => $language,
            'labels' => $labels,
            'invoiceNumber' => $this->invoiceNumber($order),
            'createdAt' => $order->created_at?->format('d.m.Y H:i') ?? '',
            'customer' => $order->customer,
            'craftsman' => $order->craftsman_snapshot,
            'items' => collect($order->items ?? [])
                ->map(fn ($item) => $this->normalizeItem(is_array($item) ? $item : [], $language))
                ->values()
                ->all(),
            'total' => (float)$order->total,
            'company' => [
                'name' => 'INTERNO',
                'subtitle' => 'Creative Interior Solutions',
                'phone' => config('shop_frontend.settings.contactPhone', '+374 44 121228'),
                'email' => config('shop_frontend.settings.contactEmail', 'info@interno.am'),
                'address' => config('shop_frontend.settings.contactAddress', 'Yerevan, Armenia'),
            ],
            'deliveryNote' => $labels['deliveryNote'],
        ];
    }

    private function normalizeItem(array $item, string $language): array
    {
        $product = is_array($item['product'] ?? null) ? $item['product'] : [];
        $quantity = max(1, (int)($item['quantity'] ?? 1));
        $unitPrice = (float)($item['effectivePrice'] ?? $product['price'] ?? 0);

        return [
            'productId' => $item['productId'] ?? ($product['id'] ?? null),
            'title' => $this->productTitle($product, $item, $language),
            'quantity' => $quantity,
            'unitPrice' => $unitPrice,
            'lineTotal' => $unitPrice * $quantity,
            'selectedOptionLabel' => $this->stringValue($item['selectedOptionLabel'] ?? null),
            'options' => $this->itemOptions($item, $product, $language),
        ];
    }

    private function itemOptions(array $item, array $product, string $language): array
    {
        $options = [];

        foreach (($item['selectedOptions'] ?? []) as $selectedOption) {
            if (!is_array($selectedOption)) {
                continue;
            }

            $label = $this->stringValue($selectedOption['label'] ?? $selectedOption['key'] ?? '');
            $value = $this->stringValue($selectedOption['value'] ?? '');

            if ($label !== '' && $value !== '') {
                $options[] = [
                    'key' => $this->stringValue($selectedOption['key'] ?? $label),
                    'label' => $label,
                    'value' => $value,
                    'color' => null,
                ];
            }
        }

        $productOptions = is_array($product['options'] ?? null) ? $product['options'] : [];
        $staticKeys = ['code', 'material', 'type', 'size', 'height', 'unit', 'power'];

        foreach ($staticKeys as $key) {
            $value = $this->stringValue($productOptions[$key] ?? null);

            if ($value === '') {
                continue;
            }

            $entry = [
                'key' => $key,
                'label' => $this->optionLabel($key, $language),
                'value' => $value,
                'color' => null,
            ];

            if (!$this->hasSameOption($options, $entry)) {
                $options[] = $entry;
            }
        }

        $color = is_array($item['color'] ?? null) ? $item['color'] : [];
        $colorValue = $this->stringValue($color['value'] ?? $productOptions['color'] ?? null);
        $colorName = $this->stringValue($color['name'] ?? null);

        if ($colorValue !== '' || $colorName !== '') {
            $options[] = [
                'key' => 'color',
                'label' => $this->optionLabel('color', $language),
                'value' => trim($colorName . ($colorName && $colorValue ? ' / ' : '') . $colorValue),
                'color' => $colorValue,
            ];
        }

        return $options;
    }

    private function hasSameOption(array $options, array $candidate): bool
    {
        foreach ($options as $option) {
            if (
                mb_strtolower($option['label'] ?? '') === mb_strtolower($candidate['label'] ?? '')
                && mb_strtolower($option['value'] ?? '') === mb_strtolower($candidate['value'] ?? '')
            ) {
                return true;
            }
        }

        return false;
    }

    private function productTitle(array $product, array $item, string $language): string
    {
        $title = $product['title'] ?? null;

        if (is_string($title) && trim($title) !== '') {
            return trim($title);
        }

        if (is_array($title)) {
            return $this->stringValue($title[$language] ?? $title['hy'] ?? $title['en'] ?? reset($title));
        }

        return 'Product #' . ($item['productId'] ?? $product['id'] ?? '');
    }

    private function optionLabel(string $key, string $language): string
    {
        $labels = [
            'hy' => [
                'code' => 'Կոդ',
                'material' => 'Նյութ',
                'type' => 'Տեսակ',
                'size' => 'Չափս',
                'height' => 'Բարձրություն',
                'unit' => 'Չափի միավոր',
                'power' => 'Հզորություն',
                'color' => 'Գույն',
            ],
            'en' => [
                'code' => 'Code',
                'material' => 'Material',
                'type' => 'Type',
                'size' => 'Size',
                'height' => 'Height',
                'unit' => 'Measurement unit',
                'power' => 'Power',
                'color' => 'Color',
            ],
            'ru' => [
                'code' => 'Код',
                'material' => 'Материал',
                'type' => 'Тип',
                'size' => 'Размер',
                'height' => 'Высота',
                'unit' => 'Единица измерения',
                'power' => 'Мощность',
                'color' => 'Цвет',
            ],
        ];

        return $labels[$language][$key] ?? $key;
    }

    private function labels(string $language): array
    {
        return [
            'hy' => [
                'invoice' => 'Հաշիվ',
                'order' => 'Պատվեր',
                'orderNumber' => 'Պատվերի համար',
                'date' => 'Ամսաթիվ',
                'customer' => 'Հաճախորդ',
                'phone' => 'Հեռախոս',
                'email' => 'Էլ. հասցե',
                'address' => 'Առաքման հասցե',
                'craftsman' => 'Արհեստավոր',
                'product' => 'Ապրանք',
                'parameters' => 'Պարամետրեր',
                'quantity' => 'Քանակ',
                'unitPrice' => 'Գին',
                'total' => 'Ընդամենը',
                'grandTotal' => 'Վճարման ենթակա',
                'thankYou' => 'Շնորհակալություն պատվերի համար',
                'emailIntro' => 'Ձեր պատվերը ընդունված է։ Հաշիվը կցված է PDF ֆայլով։',
                'deliveryNote' => 'Առաքումն իրականացվում է Yandex-ի գործող սակագներով։ Մանրամասների համար կապվեք +374 44 121228 համարով։',
            ],
            'en' => [
                'invoice' => 'Invoice',
                'order' => 'Order',
                'orderNumber' => 'Order number',
                'date' => 'Date',
                'customer' => 'Customer',
                'phone' => 'Phone',
                'email' => 'Email',
                'address' => 'Delivery address',
                'craftsman' => 'Craftsman',
                'product' => 'Product',
                'parameters' => 'Parameters',
                'quantity' => 'Qty',
                'unitPrice' => 'Price',
                'total' => 'Total',
                'grandTotal' => 'Payment due',
                'thankYou' => 'Thank you for your order',
                'emailIntro' => 'Your order has been received. The invoice is attached as a PDF file.',
                'deliveryNote' => 'Delivery is carried out according to Yandex’s current rates. For details, please contact +374 44 121228.',
            ],
            'ru' => [
                'invoice' => 'Счет',
                'order' => 'Заказ',
                'orderNumber' => 'Номер заказа',
                'date' => 'Дата',
                'customer' => 'Клиент',
                'phone' => 'Телефон',
                'email' => 'Эл. почта',
                'address' => 'Адрес доставки',
                'craftsman' => 'Мастер',
                'product' => 'Товар',
                'parameters' => 'Параметры',
                'quantity' => 'Кол-во',
                'unitPrice' => 'Цена',
                'total' => 'Сумма',
                'grandTotal' => 'К оплате',
                'thankYou' => 'Спасибо за заказ',
                'emailIntro' => 'Ваш заказ принят. Счет прикреплен PDF-файлом.',
                'deliveryNote' => 'Доставка осуществляется по действующим тарифам Yandex. За подробностями обращайтесь по номеру +374 44 121228.',
            ],
        ][$language];
    }

    private function subject(ShopOrder $order, string $language): string
    {
        $labels = $this->labels($language);

        return sprintf('%s %s - INTERNO', $labels['invoice'], $this->invoiceNumber($order));
    }

    private function invoiceNumber(ShopOrder $order): string
    {
        return 'INV-' . str_pad((string)$order->id, 6, '0', STR_PAD_LEFT);
    }

    private function recipients(ShopOrder $order): array
    {
        $adminEmails = config('shop_frontend.order_mail_to') ?: config('mail.from.address');
        $emails = array_merge([$order->customer_email], preg_split('/[,;]/', (string)$adminEmails) ?: []);

        return collect($emails)
            ->map(fn ($email) => trim((string)$email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique(fn ($email) => mb_strtolower($email))
            ->values()
            ->all();
    }

    private function language(?string $language): string
    {
        return in_array($language, self::LANGUAGES, true) ? $language : 'hy';
    }

    private function stringValue($value): string
    {
        if (is_array($value)) {
            foreach (['name', 'label', 'title', 'value'] as $key) {
                if (isset($value[$key])) {
                    return $this->stringValue($value[$key]);
                }
            }

            return collect($value)
                ->map(fn ($item) => $this->stringValue($item))
                ->filter()
                ->implode(', ');
        }

        return trim((string)($value ?? ''));
    }
}
