<?php

namespace App\Libraries\Fedex;

use Illuminate\Database\Eloquent\Model;

class ShipmentBuilder
{
    protected array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function build(Model $order, float $weight, string $shipDate): array
    {
        return [
            "labelResponseOptions" => "URL_ONLY",
            "mergeLabelDocOption" => "LABELS_AND_DOCS", // LABELS_ONLY
            "requestedShipment" => [
                "shipper" => [
                    "contact" => [
                        "personName" => "Yannick Schulz",
                        "phoneNumber" => "+420773103102",
                        "companyName" => "Svetlofon GmbH",
                        "deptName" => "SndrDeptNm",
                        "emailAddress" => "info@svetlofon.ru"
                    ],
                    "address" => [
                        "streetLines" => [
                            "Duisburger Str. 36"
                        ],
                        "city" => "Krefeld",
                        "postalCode" => "47829",
                        "countryCode" => "DE",
                        "residential" => false // ?????
                    ]
                    // ,
                    // "tins" => [
                    //     "number" => "XXX567",
                    //     "tinType" => "FEDERAL",
                    //     "usage" => "usage",
                    //     "effectiveDate" => "2024-06-13",
                    //     "expirationDate" => "2024-06-13"
                    // ]
                ],
                "recipients" => [
                    [
                        "contact" => [
                            "personName" => "John Doe",
                            "phoneNumber" => "+420773103175",
                            "companyName" => "Test",
                            "emailAddress" => "info@svetlofon.ru"
                        ],
                        "address" => [
                            "streetLines" => [
                                "Aspley Road",
                                "Aspley H"
                            ],
                            "city" => "New Malden",
                            "postalCode" => "KT3 3NJ",
                            "countryCode" => "GB",
                            "residential" => true
                            // "stateOrProvinceCode" => "test"
                        ]
                        // ,
                        // "tins" => [
                        //     "number" => "XXX567",
                        //     "tinType" => "FEDERAL",
                        //     "usage" => "usage",
                        //     "effectiveDate" => "2024-06-13",
                        //     "expirationDate" => "2024-06-13"
                        // ]
                    ]
                ],
                "shipDatestamp" => "2025-07-02",
                "totalDeclaredValue" => [
                    "amount" => 10,
                    "currency" => "EUR"
                ],
                "serviceType" => "FEDEX_INTERNATIONAL_PRIORITY",
                "packagingType" => "YOUR_PACKAGING",
                "pickupType" => "USE_SCHEDULED_PICKUP",
                "totalWeight" => 10.6,
                "shippingChargesPayment" => [
                    "paymentType" => "SENDER"
                ],
                "emailNotificationDetail" => [
                    "aggregationType" => "PER_SHIPMENT",
                    "emailNotificationRecipients" => [
                        [
                            "name" => "John Doe",
                            "emailAddress" => "info@svetlofon.ru",
                            "notificationFormatType" => "HTML",
                            "notificationType" => "EMAIL",
                            "emailNotificationRecipientType" => "RECIPIENT",
                            "notificationEventType" => [
                                "ON_DELIVERY",
                                "ON_SHIPMENT",
                                "ON_ESTIMATED_DELIVERY",
                                "ON_PICKUP_DRIVER_ARRIVED",
                                "ON_PICKUP_DRIVER_EN_ROUTE"
                            ]
                        ]
                    ]
                ],
                "labelSpecification" => [
                    "labelStockType" => "PAPER_4X6",
                    "imageType" => "PDF"
                ],
                "requestedPackageLineItems" => [
                    [
                        "weight" => [
                            "units" => "KG",
                            "value" => 5.0
                        ],
                        "declaredValue" => [
                            "amount" => 10.0,
                            "currency" => "EUR"
                        ]
                    ]
                ],
                "customsClearanceDetail" => [
                    "commercialInvoice" => [
                        "shipmentPurpose" => "SOLD"
                    ],
                    "dutiesPayment" => [
                        "payor" => [
                            "responsibleParty" => [
                                "accountNumber" => [
                                    "value" => 740561073
                                ]
                            ]
                        ],
                        "paymentType" => "SENDER"
                    ],
                    "commodities" => [
                        [
                            "description" => "Commodity Description",
                            "countryOfManufacture" => "US",
                            "quantity" => 1,
                            "quantityUnits" => "PCS",
                            "unitPrice" => [
                                "amount" => 10.0,
                                "currency" => "EUR"
                            ],
                            "customsValue" => [
                                "amount" => 10.0,
                                "currency" => "EUR"
                            ],
                            "weight" => [
                                "units" => "KG",
                                "value" => 5.0
                            ]
                        ]
                    ]
                ]
            ],
            "accountNumber" => [
                "value" => 740561073
            ]
        ];
    }
}
