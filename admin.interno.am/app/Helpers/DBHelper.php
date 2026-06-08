<?php

if (!function_exists('merge_dates_for_insert')) {
    function merge_dates_for_insert(array $data, string $now): array
    {
        $dates = ['created_at' => $now, 'updated_at' => $now];

        return array_merge($data, $dates);
    }
}

if (!function_exists('prepare_pagination_array')) {
    function prepare_pagination_array(int $page, int $perPage): array
    {
        return [
            'offset' => ($page - 1) * $perPage,
            'limit' => $perPage
        ];
    }
}
