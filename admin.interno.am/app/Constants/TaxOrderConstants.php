<?php

namespace App\Constants;

class TaxOrderConstants
{
    const GIFT_CARD = 'gift_card';
    const ACCOUNT_PAYMENTS_MAPPING = [
        'paypal' => '1000060',
        'stripe' => '1000070',
        'bank_transfer' => '1000090',
        'invoice_payment' => '1000500',
        'amazon' => '1000300',
        'cash_on_pickup_cop' => '1000600',
        self::GIFT_CARD => '1000050',
    ];

//•	1000050 = Payment via voucher
//•	1000060 = Payment via PayPal
//•	1000070 = Payment via Stripe
//•	1000080 = Payment via Amazon
//•	1000090 = Payment via bank transfer
//•	1000100 = Payment via eBay Payments
//•	1000300 = Payment via Amazon Pay
//•	1000400 = B2B
//•	1000500 = Purchase on account
//•	1000600 = Cash on Pickup


    const NON_TAX_COUNTRIES = ['CH', 'NO', 'LI', 'MC', 'AM'];

    const EU_COUNTRIES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK',
    ];

    const CONTRA_CODES = [
        'notax' => '812000',
        'gbless135vat' => '812003',
        'gbless135' => '812001',
        'gbmore135' => '812002',
        'euhasvat' => '812500',
        'eunovat' => '832000',
        'de' => '840000',
        'giftcard' => '179600',
    ];

//•	812000 = Export sales (outside the EU)
//•	840000 = Domestic sales (19% VAT)
//•	812001 = UK sales up to 135 GBP (20% UK VAT)
//•	812002 = UK sales over 135 GBP (20% UK VAT)
//•	812003 = UK sales up to 135 GBP (Reverse Charge, companies with a UK VAT ID)
//•	812500 = EU export sales (tax-free EU deliveries, companies with a VAT ID of the respective country)
//•	832000 = EU export sales (private customers in the EU, VAT charged according to the respective country's tax rate)
}
