<?php

namespace App\Repositories\OrderNote\Interfaces;

interface OrderNoteRepositoryInterface
{
    public function insert(array $data): bool;
}
