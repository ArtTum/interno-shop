<?php

namespace Database\Seeders;

use App\Models\EmailSetting;
use Illuminate\Database\Seeder;

class EmailSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const ARRAY = [
        [
            'type' => 'order_on_hold',
            'name' => 'Order on hold',
        ],
        [
            'type' => 'order_processing',
            'name' => 'Order processing',
        ],
        [
            'type' => 'order_completed',
            'name' => 'Order completed',
        ],
        [
            'type' => 'admin_new_order',
            'name' => 'Admin new order',
        ],
        [
            'type' => 'order_refund',
            'name' => 'Order refund',
        ],
        [
            'type' => 'account_reset_password ',
            'name' => 'Account reset password ',
        ],
        [
            'type' => 'email_confirmation_review_gust',
            'name' => 'Email confirmation review gust',
        ],
        [
            'type' => 'order_cancelled',
            'name' => 'Order cancelled',
        ],
        [
            'type' => 'offer_created_email',
            'name' => 'Order created email',
        ],
        [
            'type' => 'offer_updated_email',
            'name' => 'Order updated email',
        ],
        [
            'type' => 'gift_card_email',
            'name' => 'Gift card email',
        ],
        [
            'type' => 'order_customer_email',
            'name' => 'Email to customer',
        ],
        [
            'type' => 'registration_confirmation_email',
            'name' => 'Registration Confirmation',
        ],
        [
            'type' => 'registration_confirmation_email',
            'name' => 'Registration Confirmation',
        ],
        [
            'type' => 'loyalty_club_order_confirmation_email',
            'name' => 'Loyalty Club - Order Confirmation ',
        ],
        [
            'type' => 'loyalty_club_new_level_email',
            'name' => 'Loyalty Club – New Level',
        ],
        [
            'type' => 'loyalty_club_cashback_email',
            'name' => 'Cashback Credit Notification',
        ],
        [
            'type' => 'cart_share_to_email',
            'name' => 'Cart share to email',
        ],
    ];

    public function run(): void
    {
        foreach (self::ARRAY as $item) {
            EmailSetting::updateOrCreate(
                [
                    'type' => $item['type'],
                ],
                [
                    'name' => $item['name'],
                ]
            );
        }
    }
}
