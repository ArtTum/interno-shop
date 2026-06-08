<?php

namespace App\Services\ERP\Dashboard;

use App\Repositories\Country\CountryRepository;
use App\Repositories\Item\ItemRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\PaymentMethod\PaymentMethodRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardService
{
    public function __construct(
        OrderRepository         $orderRepository,
        CountryRepository       $countryRepository,
        LanguageRepository      $languageRepository,
        OrderItemRepository     $orderItemRepository,
        ItemRepository          $itemRepository,
        PaymentMethodRepository $paymentMethodRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->countryRepository = $countryRepository;
        $this->languageRepository = $languageRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->itemRepository = $itemRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function fetchBoxes(array $data): JsonResponse
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
        $boxes = $this->orderRepository->fetchDashboardBoxes($fromDate, "{$toDate} 23:59:59", $data['language_id']);
        $comparedBoxes = $this->orderRepository->fetchDashboardBoxes($comparedFromDate, "{$comparedToDate} 23:59:59", $data['language_id']);

        return response()->json([
            'main' => [
                'orders_count' => (int)$boxes->orders_count,
                'sold_products' => (int)$boxes->sold_products,
                'total_customers' => (int)$boxes->total_customers,
                'warehouse_items_sold' => (int)$boxes->warehouse_items_sold,
            ],
            'compared' => [
                'orders_count' => (int)$comparedBoxes->orders_count,
                'sold_products' => (int)$comparedBoxes->sold_products,
                'total_customers' => (int)$comparedBoxes->total_customers,
                'warehouse_items_sold' => (int)$comparedBoxes->warehouse_items_sold,
            ],
            'cumpareingNumbers' => [
                'orders_count' => $this->getPercentageChange($boxes['orders_count'], $comparedBoxes['orders_count']),
                'sold_products' => $this->getPercentageChange($boxes['sold_products'], $comparedBoxes['sold_products']),
                'total_customers' => $this->getPercentageChange($boxes['total_customers'], $comparedBoxes['total_customers']),
                'warehouse_items_sold' => $this->getPercentageChange($boxes['warehouse_items_sold'], $comparedBoxes['warehouse_items_sold']),
            ],
        ]);
    }

    public function fetchOrdersChart(array $data): JsonResponse
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
        $ordersChart = $this->orderRepository->fetchDashboardChartOrders($fromDate, "{$toDate} 23:59:59", $data['language_id']);
        $comparedOrdersChart = $this->orderRepository->fetchDashboardChartOrders($comparedFromDate, "{$comparedToDate} 23:59:59", $data['language_id']);

        $chartDataSeries = [
            [
                'data' => [],
                'name' => "This {$data['range']}"
            ],
            [
                'data' => [],
                'name' => "Last {$data['range']}"
            ],
        ];
        $chartDataLabels = [];

        $datesArray = $this->loopDates($fromDate, $toDate);
        $comparedDatesArray = $this->loopDates($comparedFromDate, $comparedToDate);

        foreach ($datesArray as $index => $actualDate) {
            $actualDateSpliced = substr($actualDate, 5);
            $comparedDateSpliced = substr($comparedDatesArray[$index], 5);
            $chartDataLabels[] = "$actualDateSpliced / $comparedDateSpliced";

            if (isset($ordersChart[$actualDate])) {
                $chartDataSeries[0]['data'][] = round($ordersChart[$actualDate]['orders_count']);
            } else {
                $chartDataSeries[0]['data'][] = 0;
            }

            if (isset($comparedOrdersChart[$comparedDatesArray[$index]])) {
                $chartDataSeries[1]['data'][] = round($comparedOrdersChart[$comparedDatesArray[$index]]['orders_count']);
            } else {
                $chartDataSeries[1]['data'][] = 0;
            }
        }

        return response()->json([
            'chartDataLabels' => $chartDataLabels,
            'chartDataSeries' => $chartDataSeries
        ]);
    }

    public function fetchCustomersChart(array $data): JsonResponse
    {
        $toDate = Carbon::now()->toDateString();

        if ($data['range'] === 'week') {
            $fromDate = Carbon::now()->subDays(6)->toDateString();
        } else if ($data['range'] === 'month') {
            $fromDate = Carbon::now()->subDays(30)->toDateString();
        }

        $ordersChart = $this->orderRepository->fetchDashboardChartCustomers($fromDate, "{$toDate} 23:59:59", $data['language_id']);

        $chartDataSeries = [
            [
                'data' => [],
                'name' => "Old customers"
            ],
            [
                'data' => [],
                'name' => "New customers"
            ],
        ];
        $chartDataLabels = [];

        $datesArray = $this->loopDates($fromDate, $toDate);

        foreach ($datesArray as $index => $actualDate) {
            $actualDateSpliced = substr($actualDate, 5);
            $chartDataLabels[] = "$actualDateSpliced";

            if (isset($ordersChart[$actualDate])) {
                $chartDataSeries[0]['data'][] = round($ordersChart[$actualDate]['old_customers_count']);
                $chartDataSeries[1]['data'][] = round($ordersChart[$actualDate]['new_customers_count']);
            } else {
                $chartDataSeries[0]['data'][] = 0;
                $chartDataSeries[1]['data'][] = 0;
            }
        }

        return response()->json([
            'chartDataLabels' => $chartDataLabels,
            'chartDataSeries' => $chartDataSeries
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
                $percent = round($billingCountriesChart[$country->label]['orders_count'] * 100 / $total->orders_count);
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

        usort($table, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

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
                $percent = round($shippingCountriesChart[$country->label]['orders_count'] * 100 / $total->orders_count);
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

        usort($table, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        return response()->json([
            'table' => $table,
            'chartInfo' => $chartInfo
        ]);
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
                $percent = round($infoByMethods[$methodName]['orders_count'] * 100 / $total->orders_count);
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
                $percent = round($languagesCountriesChart[$country->label]['orders_count'] * 100 / $total->orders_count);
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
        $productsList = $this->orderItemRepository->fetchDashboardTopProductsList($fromDate, "{$toDate} 23:59:59", $baseLanguageId, 'total_sold', $data['language_id']);

        foreach ($productsList as $product) {
            $productTable[] = [
                'image_path' => !empty($product->product['product_variant_main']['media']['path']) ? $product->product['product_variant_main']['media']['path'] : null,
                'sku' => $product->product['product_variant_main']['sku'],
                'name' => $product->product['name'],
                'total_sold' => $product['total_sold'],
            ];
        }

        return response()->json([
            'table' => $productTable
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
                'total_sold' => !empty($infoByMethods[$method['name']]['orders_count']) ? $infoByMethods[$method['name']]['orders_count'] : 0,
            ];
        }

        usort($methodsTable, function ($a, $b) {
            return $b['total_sold'] <=> $a['total_sold'];
        });

        return response()->json([
            'table' => $methodsTable
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
        $itemsList = $this->itemRepository->fetchDashboardTopItemsList($fromDate, "{$toDate} 23:59:59", 'total_sold', $data['language_id']);

        foreach ($itemsList as $item) {
            $itemsTable[] = [
                'name' => $item->item_name,
                'sku' => $item->item_sku,
                'total_sold' => $item->total_sold,
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

    private function getPercentageChange($newValue, $oldValue): float|int
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : ($newValue < 0 ? -100 : 0);
        }

        return round(((($newValue - $oldValue) / abs($oldValue)) * 100), 2);
    }
}
