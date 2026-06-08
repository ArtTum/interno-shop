<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ShopFrontendController extends Controller
{
    private string $configPath;
    private string $ordersPath;

    public function __construct()
    {
        $this->configPath = storage_path('app/shop_frontend.json');
        $this->ordersPath = storage_path('app/shop_orders.json');
    }

    public function publicConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->readConfig(),
        ]);
    }

    public function fetch(): JsonResponse
    {
        return $this->publicConfig();
    }

    public function update(Request $request): JsonResponse
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'languages' => ['required', 'array', 'min:1'],
            'menuGroups' => ['required', 'array'],
            'translations' => ['required', 'array'],
            'products' => ['required', 'array'],
            'settings' => ['required', 'array'],
            'privacy' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->syncLanguagesToDatabase($payload['languages']);
        $this->writeJson($this->configPath, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Shop frontend settings saved.',
            'data' => $payload,
        ]);
    }

    public function storeOrder(Request $request): JsonResponse
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'customer.firstName' => ['required', 'string', 'max:120'],
            'customer.lastName' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:80'],
            'customer.email' => ['required', 'email', 'max:160'],
            'customer.address' => ['required', 'string', 'max:500'],
            'customer.masterCode' => ['nullable', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'total' => ['required', 'numeric', 'min:0'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $orders = $this->readOrders();
        $nextId = empty($orders) ? 1 : max(array_column($orders, 'id')) + 1;

        $order = [
            'id' => $nextId,
            'status' => 'new',
            'created_at' => now()->toDateTimeString(),
            'customer' => $payload['customer'],
            'items' => $payload['items'],
            'total' => $payload['total'],
            'language' => $payload['language'] ?? 'hy',
        ];

        array_unshift($orders, $order);
        $this->writeJson($this->ordersPath, $orders);

        return response()->json([
            'success' => true,
            'message' => 'Order created.',
            'data' => $order,
        ], 201);
    }

    public function orders(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->readOrders(),
        ]);
    }

    private function readConfig(): array
    {
        $default = config('shop_frontend', []);
        $stored = $this->readJson($this->configPath, []);

        $config = array_replace_recursive($default, $stored);
        $databaseLanguages = $this->readLanguagesFromDatabase($config['languages'] ?? []);

        if (!empty($databaseLanguages)) {
            $config['languages'] = $databaseLanguages;
        }

        return $config;
    }

    private function readOrders(): array
    {
        return $this->readJson($this->ordersPath, []);
    }

    private function readJson(string $path, array $fallback): array
    {
        if (!File::exists($path)) {
            return $fallback;
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    private function writeJson(string $path, array $payload): void
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function readLanguagesFromDatabase(array $configuredLanguages): array
    {
        if (!Schema::hasTable('languages')) {
            return [];
        }

        $configuredByCode = collect($configuredLanguages)->keyBy('code');

        return Language::query()
            ->where('status', true)
            ->orderByDesc('base')
            ->orderBy('code')
            ->get(['code', 'name'])
            ->map(function (Language $language) use ($configuredByCode) {
                $configured = $configuredByCode->get($language->code, []);

                return [
                    'code' => $language->code,
                    'label' => $language->name,
                    'icon' => $configured['icon'] ?? "/assets/icons/flag-{$language->code}.svg",
                ];
            })
            ->values()
            ->all();
    }

    private function syncLanguagesToDatabase(array $languages): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        $codes = collect($languages)
            ->pluck('code')
            ->filter()
            ->map(fn ($code) => strtolower(trim($code)))
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return;
        }

        Language::query()
            ->whereNotIn('code', $codes->all())
            ->update(['status' => false]);

        foreach ($languages as $index => $language) {
            $code = strtolower(trim($language['code'] ?? ''));

            if ($code === '') {
                continue;
            }

            Language::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $language['label'] ?? strtoupper($code),
                    'status' => true,
                    'base' => $index === 0,
                    'hreflang' => $code,
                    'local_for_trustpilot' => $code,
                    'default_hreflang' => $index === 0,
                    'is_rtl' => false,
                ]
            );
        }
    }
}
