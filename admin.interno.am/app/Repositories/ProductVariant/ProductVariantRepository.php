<?php

namespace App\Repositories\ProductVariant;

use App\Models\ProductVariant;
use App\Repositories\BaseRepository;
use App\Repositories\ProductVariant\Interfaces\ProductVariantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductVariantRepository extends BaseRepository implements ProductVariantRepositoryInterface
{
    public function __construct(ProductVariant $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function checkVariationExists(string $key): bool
    {
        return $this->model->select('id')
            ->where('key', $key)
            ->exists();
    }

    public function deleteOldVariationsCheckingByCount(array $combination, int $productId): bool
    {
        $idsToDelete = $this->model->select('id')
            ->where('product_id', $productId)
            ->withCount('product_variant_attributes')
            ->where('is_main', false)
            ->get()
            ->filter(function ($item) use ($combination) {
                return $item->product_variant_attributes_count != count($combination);
            })
            ->pluck('id');

        return $this->model->whereIn('id', $idsToDelete)->delete();
    }

    public function fetchAfterGenerateForProductQuery(int $productId, int $languageId, array $attributeIds, int $selectedLanguageId)
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('is_main', false)
            ->when($selectedLanguageId > 0, function ($query) use ($selectedLanguageId) {
                $query->select('product_variants.id', 'product_id', 'sku',
                    'regular_price', 'regular_price as regular_price_old', 'sales_price', 'tax_status', 'product_variants.media_id', 'stock_status', 'independent_stock_status',
                    'product_variant_translations.id as translation_id',
                    'status', DB::raw('false as open'), DB::raw("IF(name IS NULL, '', name) as name"),
                    DB::raw("IF(description IS NULL, '', description) as description"),
                    DB::raw("IF(short_description IS NULL, '', short_description) as short_description"))
                    ->leftJoin('product_variant_translations', function ($join) use ($selectedLanguageId) {
                        $join->on('product_variant_translations.product_variant_id', '=', 'product_variants.id')
                            ->where('product_variant_translations.language_id', $selectedLanguageId);
                    })
                    ->with([
                        'product_variant_translation' => function ($q) use ($selectedLanguageId) {
                            return $q->select('id', 'product_variant_id', 'media_id')
                                ->where('language_id', $selectedLanguageId)
                                ->with([
                                    'gallery' => function ($galleryQuery) {
                                        $galleryQuery->join('media', 'product_variant_translation_galleries.media_id', '=', 'media.id')
                                            ->select([
                                                'product_variant_translation_galleries.id',
                                                'product_variant_translation_galleries.media_id',
                                                'product_variant_translation_galleries.product_variant_translation_id',
                                                'product_variant_translation_galleries.video_type',
                                                'product_variant_translation_galleries.video_url',
                                                'media.type',
                                                'media.original_path AS path'
                                            ]);
                                    },
                                    'media' => function ($imageQuery) {
                                        $imageQuery->select([
                                            'id', 'type', 'original_path AS path'
                                        ]);
                                    },
                                ]);
                        }
                    ]);
            })
            ->when($selectedLanguageId <= 0, function ($query) use ($selectedLanguageId) {
                $query->select('id', 'product_id', 'sku',
                    'regular_price', 'regular_price as regular_price_old', 'sales_price', 'tax_status', 'media_id', 'stock_status', 'independent_stock_status',
                    'status', DB::raw('false as open'));
            })
            ->when(!empty($attributeIds), function ($q) use ($attributeIds) {
                foreach ($attributeIds as $attributeId) {
                    $q->whereHas('product_variant_attributes', function ($q) use ($attributeId) {
                        $q->where('attribute_id', $attributeId);
                    });
                }
            });
    }

    public function fetchForProduct(int $productId, int $languageId, array $pagination, array $attributeIds, int $selectedLanguageId)
    {
        $response = self::fetchAfterGenerateForProductQuery($productId, $languageId, $attributeIds, $selectedLanguageId)
            ->when(!empty($pagination), function ($q) use ($pagination) {
                $q->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->with([
                'gallery' => function ($galleryQuery) {
                    $galleryQuery->join('media', 'product_variant_galleries.media_id', '=', 'media.id')
                        ->select([
                            'product_variant_galleries.id',
                            'product_variant_galleries.media_id',
                            'product_variant_galleries.product_variant_id',
                            'product_variant_galleries.video_type',
                            'product_variant_galleries.video_url',
                            'media.type',
                            'media.original_path AS path'
                        ]);
                }, 'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
                'product_variant_attributes' => function ($q) use ($languageId) {
                    return $q->select('id', 'attribute_id', 'product_variant_id')
                        ->with([
                            'attribute' => function ($q) use ($languageId) {
                                return $q->select('attributes.id', 'attribute_type_id', 'attribute_translations.value')
                                    ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                                    ->where('attribute_translations.language_id', $languageId)
                                    ->with([
                                        'attribute_type' => function ($q) use ($languageId) {
                                            return $q->select('attribute_types.id', 'attribute_type_translations.name')
                                                ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                                ->where('attribute_type_translations.language_id', $languageId);
                                        }
                                    ]);
                            }
                        ]);
                },
                'product_variant_parents' => function ($q) use ($languageId) {
                    return $q->select('product_variant_id', 'items.sku', 'quantity')
                        ->join('items', 'items.id', '=', 'product_variant_parents.item_id');
                },
                'product_variant_custom_field_translation' => function ($q) use ($languageId) {
                    $q->select('id', 'product_variant_id', 'key', 'value',
                        DB::raw('false as changed'), DB::raw('false as deleted'))
                        ->where('language_id', $languageId);
                }])
            ->orderBy('id', 'desc')
            ->get();

        foreach ($response as $variant) {
            $variant->media_id_translation = !empty($variant->product_variant_translation) ? $variant->product_variant_translation->media_id : '';
            $variant->media_translation = !empty($variant->product_variant_translation->media) ? $variant->product_variant_translation->media : null;
            $variant->gallery_translation = !empty($variant->product_variant_translation->gallery) ? $variant->product_variant_translation->gallery : [];

            if (!empty($variant->product_variant_parents)) {
                $str = '';

                foreach ($variant->product_variant_parents as $parent) {
                    $str .= $parent->quantity . 'x' . $parent->sku . ',';
                }
                $variant->parents = rtrim($str, ',');
            } else {
                $variant->parents = '';
            }
        }

        return $response;
    }

    public function fetchAfterGenerateForProductTotalCount(int $productId, int $languageId, array $attributeIds, int $selectedLanguageId)
    {
        return self::fetchAfterGenerateForProductQuery($productId, $languageId, $attributeIds, $selectedLanguageId)->count();
    }

    public function deleteByProductIdAndAttributeIds(int $productId, array $attributeIds): bool
    {
        return $this->model->where('product_id', $productId)
            ->whereHas('product_variant_attributes', function ($query) use ($attributeIds) {
                $query->whereIn('attribute_id', $attributeIds);
            })
            ->where('is_main', false)
            ->delete();
    }

    public function fetchForExport(array $data): Collection
    {
        $response = $this->model
            ->select('product_variants.*', 'name', 'description', 'short_description')
            ->leftJoin('product_variant_translations', function ($join) use ($data) {
                $join->on('product_variant_translations.product_variant_id', '=', 'product_variants.id')
                    ->where('product_variant_translations.language_id', $data['language_id']);
            })
            ->with([
                'product_variant_translation' => function ($q) use ($data) {
                    return $q->select('id', 'product_variant_id', 'media_id')
                        ->where('language_id', $data['language_id'])
                        ->with([
                            'gallery' => function ($galleryQuery) {
                                $galleryQuery->select([
                                    'product_variant_translation_galleries.product_variant_translation_id',
                                    'product_variant_translation_galleries.video_type',
                                    'product_variant_translation_galleries.video_url',
                                    'media.type',
                                    'media.original_path AS path'
                                ])->join('media', 'product_variant_translation_galleries.media_id', '=', 'media.id');
                            },
                            'media' => function ($imageQuery) use ($data) {
                                $imageQuery->select('id', 'original_path');
                            },
                        ]);
                },
                'product' => function ($query) use ($data) {
                    $query->select('products.id', 'name')
                        ->leftJoin('product_translations', function ($join) use ($data) {
                            $join->on('product_translations.product_id', '=', 'products.id')
                                ->where('product_translations.language_id', $data['language_id']);
                        })
                        ->with([
                            'product_variant_main' => function ($attributesQuery) {
                                $attributesQuery->select('product_id', 'sku');
                            }
                        ]);
                },
                'product_variant_parents' => function ($q) {
                    return $q->select('product_variant_id', 'items.sku', 'quantity')
                        ->join('items', 'items.id', '=', 'product_variant_parents.item_id');
                },
                'gallery' => function ($galleryQuery) {
                    $galleryQuery->select([
                        'product_variant_galleries.product_variant_id',
                        'product_variant_galleries.video_type',
                        'product_variant_galleries.video_url',
                        'media.type',
                        'media.original_path AS path'
                    ])
                        ->join('media', 'product_variant_galleries.media_id', '=', 'media.id');
                },
                'product_variant_custom_field_translation' => function ($query) use ($data) {
                    $query->select('product_variant_id', 'key', 'value')->where('language_id', $data['language_id']);
                },
                'product_variant_attributes' => function ($q) use ($data) {
                    return $q->select('attributes.id as attribute_id', 'attribute_translations.value', 'attribute_translations.description', 'attribute_type_translations.name as attribute_type_name', 'product_variant_attributes.product_variant_id')
                        ->join('attributes', 'product_variant_attributes.attribute_id', '=', 'attributes.id')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->join('attribute_types', 'attribute_types.id', '=', 'attributes.attribute_type_id')
                        ->join('attribute_type_translations', 'attribute_types.id', '=', 'attribute_type_translations.attribute_type_id')
                        ->where('attribute_translations.language_id', $data['language_id'])
                        ->where('attribute_type_translations.language_id', $data['language_id'])
                        ->orderBy('attribute_type_translations.name', 'asc');
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select('id', 'original_path');
                }
            ])->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
                $query->whereIn('product_id', $data['ids']);
            })
            ->where('is_main', false)
            ->orderBy('product_id', 'asc')
            ->get();

        foreach ($response as $variant) {
            if (!empty($variant->product_variant_parents)) {
                $str = '';

                foreach ($variant->product_variant_parents as $parent) {
                    $str .= $parent->quantity . 'x' . $parent->sku . ',';
                }
                $variant->parents = rtrim($str, ',');
            } else {
                $variant->parents = '';
            }
        }

        return $response;
    }

    public function fetchVariantForFrontExtra(array $attributeIds, int $productId, float $rate, int $languageId)
    {
        $query = $this->model->select('product_variants.id', 'product_id', 'media_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"))
            ->where('product_id', $productId)
            ->where('status', true)
            ->where('stock_status', true)
            ->with([
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attributes.id', 'attribute_translations.value')
                        ->join('attribute_translations', 'attributes.id', '=', 'attribute_translations.attribute_id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->orderBy('attributes.priority', 'asc')
                        ->orderBy('attributes.id', 'asc');
                },
                'gallery' => function ($galleryQuery) {
                    $galleryQuery->join('media', 'product_variant_galleries.media_id', '=', 'media.id')
                        ->select([
                            'product_variant_galleries.id',
                            'product_variant_galleries.media_id',
                            'product_variant_galleries.product_variant_id',
                            'product_variant_galleries.video_type',
                            'product_variant_galleries.video_url',
                            'media.type AS type',
                            'media.original_path AS path',
                            'media.path as general_path',
                            'media.width',
                            'media.height',
                        ]);
                },
                'media' => function ($imageQuery) use ($languageId) {
                    $imageQuery->select([
                        'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                    ])
                        ->leftJoin('media_translations', function ($join) use ($languageId) {
                            $join->on('media_translations.media_id', '=', 'media.id')
                                ->where('media_translations.language_id', $languageId);
                        });
                },
            ]);

        foreach ($attributeIds as $attributeId) {
            $query->whereHas('product_variant_attributes', function ($query) use ($attributeId) {
                $query->where('attribute_id', $attributeId);
            });
        }

        return $query->first();
    }

    public function fetchForGetPricesForExtra(array $attributeIds, int $productId, float $rate)
    {
        $query = $this->model->select(
            'attribute_id',
            DB::raw("CASE
                WHEN sales_price IS NOT NULL AND sales_price > 0
                THEN sales_price * {$rate}
                ELSE regular_price * {$rate}
             END as price")
        )
            ->where('product_id', $productId)
            ->where('status', true)
            ->where('stock_status', true)
            ->join('product_variant_attributes', 'product_variant_attributes.product_variant_id', 'product_variants.id')
            ->whereNotIn('attribute_id', $attributeIds);

        foreach ($attributeIds as $attributeId) {
            $query->whereHas('product_variant_attributes', function ($query) use ($attributeId) {
                $query->where('attribute_id', $attributeId);
            });
        }

        return $query->get();
    }

    public function getVariationForFront(string $key, float $rate, int $languageId): ?Model
    {
        return $this->model->select('product_variants.id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"), 'tax_status', 'size_by_unit',
            'product_variants.media_id', 'stock_status', 'sku', 'short_description', 'description')
            ->where('status', true)
            ->where('key', $key)
            ->leftJoin('product_variant_translations', function ($join) use ($languageId) {
                $join->on('product_variant_translations.product_variant_id', '=', 'product_variants.id')
                    ->where('product_variant_translations.language_id', $languageId);
            })
            ->with([
                'gallery' => function ($galleryQuery) use ($languageId) {
                    $galleryQuery->join('media', 'product_variant_galleries.media_id', '=', 'media.id')
                        ->select([
                            'product_variant_galleries.id',
                            'product_variant_galleries.media_id',
                            'product_variant_galleries.product_variant_id',
                            'product_variant_galleries.video_type',
                            'product_variant_galleries.video_url',
                            'media.type AS type',
                            'media.original_path AS path',
                            'media.path as general_path',
                            'media.width',
                            'media.height',
                        ]);
                },
                'media' => function ($imageQuery) use ($languageId) {
                    $imageQuery->select([
                        'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                    ])
                        ->leftJoin('media_translations', function ($join) use ($languageId) {
                            $join->on('media_translations.media_id', '=', 'media.id')
                                ->where('media_translations.language_id', $languageId);
                        });
                },
                'product_variant_translation' => function ($q) use ($languageId) {
                    return $q->select('id', 'product_variant_id', 'media_id')
                        ->where('language_id', $languageId)
                        ->with([
                            'gallery' => function ($galleryQuery) {
                                $galleryQuery->join('media', 'product_variant_translation_galleries.media_id', '=', 'media.id')
                                    ->select([
                                        'product_variant_translation_galleries.id',
                                        'product_variant_translation_galleries.media_id',
                                        'product_variant_translation_galleries.product_variant_translation_id',
                                        'product_variant_translation_galleries.video_type',
                                        'product_variant_translation_galleries.video_url',
                                        'media.type',
                                        'media.original_path AS path',
                                        'media.path as general_path',
                                    ]);
                            },
                            'media' => function ($imageQuery) use ($languageId) {
                                $imageQuery->select([
                                    'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            },
                        ]);
                }
            ])
            ->first();
    }

    public function getVariationForFrontForRelateds(string $key, float $rate, int $languageId): ?Model
    {
        return $this->model->select('id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"), 'media_id', 'stock_status')
            ->where('status', true)
            ->where('key', $key)
            ->with([
                'media' => function ($imageQuery) use ($languageId) {
                    $imageQuery->select([
                        'media.id', 'alt', 'path as general_path', 'width', 'height'
                    ])
                        ->leftJoin('media_translations', function ($join) use ($languageId) {
                            $join->on('media_translations.media_id', '=', 'media.id')
                                ->where('media_translations.language_id', $languageId);
                        });
                },
                'product_variant_translation' => function ($q) use ($languageId) {
                    return $q->select('id', 'product_variant_id', 'media_id')
                        ->where('language_id', $languageId)
                        ->with([
                            'media' => function ($imageQuery) use ($languageId) {
                                $imageQuery->select([
                                    'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            },
                        ]);
                }
            ])
            ->when(Auth::user()?->customer_group_id, function ($query) use ($rate) {
                $query->with([
                    'product_variant_prices' => function ($query) use ($rate) {
                        $query->select([
                            'product_variant_id', 'min', DB::raw("price * {$rate} as price")
                        ])
                            ->where('customer_group_id', Auth::user()->customer_group_id)
                            ->orderBy('min', 'desc');
                    },
                ]);
            })
            ->first();
    }

    public function fetchByIdsOrSKUs(array $ids, int $languageId, float $rate, array $skus)
    {
        return $this->model->select('id', 'sku', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"), 'media_id', 'stock_status')
            ->where('status', true)
            ->when(!empty($ids), function ($q) use ($ids) {
                $q->whereIn('id', $ids);
            })
            ->when(!empty($skus), function ($q) use ($skus, $rate) {
                $q->whereIn('sku', $skus)
                    ->when(Auth::user()?->customer_group_id, function ($query) use ($rate) {
                        $query->with([
                            'product_variant_prices' => function ($query) use ($rate) {
                                $query->select([
                                    'product_variant_id', 'min', DB::raw("price * {$rate} as price")
                                ])
                                    ->where('customer_group_id', Auth::user()->customer_group_id)
                                    ->orderBy('min', 'desc');
                            },
                        ]);
                    });
            })
            ->with([
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'path as general_path'
                    ]);
                },
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'type', 'name', 'sub_name', 'path', 'overwrite_price')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $languageId)
                        ->with([
                            'product_variant_main' => function ($attributesQuery) {
                                $attributesQuery->select('product_id', 'sku', 'media_id', 'stock_status')
                                    ->with([
                                        'media' => function ($imageQuery) {
                                            $imageQuery->select([
                                                'id', 'path as general_path'
                                            ]);
                                        }
                                    ]);
                            },
                        ]);
                },
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attributes.id', 'slug', 'attribute_type_id', 'value')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->with([
                            'attribute_type' => function ($query) use ($languageId) {
                                $query->select('attribute_types.id', 'slug', 'name')
                                    ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                    ->where('attribute_type_translations.language_id', $languageId);
                            }
                        ]);
                }
            ])
            ->get();
    }

    public function fetchByIdsForOffer(array $ids, int $languageId)
    {
        return $this->model->select('id', 'product_id', 'media_id')
            ->where('status', true)
            ->whereIn('id', $ids)
            ->with([
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'path as general_path'
                    ]);
                },
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'type', 'name')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $languageId)
                        ->with([
                            'product_variant_main' => function ($attributesQuery) {
                                $attributesQuery->select('product_id', 'media_id')
                                    ->with([
                                        'media' => function ($imageQuery) {
                                            $imageQuery->select([
                                                'id', 'path as general_path'
                                            ]);
                                        }
                                    ]);
                            },
                        ]);
                },
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attributes.id', 'slug', 'attribute_type_id', 'value')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->with([
                            'attribute_type' => function ($query) use ($languageId) {
                                $query->select('attribute_types.id', 'slug', 'name')
                                    ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                    ->where('attribute_type_translations.language_id', $languageId);
                            }
                        ]);
                }
            ])
            ->get();
    }

    public function fetchByIdsForSendingStatisticTracker(array $ids, int $languageId, float $rate)
    {
        return $this->model->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"))
            ->where('status', true)
            ->where('stock_status', true)
            ->whereIn('id', $ids)
            ->with([
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'product_translations.short_description', 'product_translations.name')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $languageId)
                        ->with([
                            'product_variant_main' => function ($attributesQuery) {
                                $attributesQuery->select('product_id', 'sku');
                            },
                            'categories' => function ($query) use ($languageId) {
                                $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                                    ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                                    ->where('category_translations.language_id', $languageId);
                            }
                        ]);
                },
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attributes.id', 'attribute_type_id', 'value')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->with([
                            'attribute_type' => function ($query) use ($languageId) {
                                $query->select('attribute_types.id', 'name')
                                    ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                    ->where('attribute_type_translations.language_id', $languageId);
                            }
                        ]);
                }
            ])
            ->get();
    }

    public function fetchByIdsForOrderCreate(array $ids, int $languageId, float $rate)
    {
        return $this->model->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"), 'media_id', 'sku', 'tax_status')
            ->where('status', true)
            ->where('stock_status', true)
            ->whereIn('id', $ids)
            ->when(Auth::user()?->customer_group_id, function ($query) use ($rate) {
                $query->with([
                    'product_variant_prices' => function ($query) use ($rate) {
                        $query->select([
                            'product_variant_id', 'min', DB::raw("price * {$rate} as price")
                        ])
                            ->where('customer_group_id', Auth::user()->customer_group_id)
                            ->orderBy('min', 'desc');
                    },
                ]);
            })
            ->with([
                'product_variant_translation' => function ($q) use ($languageId) {
                    return $q->select('id', 'product_variant_id', 'media_id')
                        ->where('language_id', $languageId);
                },
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'type', 'name', 'overwrite_price')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $languageId)
                        ->with([
                            'product_variant_main' => function ($attributesQuery) {
                                $attributesQuery->select('product_id', 'media_id', 'id', 'stock_status')
                                    ->with([
                                        'items' => function ($query) {
                                            $query->select('items.id', 'net_weight', 'gross_weight', 'un_numbers', 'quantity', 'production_price',
                                                'category_id', 'regular_price', 'sku', 'name');
                                        }
                                    ]);
                            },
                        ]);
                },
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attributes.id', 'attribute_type_id', 'value')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->with([
                            'attribute_type' => function ($query) use ($languageId) {
                                $query->select('attribute_types.id', 'name')
                                    ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                    ->where('attribute_type_translations.language_id', $languageId);
                            }
                        ]);
                },
                'items' => function ($query) use ($languageId) {
                    $query->select('items.id', 'net_weight', 'gross_weight', 'un_numbers', 'quantity', 'production_price',
                        'category_id', 'regular_price', 'sku', 'name');
                }
            ])
            ->get();
    }

    public function fetchByIdsForCheckout(array $ids, int $languageId, float $rate)
    {
        return $this->model->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"), 'tax_status')
            ->where('status', true)
            ->where('stock_status', true)
            ->whereIn('id', $ids)
            ->with([
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'type', 'overwrite_price')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $languageId);
                },
                'items' => function ($query) use ($languageId) {
                    $query->select('items.id', 'net_weight', 'gross_weight', 'un_numbers', 'quantity', 'production_price',
                        'category_id', 'regular_price', 'sku', 'name');
                }
            ])
            ->when(Auth::user()?->customer_group_id, function ($query) use ($rate) {
                $query->with([
                    'product_variant_prices' => function ($query) use ($rate) {
                        $query->select([
                            'product_variant_id', 'min', DB::raw("price * {$rate} as price")
                        ])
                            ->where('customer_group_id', Auth::user()->customer_group_id)
                            ->orderBy('min', 'desc');
                    },
                ]);
            })
            ->get();
    }

    public function deleteByProductId(int $productId, bool $deleteInvalids)
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('is_main', false)
            ->when($deleteInvalids, function ($query) use ($deleteInvalids) {
                $query->whereNull('regular_price');
            })
            ->delete();
    }

    public function getProductIdsBySKUs(array $skus)
    {
        return $this->model->select('product_id')
            ->whereIn('sku', $skus)
            ->pluck('product_id')
            ->toArray();
    }

    public function getProductIdBySKU(string $sku)
    {
        return $this->model->select('product_id')
            ->where('sku', $sku)
            ->value('product_id');
    }

    public function calculatePriceAdjustment(?float $priceAdjustment, int $categoryId)
    {
        return $this->model->whereHas('product', function ($query) use ($categoryId) {
            $query->whereHas('primary_category', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        })->update([
            'regular_price' => DB::raw(
                $priceAdjustment > 0
                    ? 'base_price * (1 + ' . ($priceAdjustment / 100) . ')'
                    : ($priceAdjustment === 0 || $priceAdjustment === null
                    ? 'base_price'
                    : 'base_price'
                )
            ),
            'sales_price' => DB::raw(
                $priceAdjustment < 0
                    ? 'base_price * (1 + ' . ($priceAdjustment / 100) . ')'
                    : 'NULL'
            ),
        ]);
    }

    public function getSKUForReviewUpload(string $sku)
    {
        return $this->model->select('product_id')
            ->where('sku', $sku)
            ->where('is_main', true)
            ->value('product_id');
    }

    public function getVariantInfoByFirstAttribute(int $attributeValueId, int $productId, int $languageId): ?Model
    {
        return $this->model->select('product_variants.id', 'name', 'short_description', 'description', 'product_variants.media_id')
            ->where('product_id', $productId)
            ->where('status', true)
            ->whereHas('product_variant_attributes', function ($query) use ($attributeValueId) {
                $query->where('attribute_id', $attributeValueId);
            })
            ->leftJoin('product_variant_translations', function ($join) use ($languageId) {
                $join->on('product_variant_translations.product_variant_id', '=', 'product_variants.id')
                    ->where('product_variant_translations.language_id', $languageId);
            })
            ->with([
                'gallery' => function ($query) {
                    $query->select([
                        'product_variant_galleries.id',
                        'product_variant_galleries.media_id',
                        'product_variant_galleries.product_variant_id',
                        'product_variant_galleries.video_type',
                        'product_variant_galleries.video_url',
                        'media.type',
                        'media.original_path AS path',
                        'media.path as general_path',
                        'media.width',
                        'media.height',
                    ])->join('media', 'product_variant_galleries.media_id', '=', 'media.id');
                },
                'media' => function ($imageQuery) use ($languageId) {
                    $imageQuery->select([
                        'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                    ])
                        ->leftJoin('media_translations', function ($join) use ($languageId) {
                            $join->on('media_translations.media_id', '=', 'media.id')
                                ->where('media_translations.language_id', $languageId);
                        });
                },
                'product_variant_translation' => function ($q) use ($languageId) {
                    return $q->select('id', 'product_variant_id', 'media_id')
                        ->where('language_id', $languageId)
                        ->with([
                            'gallery' => function ($galleryQuery) {
                                $galleryQuery->join('media', 'product_variant_translation_galleries.media_id', '=', 'media.id')
                                    ->select([
                                        'product_variant_translation_galleries.id',
                                        'product_variant_translation_galleries.media_id',
                                        'product_variant_translation_galleries.product_variant_translation_id',
                                        'product_variant_translation_galleries.video_type',
                                        'product_variant_translation_galleries.video_url',
                                        'media.type',
                                        'media.original_path AS path',
                                        'media.path as general_path',
                                    ]);
                            },
                            'media' => function ($imageQuery) use ($languageId) {
                                $imageQuery->select([
                                    'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                                ])
                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                        $join->on('media_translations.media_id', '=', 'media.id')
                                            ->where('media_translations.language_id', $languageId);
                                    });
                            },
                        ]);
                }
            ])
            ->orderBy('regular_price', 'asc')
            ->first();
    }

    public function getFeedVariantInfoByFirstAttribute(int $attributeValueId, int $productId, int $languageId, float $rate): ?Model
    {
        return $this->model
            ->select('product_variants.id', 'short_description', 'description', 'name', 'sku',
                'regular_price as regular_price_old', DB::raw("regular_price * {$rate} as regular_price"),
                DB::raw("sales_price * {$rate} as sales_price"), 'stock_status', 'product_variants.media_id')
            ->where('product_id', $productId)
            ->where('status', true)
            ->whereHas('product_variant_attributes', function ($query) use ($attributeValueId) {
                $query->where('attribute_id', $attributeValueId);
            })
            ->leftJoin('product_variant_translations', function ($join) use ($languageId) {
                $join->on('product_variant_translations.product_variant_id', '=', 'product_variants.id')
                    ->where('product_variant_translations.language_id', $languageId);
            })
            ->with([
                'gallery' => function ($query) {
                    $query->select([
                        'product_variant_galleries.id',
                        'product_variant_galleries.media_id',
                        'product_variant_galleries.product_variant_id',
                        'product_variant_galleries.video_type',
                        'product_variant_galleries.video_url',
                        'media.type',
                        'media.original_path AS path',
                        'media.path as general_path',
                        'media.width',
                        'media.height',
                    ])->join('media', 'product_variant_galleries.media_id', '=', 'media.id');
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
                'product_variant_translation' => function ($q) use ($languageId) {
                    return $q->select('id', 'product_variant_id', 'media_id')
                        ->where('language_id', $languageId)
                        ->with([
                            'gallery' => function ($galleryQuery) {
                                $galleryQuery->join('media', 'product_variant_translation_galleries.media_id', '=', 'media.id')
                                    ->select([
                                        'product_variant_translation_galleries.id',
                                        'product_variant_translation_galleries.media_id',
                                        'product_variant_translation_galleries.product_variant_translation_id',
                                        'product_variant_translation_galleries.video_type',
                                        'product_variant_translation_galleries.video_url',
                                        'media.type',
                                        'media.original_path AS path'
                                    ]);
                            },
                            'media' => function ($imageQuery) {
                                $imageQuery->select([
                                    'id', 'type', 'original_path AS path'
                                ]);
                            },
                        ]);
                },
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attribute_type_id', 'attribute_translations.slug')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('language_id', $languageId)
                        ->with([
                            'attribute_type' => function ($query) use ($languageId) {
                                $query->select('attribute_types.id', 'attribute_type_translations.slug')
                                    ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                    ->where('language_id', $languageId);
                            }
                        ]);
                }
            ])
            ->orderBy('regular_price', 'asc')
            ->first();
    }

    public function getMinVariantForProductPriceUpdate(int $productId)
    {
        return $this->model->select('regular_price', 'base_price', 'sales_price', 'size_by_unit')
            ->where('product_id', $productId)
            ->where('status', true)
            ->where('stock_status', true)
            ->where('is_main', false)
            ->orderBy('regular_price', 'asc')
            ->first();
    }

    public function updateStatusFromItems(int $itemId, bool $status, string $statusField)
    {
        return $this->model
            ->when($status, function ($query) {
                $query->whereNotNull('regular_price');
            })
            ->whereHas('product_variant_parents', function ($query) use ($itemId) {
                $query->where('item_id', $itemId);
            })
            ->when($status, function ($query) use ($statusField) {
                $query->whereDoesntHave('product_variant_parents', function ($query) use ($statusField) {
                    $query->join('items', 'items.id', '=', 'product_variant_parents.item_id')
                        ->where($statusField, false);
                });
            })
            ->where('independent_stock_status', false)
            ->update([
                $statusField => $status
            ]);
    }

    public function getItemPrice(int $productVariantId, float $rate)
    {
        return $this->model->select(DB::raw("regular_price * {$rate} as regular_price"), DB::raw("sales_price * {$rate} as sales_price"))
            ->where('id', $productVariantId)
            ->first();
    }

    public function getProductIds(array $ids)
    {
        return $this->model->select('product_id')
            ->whereIn('id', $ids)
            ->pluck('product_id', 'product_id')
            ->toArray();
    }

    public function getProductVariantInfoForOffer(int $id, int $languageId)
    {
        return $this->model->select('id', 'product_id', 'sku')
            ->where('id', $id)
            ->with(['product' => function ($query) use ($languageId) {
                $query->select('id')
                    ->with([
                        'product_translation' => function ($translationQuery) use ($languageId) {
                            $translationQuery->select('id', 'product_id', 'name')
                                ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                        }
                    ]);
            }, 'product_variant_parents' => function ($q) use ($languageId) {
                return $q->select('product_variant_id', 'items.net_weight', 'items.gross_weight', 'quantity')
                    ->join('items', 'items.id', '=', 'product_variant_parents.item_id');
            }])
            ->first();
    }

    public function getIdByKey(string $key)
    {
        return $this->model->select('id')
            ->where('key', $key)
            ->value('id');
    }

    public function getVariantByKey(string $key, string $selectFields)
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('key', $key)
            ->first();
    }

    public function getMainVariantByProductId(int $productId, string $selectFields)
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('product_id', $productId)
            ->where('is_main', true)
            ->first();
    }

    public function deleteVariantsExceptSelectedIds(array $array, int $productId)
    {
        return $this->model->whereNotIn('id', $array)->where('product_id', $productId)->delete();
    }

    public function getFieldByProductMainId(int $productId, string $field)
    {
        return $this->model->select($field)
            ->where('product_id', $productId)
            ->where('is_main', true)
            ->value($field);
    }

    public function fetchForCatalogExport(float $rate, array $categoryIds, int $languageId)
    {
        $fullData = [];

        $this->model->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
            DB::raw("sales_price * {$rate} as sales_price"), 'stock_status', 'is_main', 'sku')
            ->where('status', true)
            ->when(!empty($categoryIds), function ($q) use ($categoryIds) {
                $q->whereHas('product', function ($q) use ($categoryIds) {
                    $q->join('product_categories', 'products.id', 'product_categories.product_id')->whereIn('category_id', $categoryIds);
                });
            })
            ->with([
                'product' => function ($query) use ($languageId) {
                    $query->select('products.id', 'name', 'path', 'type')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $languageId)
                        ->with([
                            'product_variant_main' => function ($attributesQuery) {
                                $attributesQuery->select('product_id', 'stock_status');
                            },
                        ]);
                },
                'attributes' => function ($query) use ($languageId) {
                    $query->select('attributes.id', 'attribute_type_id', 'value', 'slug')
                        ->join('attribute_translations', 'attribute_translations.attribute_id', '=', 'attributes.id')
                        ->where('attribute_translations.language_id', $languageId)
                        ->with([
                            'attribute_type' => function ($query) use ($languageId) {
                                $query->select('attribute_types.id', 'name', 'slug')
                                    ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                    ->where('attribute_type_translations.language_id', $languageId);
                            }
                        ]);
                }
            ])
            ->when(Auth::user()?->customer_group_id, function ($query) use ($rate) {
                $query->with([
                    'product_variant_prices' => function ($query) use ($rate) {
                        $query->select([
                            'product_variant_id', 'min', DB::raw("price * {$rate} as price")
                        ])
                            ->where('customer_group_id', Auth::user()->customer_group_id)
                            ->orderBy('min', 'asc');
                    },
                ]);
            })
            ->chunkById(100, function ($variants) use (&$fullData) {
                $chunkData = $variants->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'product_variants.id', 'id');

        return $fullData;
    }

    public function fetchForTranslations(int $productId, int $languageId): array
    {
        $fullData = [];

        $this->model->select(
            'product_variants.id', 'product_variants.media_id as main_media_id', 'product_variant_translations.media_id as translation_media_id',
            'name', 'short_description', 'description', 'product_variant_id'
        )
            ->join('product_variant_translations', 'product_variant_translations.product_variant_id', '=', 'product_variants.id')
            ->where('product_id', $productId)
            ->where('language_id', $languageId)
            ->where(function ($query) {
                $query->orWhereNotNull('name')
                    ->orWhereNotNull('short_description')
                    ->orWhereNotNull('description');
            })
            ->chunkById(100, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'product_variants.id', 'id');


        return $fullData;
    }
}
