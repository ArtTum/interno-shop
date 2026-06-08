<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConvertServiceDates extends Command
{
    protected $signature = 'app:convert-service-dates';

    protected $description = 'Convert call_date, next_call_date, finish_date, day_surgery from d.m.Y to Y-m-d into new _new columns';

    protected array $columns = ['call_date', 'next_call_date', 'finish_date', 'day_surgery'];

    public function handle(): void
    {
        $total = DB::table('services')->whereNull('deleted_at')->count();
        $this->info("Total records: {$total}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DB::table('services')->whereNull('deleted_at')->orderBy('id')->chunk(500, function ($rows) use ($bar) {
            foreach ($rows as $row) {
                $updates = [];

                foreach ($this->columns as $col) {
                    $value = $row->$col;
                    if (empty($value)) continue;

                    // normalize Unicode dots (U+2024, U+FF0E, etc.) → standard dot
                    $normalized = preg_replace('/[\x{2024}\x{FF0E}\x{FE52}]/u', '.', trim($value));
                    // collapse multiple consecutive dots into one
                    $normalized = preg_replace('/\.{2,}/', '.', $normalized);

                    $parsed = null;
                    $formats = ['d.m.Y', 'd.m.Y H:i:s', 'd.m.Y H:i', 'Y.m.d', 'Y-m-d'];
                    foreach ($formats as $fmt) {
                        try {
                            $parsed = Carbon::createFromFormat($fmt, $normalized)->format('Y-m-d');
                            break;
                        } catch (\Exception) {}
                    }

                    if ($parsed) {
                        $updates[$col . '_new'] = $parsed;
                    } else {
                        $this->warn("\nCould not parse '{$value}' for column {$col}, row id={$row->id}");
                    }
                }

                if (!empty($updates)) {
                    DB::table('services')->where('id', $row->id)->update($updates);
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Done! Now verify the _new columns, then run the drop+rename migration.');
    }
}
