<?php

namespace App\Repositories\PasswordResetToken;

use App\Repositories\BaseRepository;
use App\Models\PasswordResetToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PasswordResetTokenRepository extends BaseRepository
{
   public function __construct(PasswordResetToken $model)
    {
        $this->model = $model;
    }

    public function saveToken(array $data): void
    {
        $this->model->updateOrInsert(
            ['email' => $data['email']],
            [
                'token' => Hash::make($data['token']),
                'created_at' => now(),
            ]
        );
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }
}
