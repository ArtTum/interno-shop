<?php

namespace App\Repositories\PaymentMethodTranslation;

use App\Repositories\BaseRepository;
use App\Models\PaymentMethodTranslation;

class PaymentMethodTranslationRepository extends BaseRepository
{
    public function __construct(PaymentMethodTranslation $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }
}
