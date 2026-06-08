<?php

namespace App\Repositories\User;

use App\Constants\MarketplaceConstants;
use App\Constants\OrderConstants;
use App\Constants\UserConstants;
use App\Models\CustomerSegmentUser;
use App\Models\User;
use App\Models\UserGroup;
use App\Repositories\User\Interfaces\UserRepositoryInterface;
use App\Repositories\UserGroup\UserGroupRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    protected Model $model;

    public function __construct(User $model)
    {
        $this->model = $model;
        $this->userGroupRepository = new UserGroupRepository(new UserGroup());
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function updateOrCreate(array $checkingData, array $data)
    {
        return $this->model->updateOrCreate($checkingData, $data);
    }

    public function loginAttempt(array $data): bool
    {
        return Auth::attempt($data);
    }

    public function fetch(?int $userId = null): Model
    {
        if (!$userId) $userId = Auth::user()->id;

        $result = $this->model->select('id', 'user_group_id', 'name', 'last_name', 'superadmin')
            ->where('id', $userId)
            ->with([
                'user_group' => function ($query) {
                    $query->select('id', 'name')
                        ->with([
                            'permissions' => function ($query) {
                                $query->select('user_group_id', 'name', 'can_add', 'can_view', 'can_edit', 'can_delete', 'can_upload', 'can_export');
                            }
                        ]);
                }
            ])
            ->first();

        $result->user_group->permissions_by_name = $result->user_group->permissions->groupBy('name');

        return $result;
    }

    public function fetchForFront(): Model
    {
        return $this->model->select('name', 'last_name', 'email', 'newsletter_subscribed')
            ->where('id', Auth::user()->id)
            ->first();
    }

    public function fetch2(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins)
    {
        return $this->model->select(DB::raw($select))
            ->when(!empty($searchFields) && $params['search'], function ($searchQuery) use ($searchFields, $params) {
                $searchTerm = $params['search'];

                $searchQuery->where(function ($query) use ($searchTerm, $searchFields) {

                    foreach ($searchFields as $fieldOrArray) {
                        if (gettype($fieldOrArray) != 'array') {
                            $searchTerm = addslashes($searchTerm);
                            $query->orWhere($fieldOrArray, 'LIKE', "%$searchTerm%");
                        } else {
                            $query->orWhereHas($fieldOrArray['relation_name'], function ($whereHasQuery) use ($fieldOrArray, $searchTerm) {
                                if (count($fieldOrArray['fields']) > 1) {
                                    $whereHasQuery->where(function ($query) use ($fieldOrArray, $searchTerm) {
                                        foreach ($fieldOrArray['fields'] as $field) {
                                            $searchTerm = addslashes($searchTerm);
                                            $query->orWhereRaw("$field LIKE '%$searchTerm%'");
                                        }
                                    });
                                } else {
                                    $searchTerm = addslashes($searchTerm);
                                    $whereHasQuery->where($fieldOrArray['fields'][0], 'LIKE', "%$searchTerm%");
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

    public function fetchUser(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->get();
    }

    public function fetchForExportByFilters(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): array
    {
        $fullData = [];
        self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with([
                'user_billing_address' => function ($query) {
                    $query->select('user_id', 'country_id', 'name', 'last_name', 'company', 'address', 'address_2', 'city', 'zip', 'state', 'phone', 'email', 'vat_number')
                        ->with(['country' => function ($query) {
                            $query->select('id', 'code');
                        }]);
                },
                'user_shipping_address' => function ($query) {
                    $query->select('user_id', 'country_id', 'name', 'last_name', 'company', 'address', 'address_2', 'city', 'zip', 'state')
                        ->with(['country' => function ($query) {
                            $query->select('id', 'code');
                        }]);
                },
                'user_group' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->chunkById(100, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'users.id', 'id');

        return $fullData;
    }

    public function fetchProvider(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->withCount('orders')
            ->get();
    }

    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields, array $with)
    {
        $query = $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query
            ->first();
    }

    public function fetchByFieldSimple(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->first();
    }

    public function checkUserExists(string $email)
    {
        return $this->model
            ->select('id', 'password', 'subscriber_id')
            ->where('email', $email)
            ->first();
    }

    public function fetchInfoByField(string $whereField, string $whereValue, string $selectedFields)
    {
        return $this->model->select(DB::raw($selectedFields))
//            ->where('blocked', false)
            ->where($whereField, $whereValue)
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return $this->model->where($whereField, $whereValue)->update($data);
    }

    public function insertOrUpdate(string $whereField, string|int $whereValue, array $data): bool
    {
        return $this->model->where($whereField, $whereValue)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return self::fetch2($select, $pagination, $ordering, $params, $searchFields, $joins)
//            ->when($params['blocked'] >= 0, function ($query) use ($params) {
////                $query->where('blocked', $params['blocked']);
//            })
//            ->when($params['type'] === 'employees', function ($query) use ($params) {
//                $query->whereNotIn('user_group_id', [$params['user_group_id_customer'], $params['user_group_id_affiliate']]);
//            })->when($params['type'] === 'customers', function ($query) use ($params) {
//                $query->where('user_group_id', $params['user_group_id_customer']);
//            })
//            ->when($params['type'] === 'members', function ($query) use ($params) {
//                $query->where('user_group_id', $params['user_group_id_affiliate']);
//            })
            ->when(!empty($params['user_group']) && $params['user_group'] > 0, function ($query) use ($params) {
                $query->where('user_group_id', $params['user_group']);
            })
            ->when(isset($params['source']), function ($query) use ($params) {
                $query->where('source', $params['source']);
            })
//            ->when(!empty($params['customer_group']) && $params['customer_group'] > 0, function ($query) use ($params) {
//                $query->where('customer_group_id', $params['customer_group']);
//            })
            ->when(!empty($params['segment_id']) && $params['segment_id'] > -1, function ($q) use ($params) {
                $q->whereHas('customer_segment_users', function ($query) use ($params) {
                    $query->where('customer_segment_id', $params['segment_id']);
                });
            })
//            ->when(($params['type'] === 'marketplaces'), function ($query) use ($params) {
//                $query->when($params['source'] === '-1' , function ($qu) use ($params) {
//                    $qu->whereIn('source', [MarketplaceConstants::AMAZON, MarketplaceConstants::EBAY]);
//                })->when($params['source'] !== '-1' , function ($qu) use ($params) {
//                    $qu->where('source', $params['source']);
//                });
//            })
            ->when((!empty($params['language_id']) && $params['language_id'] > -1) || (!empty($params['member_group_id']) && $params['member_group_id'] > -1), function ($q) use ($params) {
                $q->whereHas('user_affiliate', function ($query) use ($params) {
                    $query->when(!empty($params['language_id']) && $params['language_id'] > -1, function ($q) use ($params) {
                        $q->where('language_id', $params['language_id']);
                    })
                    ->when(!empty($params['member_group_id']) && $params['member_group_id'] > -1, function ($q) use ($params) {
                        $q->where('member_group_id', $params['member_group_id']);
                    });
                });
            });
//            ->when(($params['type'] !== 'marketplaces') && $params['only_actives'] == 1, function ($query) use ($params) {
//                $query->where(function ($query) {
//                    $query->whereNotNull('password')
//                        ->orWhereNotNull('wc_password');
//                });
//            })
//            ->when(($params['type'] !== 'marketplaces') && $params['only_actives'] == 0, function ($query) use ($params) {
//                $query->whereNull('password')
//                    ->whereNull('wc_password');
//            });
    }

    public function autocomplete(string $field, ?string $searchTerm, array $alreadySelectIds): array
    {
        $limit = count($alreadySelectIds) + 10;
        $searchTerm = addslashes($searchTerm);

        return $this->model->select(DB::raw("id as value, CONCAT(name, ' ', last_name) AS label"))
            ->when($searchTerm, function ($query) use ($searchTerm, $field) {
                $query->orWhere(function ($query) use ($field, $searchTerm) {
                    $query->when($field === 'label', function ($query) use ($searchTerm) {
                        $query->whereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%$searchTerm%"]);
                    })->when($field !== 'label', function ($query) use ($searchTerm, $field) {
                        $query->whereRaw("$field LIKE '%$searchTerm%'");
                    });
                });
            })
            ->orWhereIn('id', $alreadySelectIds)
            ->limit($limit)
            ->offset(0)
            ->get()
            ->toArray();
    }

    public function autocompleteForOffer(string $field, ?string $searchTerm, array $alreadySelectIds, array $data): array
    {
        $limit = count($alreadySelectIds) + 10;
        $searchTerm = addslashes($searchTerm);

        return $this->model->select(DB::raw("id as value, CONCAT(name, ' ', last_name, ' - (', email, ') ') AS label"))
            ->when($searchTerm, function ($query) use ($searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%$searchTerm%"])
                        ->orWhereRaw("email LIKE ?", ["%$searchTerm%"]);
                });
            })
            ->when(!empty($data['user_group_id']), function ($q) use ($data) {
                $q->where('user_group_id', $data['user_group_id']);
            })
            ->orWhereIn('id', $alreadySelectIds)
            ->limit($limit)
            ->offset(0)
            ->get()
            ->toArray();
    }


    public function fetchForExport(array $data): Collection
    {
        return $this->model->with([
            'user_billing_address' => function ($query) {
                $query->select('user_id', 'country_id', 'name', 'last_name', 'company', 'address', 'address_2', 'city', 'zip', 'state', 'phone', 'email', 'vat_number')
                    ->with(['country' => function ($query) {
                        $query->select('id', 'code');
                    }]);
            },
            'user_shipping_address' => function ($query) {
                $query->select('user_id', 'country_id', 'name', 'last_name', 'company', 'address', 'address_2', 'city', 'zip', 'state')
                    ->with(['country' => function ($query) {
                        $query->select('id', 'code');
                    }]);
            },
            'user_group' => function ($query) {
                $query->select('id', 'name');
            }
        ])
            ->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
                $query->whereIn('id', $data['ids'])->orderBy($data['ordering_field'], $data['ordering_direction']);;
            })->get();
    }

    public function fetchForUpload(string $email)
    {
        return $this->model->select('id')
            ->where('email', $email)
            ->with([
                'user_billing_address' => function ($query) {
                    $query->select('id', 'user_id');
                },
                'user_shipping_address' => function ($query) {
                    $query->select('id', 'user_id');
                }
            ])->first();
    }

    public function getIdByEmail(string $email)
    {
        return $this->model->select('id')->where('email', $email)->value('id');
    }

    public function getForCheckout(int $userId)
    {
        return $this->model->select(
            'user_billing_addresses.id as bill_id', 'user_billing_addresses.country_id as bill_country_id', 'user_billing_addresses.name as bill_name',
            'user_billing_addresses.last_name as bill_last_name', 'user_billing_addresses.company as bill_company',
            'user_billing_addresses.address as bill_address', 'user_billing_addresses.address_2 as bill_address_2',
            'user_billing_addresses.city as bill_city', 'user_billing_addresses.zip as bill_zip', 'user_billing_addresses.state as bill_state',
            'user_billing_addresses.phone as bill_phone', 'user_billing_addresses.email as bill_email',
            'user_billing_addresses.vat_number as bill_vat_number',
            'user_shipping_addresses.id as ship_id', 'user_shipping_addresses.country_id as ship_country_id', 'user_shipping_addresses.name as ship_name',
            'user_shipping_addresses.last_name as ship_last_name', 'user_shipping_addresses.company as ship_company',
            'user_shipping_addresses.address as ship_address', 'user_shipping_addresses.address_2 as ship_address_2',
            'user_shipping_addresses.city as ship_city', 'user_shipping_addresses.zip as ship_zip', 'user_shipping_addresses.state as ship_state'
        )
            ->where('users.id', $userId)
            ->leftJoin('user_billing_addresses', function ($join) {
                $join->on('user_billing_addresses.user_id', '=', 'users.id');
            })
            ->leftJoin('user_shipping_addresses', function ($join) {
                $join->on('user_shipping_addresses.user_id', '=', 'users.id');
            })
            ->first();
    }

    public function getInfoForAccount(int $userId, float $rate = 1)
    {
        return $this->model->select('users.name', 'users.email', 'users.last_name', 'users.newsletter_subscribed',
            'users.subscriber_id', 'countries.code', 'phone', 'city', 'state', DB::raw("balance * {$rate} as balance"),
            'address', 'address_2', 'zip', 'countries.name as country_name'
        )
            ->leftJoin('user_billing_addresses', function ($join) {
                $join->on('user_billing_addresses.user_id', '=', 'users.id')
                    ->join('countries', 'user_billing_addresses.country_id', '=', 'countries.id');
            })
            ->where('users.id', $userId)
            ->first();
    }

    public function getInfoForAddressesTab(int $userId)
    {
        return $this->model->select(
            'user_billing_addresses.country_id as bill_country_id', 'user_billing_addresses.name as bill_name',
            'user_billing_addresses.last_name as bill_last_name',
            'user_billing_addresses.company as bill_company', 'user_billing_addresses.address as bill_address',
            'user_billing_addresses.address_2 as bill_address_2', 'bill_countries.name as bill_country_name',
            'user_billing_addresses.city as bill_city', 'user_billing_addresses.zip as bill_zip',
            'user_billing_addresses.state as bill_state',
            'user_billing_addresses.phone as bill_phone', 'user_billing_addresses.email as bill_email',
            'user_billing_addresses.vat_number as bill_vat_number',
            'user_shipping_addresses.country_id as ship_country_id', 'user_shipping_addresses.name as ship_name',
            'user_shipping_addresses.last_name as ship_last_name',
            'user_shipping_addresses.company as ship_company', 'user_shipping_addresses.address as ship_address',
            'user_shipping_addresses.address_2 as ship_address_2', 'ship_countries.name as ship_country_name',
            'user_shipping_addresses.city as ship_city', 'user_shipping_addresses.zip as ship_zip', 'user_shipping_addresses.state as ship_state'
        )
            ->where('users.id', $userId)
            ->leftJoin('user_billing_addresses', function ($join) {
                $join->on('user_billing_addresses.user_id', '=', 'users.id')
                    ->join('countries as bill_countries', 'user_billing_addresses.country_id', '=', 'bill_countries.id');
            })
            ->leftJoin('user_shipping_addresses', function ($join) {
                $join->on('user_shipping_addresses.user_id', '=', 'users.id')
                    ->join('countries as ship_countries', 'user_shipping_addresses.country_id', '=', 'ship_countries.id');
            })
            ->first();
    }

    public function getIdByGtin(string|int $gtin)
    {
        return $this->model
            ->select('id', 'name', 'last_name')
            ->where('gtin', $gtin)
            ->first();
    }

    private function fetchQueryForSharedCarts(array $params): Builder
    {
        return $this->model
            ->whereHas('shared_carts', function ($query) use ($params) {
                $query->whereHas('order_infos', function ($query) use ($params) {
                    $query->whereHas('order', function ($query) use ($params) {
                        $query->where('status', OrderConstants::STATUS_COMPLETED);
                    });
                });
            })
            ->when($params['created_at_from'], function ($query) use ($params) {
                $query->whereHas('shared_carts', function ($query) use ($params) {
                    $query->whereHas('order_infos', function ($query) use ($params) {
                        $query->whereHas('order', function ($query) use ($params) {
                            $query->where('status_change_date', '>=', $params['created_at_from']);
                        });
                    });
                });
            })
            ->when($params['created_at_to'], function ($query) use ($params) {
                $query->whereHas('shared_carts', function ($query) use ($params) {
                    $query->whereHas('order_infos', function ($query) use ($params) {
                        $query->whereHas('order', function ($query) use ($params) {
                            $query->where('status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        });
                    });
                });
            })
            ->when($params['user_group_id'] > 0, function ($query) use ($params) {
                $query->where('user_group_id', $params['user_group_id']);
            })
            ->when($params['user_group_id'] === 0, function ($query) use ($params) {
                $query->whereNull('user_group_id');
            });
    }

    private function fetchQueryForOffers(array $params): Builder
    {
        return $this->model
            ->whereHas('offers', function ($query) use ($params) {
                $query->whereHas('order', function ($query) use ($params) {
                    $query->where('status', OrderConstants::STATUS_COMPLETED);
                });
            })
            ->when($params['created_at_from'], function ($query) use ($params) {
                $query->whereHas('offers', function ($query) use ($params) {
                    $query->whereHas('order', function ($query) use ($params) {
                        $query->where('status_change_date', '>=', $params['created_at_from']);
                    });
                });
            })
            ->when($params['created_at_to'], function ($query) use ($params) {
                $query->whereHas('offers', function ($query) use ($params) {
                    $query->whereHas('order', function ($query) use ($params) {
                        $query->where('status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                    });
                });
            })
            ->when($params['user_group_id'] > 0, function ($query) use ($params) {
                $query->where('user_group_id', $params['user_group_id']);
            })
            ->when($params['user_group_id'] === 0, function ($query) use ($params) {
                $query->whereNull('user_group_id');
            });
    }

    public function fetchForSharedCarts(array $pagination, array $ordering, array $params): Collection
    {
        return self::fetchQueryForSharedCarts($params)
            ->select(DB::raw('id, name, last_name, user_group_id'))
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->with([
                'shared_carts' => function ($query) use ($params) {
                    $query->select('shared_carts.id', 'shared_carts.user_id', 'orders.total_price', 'order_infos.agent_revenue',
                        'orders.id as order_id', 'orders.total_discount_price', 'orders.created_at', 'orders.status_change_date', 'orders.status', 'currencies.symbol as currency_symbol')
                        ->join('order_infos', 'order_infos.shared_cart_id', '=', 'shared_carts.id')
                        ->join('orders', 'orders.id', '=', 'order_infos.order_id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING])
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        });
                },
                'user_group' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->when(!empty($ordering), function ($orderQuery) use ($ordering) {
                $orderQuery->orderBy($ordering['field'], $ordering['direction']);
            })
            ->withSum([
                'shared_carts as total_agent_revenue' => function ($query) use ($params) {
                    $query->join('order_infos', 'order_infos.shared_cart_id', '=', 'shared_carts.id')
                        ->join('orders', 'orders.id', '=', 'order_infos.order_id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING])
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        })
                        ->whereNotNull('order_infos.agent_revenue'); // Ensure null values don't interfere
                }
            ], 'order_infos.agent_revenue')
            ->get();
    }

    public function fetchForOffers(array $pagination, array $ordering, array $params): Collection
    {
        return self::fetchQueryForOffers($params)
            ->select(DB::raw('id, name, last_name, user_group_id'))
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->with([
                'offers' => function ($query) use ($params) {
                    $query->select('offers.id', 'offers.user_id', 'orders.total_price', 'order_infos.agent_revenue',
                        'orders.id as order_id', 'orders.created_at', 'orders.status_change_date', 'orders.status', 'currencies.symbol as currency_symbol')
                        ->join('orders', 'orders.id', '=', 'offers.order_id')
                        ->join('order_infos', 'order_infos.order_id', '=', 'orders.id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING])
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        });
                },
                'user_group' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->when(!empty($ordering), function ($orderQuery) use ($ordering) {
                $orderQuery->orderBy($ordering['field'], $ordering['direction']);
            })
            ->withSum([
                'offers as total_agent_revenue' => function ($query) use ($params) {
                    $query->join('orders', 'orders.id', '=', 'offers.order_id')
                        ->join('order_infos', 'order_infos.order_id', '=', 'orders.id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING])
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        })
                        ->whereNotNull('order_infos.agent_revenue'); // Ensure null values don't interfere
                }
            ], 'order_infos.agent_revenue')
            ->get();
    }

    public function fetchForSharedCartsForUser(string $select, array $params, int $userId)
    {
        return $this->model->select(DB::raw($select))
            ->where('users.id', $userId)
            ->with([
                'shared_carts' => function ($query) use ($params) {
                    $query->select('shared_carts.id', 'shared_carts.user_id', 'orders.total_price', 'order_infos.agent_revenue',
                        'orders.id as order_id', 'orders.total_discount_price', 'orders.created_at', 'orders.status', 'currencies.symbol as currency_symbol')
                        ->join('order_infos', 'order_infos.shared_cart_id', '=', 'shared_carts.id')
                        ->join('orders', 'orders.id', '=', 'order_infos.order_id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        })
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING]);
                },
                'user_group' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->withSum([
                'shared_carts as total_agent_revenue' => function ($query) use ($params) {
                    $query->join('order_infos', 'order_infos.shared_cart_id', '=', 'shared_carts.id')
                        ->join('orders', 'orders.id', '=', 'order_infos.order_id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING])
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        })
                        ->whereNotNull('order_infos.agent_revenue'); // Ensure null values don't interfere
                }
            ], 'order_infos.agent_revenue')
            ->first();
    }

    public function fetchForOffersForUser(string $select, array $params, int $userId)
    {
        return $this->model->select(DB::raw($select))
            ->where('users.id', $userId)
            ->with([
                'offers' => function ($query) use ($params) {
                    $query->select('offers.id', 'offers.user_id', 'orders.total_price', 'order_infos.agent_revenue',
                        'orders.id as order_id', 'orders.created_at', 'orders.status_change_date', 'orders.status', 'currencies.symbol as currency_symbol')
                        ->join('orders', 'orders.id', '=', 'offers.order_id')
                        ->join('order_infos', 'order_infos.order_id', '=', 'orders.id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        })
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING]);
                },
                'user_group' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->withSum([
                'offers as total_agent_revenue' => function ($query) use ($params) {
                    $query->join('orders', 'orders.id', '=', 'offers.order_id')
                        ->join('order_infos', 'order_infos.order_id', '=', 'orders.id')
                        ->join('currencies', 'currencies.code', '=', 'orders.order_currency')
                        ->whereIn('orders.status', [OrderConstants::STATUS_COMPLETED, OrderConstants::STATUS_PROCESSING])
                        ->when($params['created_at_from'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '>=', $params['created_at_from']);
                        })
                        ->when($params['created_at_to'], function ($query) use ($params) {
                            $query->where('orders.status_change_date', '<=', $params['created_at_to'] . ' 23:59:59');
                        })
                        ->whereNotNull('order_infos.agent_revenue'); // Ensure null values don't interfere
                }
            ], 'order_infos.agent_revenue')
            ->first();
    }

    public function fetchTotalCountForSharedCarts(array $params): int
    {
        return self::fetchQueryForSharedCarts($params)->count();
    }

    public function fetchTotalCountForOffers(array $params): int
    {
        return self::fetchQueryForOffers($params)->count();
    }

    public function checkEmailExistsInActiveUsers(string $email)
    {
        return $this->model->select('id')
            ->where('email', $email)
            ->where(function ($query) {
                $query->whereNotNull('password')
                    ->orWhereNotNull('wc_password');
            })
            ->first();
    }

    public function checkExistsEmailAndActiveUser(string $email): bool
    {
        return $this->model->select('id')
            ->where('email', $email)
//            ->where('blocked', false)
            ->where(function ($query) {
                $query->whereNotNull('password')
                    ->orWhereNotNull('wc_password');
            })
            ->exists();
    }

    public function checkExistsForVendorsSwitch(string $email): bool
    {
        return $this->model->select('id')
            ->where('email', $email)
//            ->where('blocked', false)
            ->where(function ($query) {
                $query->whereNotNull('password')
                    ->orWhereNotNull('wc_password');
            })
            ->whereDoesntHave('user_group', function ($q) {
                $q->where('name', UserConstants::USER_GROUPS['customer']);
            })
            ->exists();
    }

    public function deleteOldIPs()
    {
        return $this->model->whereNotNull('ip_expires_at')
            ->where('ip_expires_at', '<', now())
            ->update([
                'ip' => null,
                'ip_expires_at' => null,
            ]);
    }

    public function customersOrdersDaysDiffSum(array $data, $categoryIds)
    {
        if (!empty($categoryIds)) {
            $categoryCheckingStrJoin = "
                JOIN order_items ON orders.id = order_items.order_id
                JOIN products ON order_items.product_id = products.id
                JOIN product_categories ON products.id = product_categories.product_id
                AND product_categories.category_id IN (" . implode(',', $categoryIds) . ")";
        } else {
            $categoryCheckingStrJoin = null;
        }
        if (!empty($categoryIds)) {
            $categoryCheckingStr = "
                AND product_categories.category_id IN (" . implode(',', $categoryIds) . ")";
        } else {
            $categoryCheckingStr = null;
        }

        return DB::table(DB::raw("(
                SELECT
                orders.user_id,
                MIN(orders.created_at) AS first_order_date,
                MAX(orders.created_at) AS last_order_date,
                COUNT(orders.id) AS total_orders
                FROM orders {$categoryCheckingStrJoin}
                WHERE orders.status = " . OrderConstants::STATUS_COMPLETED . "
                AND orders.created_at BETWEEN ? AND ?
                AND orders.full_reshipment IS NULL {$categoryCheckingStr}
                GROUP BY orders.user_id
            ) AS user_orders"))
            ->selectRaw("SUM(CASE
                    WHEN total_orders = 1 THEN 0
                    ELSE DATEDIFF(last_order_date, first_order_date) + 1
                END) AS total_active_days")
            ->setBindings([$data['order_date_from'], $data['order_date_to']])
            ->value('total_active_days');
    }

    public function getBySegmentCriterias(array $criteria, int $segmentId): void
    {
        $this->model->select(
            'users.id',
            DB::raw('MIN(orders.created_at) as first_order_date'),
            DB::raw('MAX(orders.created_at) as last_order_date'),
            DB::raw('COALESCE(COUNT(orders.id), 0) as order_count'),
            DB::raw('COALESCE(SUM(orders.total_price / orders.order_currency_rate), 0) as total_revenue'),
            DB::raw('COALESCE(SUM(orders.total_price / orders.order_currency_rate) / NULLIF(COUNT(orders.id), 0), 0) as avg_revenue'),
            DB::raw('DATEDIFF(MAX(orders.created_at), MIN(orders.created_at)) / (COUNT(orders.id) - 1) as purchase_frequency_days'),
            DB::raw('DATEDIFF(CURDATE(), MAX(orders.created_at)) as last_purchase_days')
        )
            ->leftJoin('orders', function ($join) {
                $join->on('orders.user_id', '=', 'users.id')
                    ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
                    ->whereNull('orders.full_reshipment');
            })
            ->groupBy('users.id')
            ->when(!empty($criteria['order_count_range_from']), function ($q) use ($criteria) {
                $q->having('order_count', '>=', $criteria['order_count_range_from']);
            })
            ->when(!empty($criteria['order_count_range_to']), function ($q) use ($criteria) {
                $q->having('order_count', '<=', $criteria['order_count_range_to']);
            })
            ->when(!empty($criteria['avg_revenue_from']), function ($q) use ($criteria) {
                $q->having('avg_revenue', '>=', $criteria['avg_revenue_from']);
            })
            ->when(!empty($criteria['avg_revenue_to']), function ($q) use ($criteria) {
                $q->having('avg_revenue', '<=', $criteria['avg_revenue_to']);
            })
            ->when(!empty($criteria['purchase_frequency_days_from']), function ($q) use ($criteria) {
                $q->having('purchase_frequency_days', '>=', $criteria['purchase_frequency_days_from']);
            })
            ->when(!empty($criteria['purchase_frequency_days_to']), function ($q) use ($criteria) {
                $q->having('purchase_frequency_days', '<=', $criteria['purchase_frequency_days_to']);
            })
            ->when(!empty($criteria['last_purchase_days_from']), function ($q) use ($criteria) {
                $q->having('last_purchase_days', '>=', $criteria['last_purchase_days_from']);
            })
            ->when(!empty($criteria['last_purchase_days_to']), function ($q) use ($criteria) {
                $q->having('last_purchase_days', '<=', $criteria['last_purchase_days_to']);
            })
            ->when(!empty($criteria['language_id']) && $criteria['language_id'] > -1, function ($q) use ($criteria) {
                $q->where('orders.language_id', $criteria['language_id']);
            })
            ->when(!empty($criteria['only_actives']) && $criteria['only_actives'] === 1, function ($q) {
                $q->where(function ($query) {
                    $query->whereNotNull('password')
                        ->orWhereNotNull('wc_password');
                });
            })
            ->when(!empty($criteria['only_actives']) && $criteria['only_actives'] == 0, function ($query) {
                $query->whereNull('password')
                    ->whereNull('wc_password');
            })
            ->when(
                !empty($criteria['country_ids']) || !empty($criteria['cities']) || !empty($criteria['zip'])
                || (!empty($criteria['vat_exists']) && $criteria['vat_exists'] == 1) || (!empty($criteria['vat_exists']) && $criteria['vat_exists'] == 0)
                || (!empty($criteria['company_exists']) && $criteria['company_exists'] == 1) || (!empty($criteria['company_exists']) && $criteria['company_exists'] == 0), function ($query) use ($criteria) {
                $query->whereHas('user_billing_address', function ($q) use ($criteria) {
                    $q->when(!empty($criteria['country_ids']), function ($q) use ($criteria) {
                        $q->whereIn('country_id', $criteria['country_ids']);
                    })
                        ->when(!empty($criteria['cities']), function ($q) use ($criteria) {
                            $criteria['cities'] = explode(',', $criteria['cities']);
                            $q->whereIn('city', $criteria['cities']);
                        })
                        ->when(!empty($criteria['zip']), function ($q) use ($criteria) {
                            if (str_contains($criteria['zip'], '-')) {
                                $criteria['zip'] = explode('-', $criteria['zip']);
                                $q->where('zip', '>=', $criteria['zip'][0])
                                    ->where('zip', '<=', $criteria['zip'][1]);
                            } else if (str_contains($criteria['zip'], ';')) {
                                $criteria['zip'] = explode(';', $criteria['zip']);
                                $q->whereIn('zip', $criteria['zip']);
                            } else {
                                $q->where('zip', $criteria['zip']);
                            }
                        })
                        ->when(!empty($criteria['vat_exists']) && $criteria['vat_exists'] == 1, function ($q) use ($criteria) {
                            $q->whereNotNull('vat_number');
                        })
                        ->when(!empty($criteria['vat_exists']) && $criteria['vat_exists'] == 0, function ($q) use ($criteria) {
                            $q->whereNull('vat_number');
                        })
                        ->when(!empty($criteria['company_exists']) && $criteria['company_exists'] == 1, function ($q) use ($criteria) {
                            $q->whereNotNull('company');
                        })
                        ->when(!empty($criteria['company_exists']) && $criteria['company_exists'] == 0, function ($q) use ($criteria) {
                            $q->whereNull('company');
                        });
                });
            })
            ->when(!empty($criteria['product_ids']) || !empty($criteria['category_ids']), function ($q) {
                $q->leftJoin('order_items', 'order_items.order_id', 'orders.id');
            })
            ->when(!empty($criteria['product_ids']), function ($q) use ($criteria) {
                if ($criteria['products_matching_rule'] === 'one') {
                    $q->whereIn('order_items.product_id', $criteria['product_ids']);
                } else {
                    foreach ($criteria['product_ids'] as $id) {
                        $q->where('order_items.product_id', $id);
                    }
                }
            })
            ->when(!empty($criteria['category_ids']), function ($q) use ($criteria) {
                $q->leftJoin('product_categories', 'order_items.product_id', 'product_categories.product_id');
                if ($criteria['products_matching_rule'] === 'one') {
                    $q->whereIn('product_categories.category_id', $criteria['category_ids']);
                } else {
                    foreach ($criteria['category_ids'] as $id) {
                        $q->where('product_categories.category_id', $id);
                    }
                }
            })
            ->chunkById(1000, function ($users) use ($segmentId) {
                foreach ($users as $user) {
                    $conditions = [
                        'user_id' => $user->id,
                        'customer_segment_id' => $segmentId,
                    ];
                    $params = [
                        'imported' => false
                    ];

                    $row = CustomerSegmentUser::select('id')
                        ->where($conditions)
                        ->first();

                    if ($row) {
                        $row->update($params);
                    } else {
                        CustomerSegmentUser::insert(merge_dates_for_insert(array_merge($conditions, $params), now()));
                    }
                }
            }, 'users.id', 'id');
    }

    public function getCountByUserGroupId(int $userGroupId)
    {
        return $this->model->select('id')
            ->where('user_group_id', $userGroupId)
            ->count();
    }

    public function getEmployeeUserCertificateStatusByEmail(string $email)
    {
        return $this->model
            ->select('check_client_certificate')
            ->where('email', $email)
            ->whereNot('user_group_id',  $this->userGroupRepository->getIdByGroup('customer'))
            ->value('check_client_certificate');
    }

}
