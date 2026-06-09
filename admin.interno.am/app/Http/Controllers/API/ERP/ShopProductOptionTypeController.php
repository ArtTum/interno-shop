<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\ShopProductOptionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopProductOptionTypeController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $status = (int) $request->query('status', -1);
        $orderingField = (string) $request->query('ordering_field', 'sort_order');
        $orderingDirection = $request->query('ordering_direction') === 'desc' ? 'desc' : 'asc';
        $orderingColumns = [
            'id' => 'id',
            'name' => 'name',
            'status' => 'status',
            'sort_order' => 'sort_order',
        ];
        $orderingColumn = $orderingColumns[$orderingField] ?? 'sort_order';

        $query = ShopProductOptionType::query()
            ->when($status >= 0, fn ($query) => $query->where('status', (bool) $status))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
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
            'data' => ShopProductOptionType::query()->findOrFail((int) $request->query('id')),
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        $type = ShopProductOptionType::query()->create($this->validatedPayload($request));

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => ['id' => $type->id],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $type = ShopProductOptionType::query()->findOrFail((int) $request->input('id'));
        $type->update($this->validatedPayload($request, $type->id));

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        ShopProductOptionType::query()->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }

    private function validatedPayload(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('shop_product_option_types', 'name')->ignore($id)],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
