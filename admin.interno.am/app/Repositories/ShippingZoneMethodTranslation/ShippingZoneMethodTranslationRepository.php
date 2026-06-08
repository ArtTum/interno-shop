<?php

namespace App\Repositories\ShippingZoneMethodTranslation;
use App\Repositories\BaseRepository;
use App\Models\ShippingZoneMethodTranslation;

class ShippingZoneMethodTranslationRepository extends BaseRepository
{
   public function __construct(ShippingZoneMethodTranslation $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function getByMethodIdAndLanguageId(int $shippingZoneId, int $languageId)
    {
        return $this->model->select('name')
            ->where('shipping_zone_method_id', $shippingZoneId)
            ->where('language_id', $languageId)
            ->value('name');
    }
}
