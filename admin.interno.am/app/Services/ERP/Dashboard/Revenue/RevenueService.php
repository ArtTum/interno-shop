<?php

namespace App\Services\ERP\Dashboard\Revenue;

use App\Repositories\Country\CountryRepository;
use App\Repositories\Item\ItemRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\PaymentMethod\PaymentMethodRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class RevenueService
{
    public function __construct(
        OrderRepository         $orderRepository,
        CountryRepository       $countryRepository,
        LanguageRepository      $languageRepository,
        OrderItemRepository     $orderItemRepository,
        ItemRepository          $itemRepository,
        PaymentMethodRepository $paymentMethodRepository,
    )
    {
        $this->orderRepository = $orderRepository;
        $this->countryRepository = $countryRepository;
        $this->languageRepository = $languageRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->itemRepository = $itemRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function fetchRevenueChart(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
            $comparedFromDate = Carbon::now()->subDays(13)->toDateString();
            $comparedToDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
            $comparedFromDate = Carbon::now()->subDays(60)->toDateString();
            $comparedToDate = Carbon::now()->subDays(30)->toDateString();
        }

        $datesArray = $this->loopDates($fromDate, "{$toDate} 23:59:59");
        $comparedDatesArray = $this->loopDates($comparedFromDate, "{$comparedToDate} 23:59:59");
        $chartDataSeries = [
            'total_sales_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'total_price_gross_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'total_price_net_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'total_tax_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'total_shipping_cost_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'total_refund_amount_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'total_discount_price_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
            'order_count_series' => [
                [
                    'data' => [],
                    'name' => 'Order date'
                ],
                [
                    'data' => [],
                    'name' => 'Compared date'
                ],
            ],
        ];
        $chartDataLabels = [];

        $actualDatesInfoForChart = $this->orderRepository->getInfoForRevenueAnalyticsByDaily([
            'order_date_from' => $fromDate,
            'order_date_to' => "{$toDate} 23:59:59",
        ], [], [], '', $data['language_id'])->toArray();

        $comparedDatesInfoForChartCmparing = $this->orderRepository->getInfoForRevenueAnalyticsByDaily([
            'compared_order_date_from' => $comparedFromDate,
            'compared_order_date_to' => "{$comparedToDate} 23:59:59",
        ], [], [], 'compared_', $data['language_id'])->toArray();

        foreach ($datesArray as $index => $actualDate) {
            if (!empty($data['compared_order_date_from'])) {
                $actualDateSpliced = substr($actualDate, 5);
                $comparedDateSpliced = substr($comparedDatesArray[$index], 5);
                $chartDataLabels[] = "$actualDateSpliced / $comparedDateSpliced";
            } else {
                $actualDateSpliced = substr($actualDate, 5);
                $chartDataLabels[] = "$actualDateSpliced";
            }

            if (isset($actualDatesInfoForChart[$actualDate])) {
                $chartDataSeries['total_sales_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_sales']);
                $chartDataSeries['total_price_gross_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_price_gross']);
                $chartDataSeries['total_price_net_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_price_net']);
                $chartDataSeries['total_tax_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_tax']);
                $chartDataSeries['total_shipping_cost_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_shipping_cost']);
                $chartDataSeries['total_refund_amount_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_refund_amount']);
                $chartDataSeries['total_discount_price_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_discount_price']);
                $chartDataSeries['order_count_series'][0]['data'][] = round($actualDatesInfoForChart[$actualDate]['total_orders']);
            } else {
                $chartDataSeries['total_sales_series_series'][0]['data'][] = 0;
                $chartDataSeries['total_price_gross_series'][0]['data'][] = 0;
                $chartDataSeries['total_price_net_series'][0]['data'][] = 0;
                $chartDataSeries['total_tax_series'][0]['data'][] = 0;
                $chartDataSeries['total_shipping_cost_series'][0]['data'][] = 0;
                $chartDataSeries['total_refund_amount_series'][0]['data'][] = 0;
                $chartDataSeries['total_discount_price_series'][0]['data'][] = 0;
                $chartDataSeries['order_count_series'][0]['data'][] = 0;
            }

            if (isset($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]])) {
                $chartDataSeries['total_sales_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_sales']);
                $chartDataSeries['total_price_gross_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_price_gross']);
                $chartDataSeries['total_price_net_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_price_net']);
                $chartDataSeries['total_tax_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_tax']);
                $chartDataSeries['total_shipping_cost_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_shipping_cost']);
                $chartDataSeries['total_refund_amount_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_refund_amount']);
                $chartDataSeries['total_discount_price_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_discount_price']);
                $chartDataSeries['order_count_series'][1]['data'][] = round($comparedDatesInfoForChartCmparing[$comparedDatesArray[$index]]['total_orders']);
            } else {
                $chartDataSeries['total_sales_series_series'][1]['data'][] = 0;
                $chartDataSeries['total_price_gross_series'][1]['data'][] = 0;
                $chartDataSeries['total_price_net_series'][1]['data'][] = 0;
                $chartDataSeries['total_tax_series'][1]['data'][] = 0;
                $chartDataSeries['total_shipping_cost_series'][1]['data'][] = 0;
                $chartDataSeries['total_refund_amount_series'][1]['data'][] = 0;
                $chartDataSeries['total_discount_price_series'][1]['data'][] = 0;
                $chartDataSeries['order_count_series'][1]['data'][] = 0;
            }
        }

        return response()->json([
            'chartDataLabels' => $chartDataLabels,
            'chartDataSeries' => $chartDataSeries,
        ]);
    }

    public function fetchBillingCountriesChart(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $countryOrdering = [
            'field' => 'code',
            'direction' => 'ASC',
        ];

        $countries = $this->countryRepository->fetch("code as label, name", [], $countryOrdering, [], [], []);
        $countryCodes = $countries->pluck('label')->toArray();

        $total = $this->orderRepository->fetchDashboardChartCountriesTotal($fromDate, "{$toDate} 23:59:59", 'order_billing_addresses', $countryCodes, $data['language_id']);
        $billingCountriesChart = $this->orderRepository->fetchDashboardChartCountries($fromDate, "{$toDate} 23:59:59", 'order_billing_addresses', $countryCodes, $data['language_id']);

        $chartInfo = [
            'labels' => [],
            'series' => [],
        ];

        $table = [];
        foreach ($countries as $country) {
            if (isset($billingCountriesChart[$country->label])) {
                $percent = round($billingCountriesChart[$country->label]['total_price_gross'] * 100 / $total->total_price_gross);
                $chartInfo['labels'][] = $country->label;
                $chartInfo['series'][] = $percent;

                $table[] = [
                    'label' => $country->name,
                    'value' => $percent,
                ];
            } else {
                $chartInfo['labels'][] = $country->label;
                $chartInfo['series'][] = 0;

                $table[] = [
                    'label' => $country->name,
                    'value' => 0,
                ];
            }
        }

        return response()->json([
            'table' => $table,
            'chartInfo' => $chartInfo
        ]);
    }

    public function fetchShippingCountriesChart(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $countryOrdering = [
            'field' => 'code',
            'direction' => 'ASC',
        ];

        $countries = $this->countryRepository->fetch("code as label, name", [], $countryOrdering, [], [], []);
        $countryCodes = $countries->pluck('label')->toArray();

        $total = $this->orderRepository->fetchDashboardChartCountriesTotal($fromDate, "{$toDate} 23:59:59", 'order_shipping_addresses', $countryCodes, $data['language_id']);
        $shippingCountriesChart = $this->orderRepository->fetchDashboardChartCountries($fromDate, "{$toDate} 23:59:59", 'order_shipping_addresses', $countryCodes, $data['language_id']);

        $chartInfo = [
            'labels' => [],
            'series' => [],
        ];

        $table = [];
        foreach ($countries as $country) {
            if (isset($shippingCountriesChart[$country->label])) {
                $percent = round($shippingCountriesChart[$country->label]['total_price_gross'] * 100 / $total->total_price_gross);
                $chartInfo['labels'][] = $country->label;
                $chartInfo['series'][] = $percent;

                $table[] = [
                    'label' => $country->name,
                    'value' => $percent,
                ];
            } else {
                $chartInfo['labels'][] = $country->label;
                $chartInfo['series'][] = 0;

                $table[] = [
                    'label' => $country->name,
                    'value' => 0,
                ];
            }
        }

        return response()->json([
            'table' => $table,
            'chartInfo' => $chartInfo
        ]);
    }

    public function fetchLanguagesChart(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $countryOrdering = [
            'field' => 'code',
            'direction' => 'ASC',
        ];

        $languages = $this->languageRepository->fetch("code as label, name", [], $countryOrdering, [], [], []);
        $languageCodes = $languages->pluck('label')->toArray();

        $total = $this->orderRepository->fetchDashboardChartLanguagesTotal($fromDate, "{$toDate} 23:59:59", $languageCodes, $data['language_id']);
        $languagesCountriesChart = $this->orderRepository->fetchDashboardChartLanguages($fromDate, "{$toDate} 23:59:59", $languageCodes, $data['language_id']);

        $chartInfo = [
            'labels' => [],
            'series' => [],
        ];

        $table = [];
        foreach ($languages as $country) {
            if (isset($languagesCountriesChart[$country->label])) {
                $percent = round($languagesCountriesChart[$country->label]['total_price_gross'] * 100 / $total->total_price_gross);
                $chartInfo['labels'][] = $country->label;
                $chartInfo['series'][] = $percent;

                $table[] = [
                    'label' => $country->name,
                    'value' => $percent,
                ];
            } else {
                $chartInfo['labels'][] = $country->label;
                $chartInfo['series'][] = 0;

                $table[] = [
                    'label' => $country->name,
                    'value' => 0,
                ];
            }
        }

        return response()->json([
            'table' => $table,
            'chartInfo' => $chartInfo
        ]);
    }

    public function fetchTopProducts(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();
        $baseLanguageId = $this->languageRepository->getBaseId();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $productTable = [];
        $productsList = $this->orderItemRepository->fetchDashboardTopProductsList($fromDate, "{$toDate} 23:59:59", $baseLanguageId, 'total_sales', $data['language_id']);

        foreach ($productsList as $product) {
            $productTable[] = [
                'image_path' => !empty($product->product['product_variant_main']['media']['path']) ? $product->product['product_variant_main']['media']['path'] : null,
                'sku' => $product->product['product_variant_main']['sku'],
                'name' => $product->product['name'],
                'total_sold' => $product['total_sold'],
                'total_sales' => round($product['total_sales'], 2),
            ];
        }

        return response()->json([
            'table' => $productTable
        ]);
    }

    public function fetchTopItems(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $itemsTable = [];
        $itemsList = $this->itemRepository->fetchDashboardTopItemsList($fromDate, "{$toDate} 23:59:59", 'production_price_total', $data['language_id']);

        foreach ($itemsList as $item) {
            $itemsTable[] = [
                'name' => $item->item_name,
                'sku' => $item->item_sku,
                'total_sold' => $item->total_sold,
                'production_price_total' => $item->production_price_total,
            ];
        }

        return response()->json([
            'table' => $itemsTable
        ]);
    }

    private function loopDates($fromDate, $toDate)
    {
        $startDate = Carbon::parse($fromDate); // Parse the start date
        $endDate = Carbon::parse($toDate); // Parse the end date

        $dates = [];

        while ($startDate <= $endDate) {
            $dates[] = $startDate->toDateString();

            $startDate->addDay();
        }

        return $dates;
    }

    public function fetchPaymentMethodsChart(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();
        $baseLanguageId = $this->languageRepository->getBaseId();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $methodsList = $this->paymentMethodRepository->fetchForDashboard($baseLanguageId);
        $methodNames = $methodsList->pluck('name')->toArray();

        $total = $this->orderRepository->fetchDashboardTopPaymentMethodsTotal($fromDate, "{$toDate} 23:59:59", $methodNames, $data['language_id']);
        $infoByMethods = $this->orderRepository->fetchDashboardTopPaymentMethods($fromDate, "{$toDate} 23:59:59", $methodNames, $data['language_id'])->toArray();

        $chartInfo = [
            'labels' => [],
            'series' => [],
        ];

        $table = [];
        foreach ($methodNames as $methodName) {
            if (isset($infoByMethods[$methodName])) {
                $percent = round($infoByMethods[$methodName]['total_price_gross'] * 100 / $total->total_price_gross);
                $chartInfo['labels'][] = $methodName;
                $chartInfo['series'][] = $percent;

                $table[] = [
                    'label' => $methodName,
                    'value' => $percent,
                ];
            } else {
                $chartInfo['labels'][] = $methodName;
                $chartInfo['series'][] = 0;

                $table[] = [
                    'label' => $methodName,
                    'value' => 0,
                ];
            }
        }

        usort($table, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        return response()->json([
            'table' => $table,
            'chartInfo' => $chartInfo
        ]);
    }

    public function fetchTopPaymentMethods(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();
        $baseLanguageId = $this->languageRepository->getBaseId();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $methodsTable = [];
        $methodsList = $this->paymentMethodRepository->fetchForDashboard($baseLanguageId);
        $methodNames = $methodsList->pluck('name')->toArray();

        $infoByMethods = $this->orderRepository->fetchDashboardTopPaymentMethods($fromDate, "{$toDate} 23:59:59", $methodNames, $data['language_id'])->toArray();

        foreach ($methodsList as $method) {
            $methodsTable[] = [
                'payment_key' => $method['payment_key'],
                'name' => $method['name'],
                'status' => $method['status'],
                'total_sales' => !empty($infoByMethods[$method['name']]['total_price_gross']) ? $infoByMethods[$method['name']]['total_price_gross'] : 0,
            ];
        }

        usort($methodsTable, function ($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });

        return response()->json([
            'table' => $methodsTable
        ]);
    }
}
