<?php

namespace App\Repositories\PageSectionComponentItem;

use App\Models\PageSectionComponentItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class PageSectionComponentItemRepository extends BaseRepository
{
    public function __construct(PageSectionComponentItem $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function checkExistsStr(string $str)
    {
        return $this->model->select('id', 'config')
            ->where('config', 'LIKE', "%{$str}%")
            ->get()
            ->toArray();
    }

    public function checkAndReplace(int $id, $newStr)
    {
        return $this->model->where('id', $id)
            ->update([
                'config' => $newStr,
            ]);
    }
}
