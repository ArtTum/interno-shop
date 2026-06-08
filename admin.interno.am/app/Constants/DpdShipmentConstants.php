<?php

namespace App\Constants;

class DpdShipmentConstants
{
    const STATUS_CODES = [
        'start_order',
        'pickup_driver',
        'pickup_depot',
        'delivery_depot',
        'delivery_carload',
        'delivery_nab',
        'delivery_notification',
        'delivery_customer',
        'delivery_shop',
        'error_pickup',
        'error_return',
        'pickup_by_consignee',
        'no_pickup_by_consignee',
    ];

    const STATUS_DESCRIPTIONS = [
        'start_order' => 'Order data were transmitted',
        'pickup_driver' => 'Parcel was picked up by driver',
        'pickup_depot' => 'Parcel arrived at pickup depot',
        'delivery_depot' => 'Parcel arrived at delivery depot',
        'delivery_carload' => 'Parcel is on the road',
        'delivery_nab' => 'An NAB scan was triggered',
        'delivery_notification' => 'Delivery problem / notification was triggered (e.g. due to address clearing)',
        'delivery_customer' => 'Parcel was delivered to customer',
        'delivery_shop' => 'Parcel delivery at Pickup parcelshop',
        'error_pickup' => 'Pickup problem',
        'error_return' => 'System return back to send',
        'pickup_by_consignee' => 'Parcel pickup by consignee in a Pickup parcelshop',
        'no_pickup_by_consignee' => 'Parcel not picked up by consignee in a Pickup parcelshop',
    ];

    const STATUS_MAPPING = [
        'start_order' => 'pre-transit',
        'pickup_driver' => 'pre-transit',
        'pickup_depot' => 'pre-transit',
        'delivery_depot' => 'in-transit',
        'delivery_carload' => 'in-transit',
        'delivery_nab' => 'in-transit',
        'delivery_notification' => 'in-transit',
        'delivery_customer' => 'delivered',
        'delivery_shop' => 'in-transit',
        'error_pickup' => 'in-transit',
        'error_return' => 'in-transit',
        'pickup_by_consignee' => 'in-transit',
        'no_pickup_by_consignee' => 'in-transit',
    ];

    /**
     * Get the mapped status for a given DPD status code
     *
     * @param string $statusCode
     * @return string|null Returns the mapped status or null if not found
     */
    public static function getMappedStatus(string $statusCode): ?string
    {
        return self::STATUS_MAPPING[$statusCode] ?? null;
    }

}
