<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE services ADD FULLTEXT INDEX ft_phone (phone, other_phone)');
        DB::statement('ALTER TABLE services ADD INDEX idx_patient_full_name (patient_full_name(100))');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE services DROP INDEX ft_phone');
        DB::statement('ALTER TABLE services DROP INDEX idx_patient_full_name');
    }
};