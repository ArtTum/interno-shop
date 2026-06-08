<?php

namespace App\Repositories\Appointment;

use App\Repositories\BaseRepository;
use App\Models\Appointment;

class AppointmentRepository extends BaseRepository
{
   public function __construct(Appointment $model)
    {
        $this->model = $model;
    }
}