<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    protected Model $model;

    protected function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins)
    {
        return $this->model
            ->when(isset($params['trash']) && $params['trash'] == 0, function ($query) {
                $query->withTrashed();
            })
            ->when(isset($params['trash']) && $params['trash'] == 1, function ($query) {
                $query->onlyTrashed();
            })
            ->select(DB::raw($select))
            ->when(!empty($searchFields) && ($params['search'] ?? ''), function ($searchQuery) use ($searchFields, $params) {
                $searchTerm = $params['search'];

                $searchQuery->where(function ($query) use ($searchTerm, $searchFields, $params) {

                    foreach ($searchFields as $fieldOrArray) {
                        if (gettype($fieldOrArray) != 'array') {
                            if (gettype($searchTerm) === 'array') {
                                $query->whereIn($fieldOrArray, $searchTerm);
                            } else {
                                $searchTerm = addslashes($searchTerm);
                                $query->orWhere($fieldOrArray, 'LIKE', "%$searchTerm%");
                            }
                        } elseif (isset($fieldOrArray['raw'])) {
                            $escaped = addslashes($searchTerm);
                            $query->orWhereRaw("{$fieldOrArray['raw']} LIKE '%$escaped%'");
                        } elseif (isset($fieldOrArray['whereIn'])) {
                            if (!empty($fieldOrArray['values'])) {
                                $query->orWhereIn($fieldOrArray['field'], $fieldOrArray['values']);
                            }
                        } else {
                            $query->orWhereHas($fieldOrArray['relation_name'], function ($whereHasQuery) use ($fieldOrArray, $searchTerm, $params) {
                                if (count($fieldOrArray['fields']) > 1) {
                                    $whereHasQuery->where(function ($query) use ($fieldOrArray, $searchTerm, $params) {
                                        foreach ($fieldOrArray['fields'] as $field) {
                                            $searchTerm = addslashes($searchTerm);
                                            $query->orWhereRaw("$field LIKE '%$searchTerm%'");
                                        }
                                    });

                                    if (str_contains($fieldOrArray['relation_name'], 'translation')) {
                                        $whereHasQuery->where('language_id', $params['base_language_id']);
                                    }
                                } else {
                                    $searchTerm = addslashes($searchTerm);
                                    $whereHasQuery->where($fieldOrArray['fields'][0], 'LIKE', "%$searchTerm%");


                                    if (str_contains($fieldOrArray['relation_name'], 'translation')) {
                                        $whereHasQuery->where('language_id', $params['base_language_id']);
                                    }
                                }
                            });
                        }
                    }
                });
            })
            ->when(!empty($joins), function ($joinQuery) use ($joins) {
                foreach ($joins as $join) {
                    if (!empty($join[4]) && $join[4] === 'leftJoin') {
                        $joinQuery->leftJoin($join[0], $join[1], $join[2], $join[3]);
                    } else {
                        $joinQuery->join($join[0], $join[1], $join[2], $join[3]);
                    }
                }
            })
            ->when(!empty($ordering), function ($orderQuery) use ($ordering) {
                $orderQuery->orderBy($ordering['field'], $ordering['direction']);
            });
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    protected function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    protected function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    protected function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return $this->model->where($whereField, $whereValue)->update($data);
    }

    protected function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }
}
