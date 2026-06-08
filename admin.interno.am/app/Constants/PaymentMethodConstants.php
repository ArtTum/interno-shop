<?php

namespace App\Constants;

class PaymentMethodConstants
{
    const PAYMENT_METHOD_TYPE_BANK_TRANSFER = 1;
    const PAYMENT_METHOD_TYPE_STRIPE = 2;
    const PAYMENT_METHOD_TYPE_PAYPAL = 3;
    const PAYMENT_METHOD_TYPE_CASHBACK = 4;
    const PAYMENT_METHOD_TYPE_GIFT_CARD = 5;
    const PAYMENT_METHOD_TYPE_COP = 6;

    const PAYPAL_NOT_DECIMAL_CURRENCIES = ['HUF', 'JPY', 'TWD'];

    // bank transfer
    const METHOD_BANK_TRANSFER = 'bank_transfer';

    // cop
    const METHOD_COP = 'cop';

    // paypal
    const METHOD_PAYPAL = 'paypal';

    const METHOD_ACSS_DEBIT = 'acss_debit';
    const METHOD_AFFIRM = 'affirm';
    const METHOD_AFTERPAY_CLEARPAY = 'afterpay_clearpay';
    const METHOD_ALIPAY = 'alipay';
    const METHOD_ALMA = 'alma';
    const METHOD_AMAZON_PAY = 'amazon_pay';
    const METHOD_AU_BECS_DEBIT = 'au_becs_debit';
    const METHOD_BACS_DEBIT = 'bacs_debit';
    const METHOD_BANCONTACT = 'bancontact';
    const METHOD_BLIK = 'blik';
    const METHOD_BOLETO = 'boleto';
    const METHOD_CARD = 'card';
    const METHOD_CARD_PRESENT = 'card_present';
    const METHOD_CASHAPP = 'cashapp';
    const METHOD_CUSTOMER_BALANCE = 'customer_balance';
    const METHOD_EPS = 'eps';
    const METHOD_FPX = 'fpx';
    const METHOD_GIROPAY = 'giropay';
    const METHOD_GRABPAY = 'grabpay';
    const METHOD_IDEAL = 'ideal';
    const METHOD_INTERAC_PRESENT = 'interac_present';
    const METHOD_KAKAO_PAY = 'kakao_pay';
    const METHOD_KLARNA = 'klarna';
    const METHOD_KONBINI = 'konbini';
    const METHOD_KR_CARD = 'kr_card';
    const METHOD_LINK = 'link';
    const METHOD_MOBILEPAY = 'mobilepay';
    const METHOD_MULTIBANCO = 'multibanco';
    const METHOD_NAVER_PAY = 'naver_pay';
    const METHOD_OXXO = 'oxxo';
    const METHOD_P24 = 'p24';
    const METHOD_PAYCO = 'payco';
    const METHOD_PAYNOW = 'paynow';
//    const METHOD_PAYPAL = 'paypal';
    const METHOD_PIX = 'pix';
    const METHOD_PROMPTPAY = 'promptpay';
    const METHOD_REVOLUT_PAY = 'revolut_pay';
    const METHOD_SAMSUNG_PAY = 'samsung_pay';
    const METHOD_SEPA_DEBIT = 'sepa_debit';
    const METHOD_SOFORT = 'sofort';
    const METHOD_SWISH = 'swish';
    const METHOD_TWINT = 'twint';
    const METHOD_US_BANK_ACCOUNT = 'us_bank_account';
    const METHOD_WECHAT_PAY = 'wechat_pay';
    const METHOD_ZIP = 'zip';
    const METHOD_APPLE_PAY = 'apple_pay';
    const METHOD_GOOGLE_PAY = 'google_pay';

    const PARENT_PAYMENT_METHODS = [
        self::PAYMENT_METHOD_TYPE_BANK_TRANSFER => 'Bank transfer',
        self::PAYMENT_METHOD_TYPE_COP => 'Cash on Pickup (COP)',
        self::PAYMENT_METHOD_TYPE_STRIPE => 'Stripe',
        self::PAYMENT_METHOD_TYPE_PAYPAL => 'Paypal',
        self::PAYMENT_METHOD_TYPE_CASHBACK => 'Cashback',
        self::PAYMENT_METHOD_TYPE_GIFT_CARD => 'Gift card',
    ];

    const PARENT_PAYMENT_METHODS_FOR_ON_HOLD = [
        'Bank transfer',
        'Cash on Pickup (COP)',
    ];

    const PAYMENT_METHODS = [
        self::METHOD_BANK_TRANSFER => self::PAYMENT_METHOD_TYPE_BANK_TRANSFER,
        self::METHOD_COP => self::PAYMENT_METHOD_TYPE_COP,
        self::METHOD_PAYPAL => self::PAYMENT_METHOD_TYPE_PAYPAL,
        self::METHOD_ALIPAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_BANCONTACT => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_BLIK => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_CARD => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_EPS => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_GIROPAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_IDEAL => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_KLARNA => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_MOBILEPAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_MULTIBANCO => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_P24 => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_REVOLUT_PAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_SEPA_DEBIT => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_SOFORT => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_WECHAT_PAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_GOOGLE_PAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_APPLE_PAY => self::PAYMENT_METHOD_TYPE_STRIPE,
        self::METHOD_AMAZON_PAY => self::PAYMENT_METHOD_TYPE_STRIPE,
    ];
}
