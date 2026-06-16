<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\ShopProductAttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopProductAttributeValueController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $type = $this->validatedType($request);
        $search = trim((string) $request->query('search', ''));
        $status = (int) $request->query('status', -1);
        $orderingField = (string) $request->query('ordering_field', 'sort_order');
        $orderingDirection = $request->query('ordering_direction') === 'desc' ? 'desc' : 'asc';
        $orderingColumns = [
            'id' => 'id',
            'name' => 'name',
            'value' => 'value',
            'status' => 'status',
            'sort_order' => 'sort_order',
        ];
        $orderingColumn = $orderingColumns[$orderingField] ?? 'sort_order';

        $query = ShopProductAttributeValue::query()
            ->where('type', $type)
            ->when($status >= 0, fn ($query) => $query->where('status', (bool) $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('value', 'like', "%{$search}%")))
            ->orderBy($orderingColumn, $orderingDirection)
            ->orderBy('id');

        $perPage = max(1, (int) $request->query('per_page', 25));
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();

        return response()->json([
            'success' => true,
            'data' => $query->forPage($page, $perPage)->get(),
            'pagination' => prepare_pagination($page, $perPage, $total),
        ]);
    }

    public function fetchByField(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ShopProductAttributeValue::query()
                ->where('type', $this->validatedType($request))
                ->findOrFail((int) $request->query('id')),
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        $value = ShopProductAttributeValue::query()->create($this->validatedPayload($request));

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => ['id' => $value->id],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $value = ShopProductAttributeValue::query()
            ->where('type', $this->validatedType($request))
            ->findOrFail((int) $request->input('id'));
        $value->update($this->validatedPayload($request, $value->id));

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        ShopProductAttributeValue::query()->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }

    private function validatedPayload(Request $request, ?int $id = null): array
    {
        $type = $this->validatedType($request);

        return $request->validate([
            'type' => ['required', 'string', Rule::in(ShopProductAttributeValue::TYPES)],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('shop_product_attribute_values', 'name')
                    ->where(fn ($query) => $query->where('type', $type))
                    ->ignore($id),
            ],
            'value' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function validatedType(Request $request): string
    {
        return $request->validate([
            'type' => ['required', 'string', Rule::in(ShopProductAttributeValue::TYPES)],
        ])['type'];
    }
}
