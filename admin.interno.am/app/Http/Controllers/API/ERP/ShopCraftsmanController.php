<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\ShopCraftsman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopCraftsmanController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $status = (int) $request->query('status', -1);
        $region = trim((string) $request->query('work_region', ''));
        $city = trim((string) $request->query('work_city', ''));
        $field = trim((string) $request->query('work_field', ''));
        $orderingField = (string) $request->query('ordering_field', 'sort_order');
        $orderingDirection = $request->query('ordering_direction') === 'desc' ? 'desc' : 'asc';
        $orderingColumns = [
            'id' => 'id',
            'code' => 'code',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'work_region' => 'work_region',
            'work_city' => 'work_city',
            'work_field' => 'work_field',
            'status' => 'status',
            'sort_order' => 'sort_order',
        ];
        $orderingColumn = $orderingColumns[$orderingField] ?? 'sort_order';

        $query = ShopCraftsman::query()
            ->with('media:id,original_path,path,type,file_type')
            ->when($status >= 0, fn ($query) => $query->where('status', (bool) $status))
            ->when($region !== '', fn ($query) => $query->where('work_region', 'like', "%{$region}%"))
            ->when($city !== '', fn ($query) => $query->where('work_city', 'like', "%{$city}%"))
            ->when($field !== '', fn ($query) => $query->where('work_field', 'like', "%{$field}%"))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('work_region', 'like', "%{$search}%")
                        ->orWhere('work_city', 'like', "%{$search}%")
                        ->orWhere('work_field', 'like', "%{$search}%");
                });
            })
            ->orderBy($orderingColumn, $orderingDirection)
            ->orderBy('id');

        $perPage = max(1, (int) $request->query('per_page', 25));
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();

        return response()->json([
            'success' => true,
            'data' => $query->forPage($page, $perPage)->get()->map(fn (ShopCraftsman $craftsman) => $this->formatCraftsman($craftsman)),
            'pagination' => prepare_pagination($page, $perPage, $total),
        ]);
    }

    public function fetchByField(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatCraftsman(ShopCraftsman::query()->with('media:id,original_path,path,type,file_type')->findOrFail((int) $request->query('id'))),
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        $craftsman = ShopCraftsman::query()->create($this->validatedPayload($request));

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => ['id' => $craftsman->id],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $craftsman = ShopCraftsman::query()->findOrFail((int) $request->input('id'));
        $craftsman->update($this->validatedPayload($request, $craftsman->id));

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        ShopCraftsman::query()->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }

    private function validatedPayload(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:80', Rule::unique('shop_craftsmen', 'code')->ignore($id)],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:80'],
            'work_region' => ['nullable', 'string', 'max:120'],
            'work_city' => ['nullable', 'string', 'max:120'],
            'work_field' => ['nullable', 'string', 'max:160'],
            'has_whatsapp' => ['required', 'boolean'],
            'has_viber' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function formatCraftsman(ShopCraftsman $craftsman): array
    {
        return [
            'id' => $craftsman->id,
            'media_id' => $craftsman->media_id,
            'image' => $craftsman->media?->original_path,
            'media' => $craftsman->media ? [[
                'id' => $craftsman->media->id,
                'media_id' => $craftsman->media->id,
                'path' => $craftsman->media->original_path,
                'type' => $craftsman->media->type,
                'file_type' => $craftsman->media->file_type,
            ]] : [],
            'code' => $craftsman->code,
            'first_name' => $craftsman->first_name,
            'last_name' => $craftsman->last_name,
            'full_name' => $craftsman->full_name,
            'phone' => $craftsman->phone,
            'work_region' => $craftsman->work_region,
            'work_city' => $craftsman->work_city,
            'work_field' => $craftsman->work_field,
            'has_whatsapp' => $craftsman->has_whatsapp,
            'has_viber' => $craftsman->has_viber,
            'status' => $craftsman->status,
            'sort_order' => $craftsman->sort_order,
        ];
    }
}
