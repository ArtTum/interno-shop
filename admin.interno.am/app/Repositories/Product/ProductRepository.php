<?php

namespace App\Repositories\Product;

use App\Constants\AttributeConstants;
use App\Constants\MarketplaceConstants;
use App\Constants\OrderConstants;
use App\Constants\ProductConstants;
use App\Constants\ReviewConstants;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Product\Interfaces\ProductRepositoryInterface;
use App\Traits\Contents\ContentTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    use ContentTrait;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->with([
                'product_translation' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'product_id', 'name', 'path')->where('language_id', $baseLanguageId);
                },
                'product_variant_main' => function ($variantQuery) use ($params) {
                    $variantQuery->select('id', 'product_id', 'media_id')
                        ->with([
                            'media' => function ($imageQuery) {
                                $imageQuery->select([
                                    'id', 'original_path AS path'
                                ]);
                            },
                            'product_variant_translation' => function ($q) use ($params) {
                                return $q->select('id', 'product_variant_id', 'media_id')
                                    ->where('language_id', $params['base_language_id'])
                                    ->with([
                                        'media' => function ($imageQuery) {
                                            $imageQuery->select('id', 'original_path AS path');
                                        },
                                    ]);
                            }
                        ]);
                }
            ])
            ->get();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByWatermarkImage(string $whereField, string|int $whereValue, int $languageId, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'product_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'product_id', 'language_id', 'watermark_settings')
                        ->where('language_id', $languageId);
                }
            ])
            ->first();
    }

    public function bulkDelete(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchForProductTranslations(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): array
    {
        $fullData = [];
        self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->chunkById(500, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'products.id', 'id');

        return $fullData;
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        $product = $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'drafted_product' => function ($q) {
                    $q->select('id', 'real_product_id');
                },
                'product_multiselect' => function ($multiselectQuery) use ($languageId) {
                    $multiselectQuery->select('product_multiselects.id as id', 'product_id', 'options_limit', 'title', 'description')
                        ->leftJoin('product_multiselect_translations', function ($join) use ($languageId) {
                            $join->on('product_multiselect_translations.product_multiselect_id', '=', 'product_multiselects.id')
                                ->where('product_multiselect_translations.language_id', $languageId);
                        })
                        ->with([
                            'product_multiselect_options' => function ($multiselectQuery) use ($languageId) {
                                $multiselectQuery->select('product_multiselect_options.id', 'product_multiselect_id', 'media_id', 'additional_price', 'title')
                                    ->leftJoin('product_multiselect_option_translations', function ($join) use ($languageId) {
                                        $join->on('product_multiselect_option_translations.product_multiselect_option_id', '=', 'product_multiselect_options.id')
                                            ->where('product_multiselect_option_translations.language_id', $languageId);
                                    })
                                    ->with([
                                        'product_multiselect_option_parents' => function ($q) use ($languageId) {
                                            return $q->select('product_multiselect_option_id', 'items.sku', 'quantity')
                                                ->join('items', 'items.id', '=', 'product_multiselect_option_parents.item_id');
                                        },
                                        'media' => function ($imageQuery) {
                                            $imageQuery->select([
                                                'id', 'type', 'original_path AS path'
                                            ]);
                                        },
                                    ]);
                            }
                        ]);
                },
                'product_variant_main' => function ($attributesQuery) use ($languageId) {
                    $attributesQuery->select('id', 'product_id', 'sku',
                        'regular_price', 'sales_price', 'tax_status', 'media_id', 'stock_status', 'independent_stock_status',
                        'status')
                        ->with([
                            'product_variant_prices' => function ($q) {
                                $q->select('id', 'product_variant_id', 'customer_group_id', 'min', 'price');
                            },
                            'product_variant_parents' => function ($q) use ($languageId) {
                                return $q->select('product_variant_id', 'items.sku', 'quantity')
                                    ->join('items', 'items.id', '=', 'product_variant_parents.item_id');
                            },
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
                            },
                            'reel' => function ($galleryQuery) {
                                $galleryQuery->join('media', 'product_variant_reels.media_id', '=', 'media.id')
                                    ->select([
                                        'product_variant_reels.id',
                                        'product_variant_reels.media_id',
                                        'product_variant_reels.product_variant_id',
                                        'product_variant_reels.video_type',
                                        'product_variant_reels.video_url',
                                        'media.type',
                                        'media.original_path AS path'
                                    ]);
                            },
                            'shorts' => function ($galleryQuery) {
                                $galleryQuery->select(['id', 'product_variant_id', 'shorts_urls']);
                            },
                            'media' => function ($imageQuery) {
                                $imageQuery->select([
                                    'id', 'type', 'original_path AS path'
                                ]);
                            },
                            'product_variant_custom_field_translation' => function ($q) use ($languageId) {
                                return $q->select(
                                    'id', 'product_variant_id', 'key', 'value',
                                    DB::raw('false as changed'), DB::raw('false as deleted')
                                )
                                    ->where('language_id', $languageId);
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
                                        'reel' => function ($galleryQuery) {
                                            $galleryQuery->join('media', 'product_variant_translation_reels.media_id', '=', 'media.id')
                                                ->select([
                                                    'product_variant_translation_reels.id',
                                                    'product_variant_translation_reels.media_id',
                                                    'product_variant_translation_reels.product_variant_translation_id',
                                                    'product_variant_translation_reels.video_type',
                                                    'product_variant_translation_reels.video_url',
                                                    'media.type',
                                                    'media.original_path AS path'
                                                ]);
                                        },
                                        'shorts' => function ($galleryQuery) {
                                            $galleryQuery->select(['id', 'product_variant_translation_id', 'shorts_urls']);
                                        },
                                        'media' => function ($imageQuery) {
                                            $imageQuery->select([
                                                'id', 'type', 'original_path AS path'
                                            ]);
                                        },
                                    ]);
                            }
                        ]);
                },
                'categories' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'category_id');
                },
                'primary_category' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'category_id');
                },
                'upsells' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'bundling' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'product_gift_cards' => function ($categoriesQuery) {
                    $categoriesQuery->select('currency_id', 'product_id', 'price');
                },
                'cross_sells' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'extra_products' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id', 'required_extra', 'show_extra_prices');
                },
                'related' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
            ])
            ->when($languageId > 0, function ($query) use ($languageId) {
                $query->with([
                    'product_translation' => function ($translationQuery) use ($languageId) {
                        $translationQuery->select('id', 'product_id', 'language_id', 'slug', 'name', 'watermark_settings', 'attributes_description_popup',
                            'sub_name', 'short_description', 'description', 'meta_title', 'meta_keywords', 'meta_description',
                            'snippet_id', 'a_plus_content_id', 'sec_a_plus_content_id', 'bundle_label', 'approved', 'translation_status', 'category_inheritance'
                        )->where('language_id', $languageId);
                    }
                ]);
            })
            ->withCount('reviews')
            ->first();

        $parents = '';
        if (!empty($product->product_variant_main->product_variant_parents)) {
            $str = '';

            foreach ($product->product_variant_main->product_variant_parents as $parent) {
                $str .= $parent->quantity . 'x' . $parent->sku . ',';
            }
            $product->product_variant_main->parents = rtrim($str, ',');
        } else {
            $product->product_variant_main->parents = $parents;
        }

        if (!empty($product->product_multiselect) && !empty($product->product_multiselect->product_multiselect_options)) {
            $multiselectOptionsPreparedArr = [];

            foreach ($product->product_multiselect->product_multiselect_options as $option) {
                $optionParents = '';

                foreach ($option->product_multiselect_option_parents as $optionParent) {
                    $optionParents .= $optionParent->quantity . 'x' . $optionParent->sku . ',';
                }

                $multiselectOptionsPreparedArr[] = [
                    'media' => [$option->media],
                    'media_id' => $option->media_id,
                    'id' => $option->id,
                    'title' => $option->title,
                    'additional_price' => $option->additional_price,
                    'parents' => rtrim($optionParents, ',')
                ];
            }

            $product->multiselect = [
                'options_limit' => $product->product_multiselect->options_limit,
                'id' => $product->product_multiselect->id,
                'title' => $product->product_multiselect->title,
                'description' => $product->product_multiselect->description,
                'options' => $multiselectOptionsPreparedArr,
            ];
        } else {
            $product->multiselect = [
                'options_limit' => 10,
                'id' => $product->product_multiselect?->id,
                'title' => $product->product_multiselect?->title,
                'description' => $product->product_multiselect?->description,
                'options' => [],
            ];
        }

        return $product;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function forceDelete(int $id): bool
    {
        return $this->model->find($id)->forceDelete($id);
    }

    public function fetchAsProductParam(int $languageId): Collection
    {
        return $this->model->select('products.id as value', 'name as label')
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
            ->where('product_translations.language_id', $languageId)
            ->whereHas('product_translation')
            ->where('extra_product', false)
            ->get();
    }

    public function fetchExtraProducts(int $languageId): Collection
    {
        return $this->model->select('products.id as value', 'name as label')
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
            ->where('product_translations.language_id', $languageId)
            ->whereHas('product_translation')
            ->where('extra_product', true)
            ->get();
    }

    public function fetchRelatedsByProductId(int $productId): Model
    {
        return $this->model->select('id')
            ->where('id', $productId)
            ->with([
                'upsells' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'cross_sells' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'extra_products' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'related' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
                'bundling' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'related_product_id');
                },
            ])
            ->first();
    }

    public function autocomplete(string $field, ?string $searchTerm, int $languageId, array $alreadySelectIds): array
    {
        $limit = count($alreadySelectIds) + 10;

        $data = $this->model->select('id')
            ->whereHas('product_translation')
            ->with([
                'product_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'product_id', 'name')
                        ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                },
                'product_variant_main' => function ($attributesQuery) use ($languageId) {
                    $attributesQuery->select('product_id', 'sku');
                },
            ])
            ->where(function ($query) use ($field, $searchTerm, $alreadySelectIds) {
                $query->when(!empty($searchTerm), function ($query) use ($field, $searchTerm) {
                    $query->whereHas('product_translation', function ($q) use ($field, $searchTerm) {
                        $searchTerm = addslashes($searchTerm);
                        $q->whereRaw("$field LIKE '%$searchTerm%'");
                    });
                })->when(!empty($alreadySelectIds), function ($query) use ($alreadySelectIds) {
                    $query->orWhereIn('id', $alreadySelectIds);
                });
            })
            ->where('extra_product', false)
            ->limit($limit)
            ->offset(0)
            ->get();

        $preparedData = [];

        foreach ($data as $item) {
            $preparedData[] = [
                'value' => $item->id,
                'label' => $item->product_translation->name . ' (' . $item->product_variant_main->sku . ')',
            ];
        }

        return $preparedData;
    }

    public function fetchForOrderItem(int $productVariantId, int $languageId): array
    {
        return $this->model->select('products.id', 'product_translations.name')
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
            ->where('product_translations.language_id', $languageId)
            ->whereHas('product_variant_from_all', function ($query) use ($productVariantId) {
                $query->where('id', $productVariantId);
            })
            ->with([
                'product_variant_from_all' => function ($query) use ($productVariantId, $languageId) {
                    $query->select('id', 'product_id', 'sku', 'media_id', 'tax_status')
                        ->where('id', $productVariantId)
                        ->with([
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
                            'items' => function ($query) use ($languageId) {
                                $query->select('items.id', 'net_weight', 'gross_weight', 'un_numbers', 'quantity', 'production_price',
                                    'category_id', 'regular_price', 'sku', 'name');
                            },
                            'product_variant_translation' => function ($q) use ($languageId) {
                                return $q->select('id', 'product_variant_id', 'media_id')
                                    ->where('language_id', $languageId);
                            },
                        ]);
                }
            ])
            ->where('extra_product', false)
            ->first()
            ->toArray();
    }

    public function autocompleteForOrder(string $field, ?string $searchTerm, int $languageId, int $orderId): array
    {
        $searchTerm = addslashes($searchTerm);

        $data = $this->model
            ->select('products.id')
            ->with([
                'product_variations_all' => function ($query) use ($orderId, $field, $searchTerm) {
                    $query->select('id', 'product_id', 'sku')
                        ->where('status', true)
                        ->where(function ($query) {
                            $query->orWhere('is_main', false)
                                ->orWhereHas('product', function ($q) {
                                    $q->where('type', '!=', ProductConstants::TYPES['variable']);
                                });
                        })
                        ->where(function ($query) use ($field, $searchTerm) {
                            $query->orWhereHas('product', function ($q) use ($field, $searchTerm) {
                                $q->whereHas('product_translation', function ($q) use ($field, $searchTerm) {
                                    $q->whereRaw("$field LIKE '%$searchTerm%'");
                                });
                            })->orWhereRaw("sku LIKE '%$searchTerm%'");
                        })
                        ->whereDoesntHave('order_item', function ($query) use ($orderId) {
                            $query->where('order_id', $orderId);
                        });
                }
            ])
            ->whereHas('product_translation')
            ->with([
                'product_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'product_id', 'name')
                        ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                }
            ])
            ->where(function ($query) use ($field, $searchTerm) {
                $query->when(!empty($searchTerm), function ($query) use ($field, $searchTerm) {
                    $query->where(function ($query) use ($field, $searchTerm) {
                        $query->orWhereHas('product_translation', function ($q) use ($field, $searchTerm) {
                            $q->whereRaw("$field LIKE '%$searchTerm%'");
                        })->orWhereHas('product_variations_all', function ($q) use ($field, $searchTerm) {
                            $q->whereRaw("sku LIKE '%$searchTerm%'");
                        });
                    });
                });
            })
            ->where('extra_product', false)
            ->limit(10)
            ->offset(0)
            ->get();

        $preparedData = [];

        foreach ($data as $product) {
            foreach ($product->product_variations_all as $variation) {
                if (count($preparedData) === 10) {
                    break;
                }

                $preparedData[] = [
                    'value' => $variation->id,
                    'label' => $product->product_translation->name . ' (' . $variation->sku . ')',
                ];
            }
        }

        return $preparedData;
    }

    public function autocompleteForOffer(string $field, ?string $searchTerm, int $languageId, array $alreadySelectIds, array $productIds): array
    {
        $limit = count($alreadySelectIds) + 10;
        $searchTerm = addslashes($searchTerm);

        $data = $this->model
            ->select('products.id')
            ->when(!empty($alreadySelectIds), function ($query) use ($alreadySelectIds, $productIds) {
                $query->whereHas('product_variations_all', function ($q) use ($alreadySelectIds) {
                    $q->whereIn('id', $alreadySelectIds);
                })->whereIn('id', $productIds);;
            })
            ->with([
                'product_variations_all' => function ($query) use ($field, $searchTerm, $alreadySelectIds, $limit) {
                    $query->select('id', 'product_id', 'sku')
                        ->where('status', true)
                        ->where(function ($query) {
                            $query->orWhere('is_main', false)
                                ->orWhereHas('product', function ($q) {
                                    $q->where('type', '!=', ProductConstants::TYPES['variable']);
                                });
                        })
                        ->where(function ($query) use ($field, $searchTerm) {
                            $query->orWhereHas('product', function ($q) use ($field, $searchTerm) {
                                $q->whereHas('product_translation', function ($q) use ($field, $searchTerm) {
                                    $q->whereRaw("`name` LIKE '%$searchTerm%'");
                                });
                            })->orWhereRaw("sku LIKE '%$searchTerm%'");
                        })->when(!empty($alreadySelectIds), function ($query) use ($alreadySelectIds, $limit) {
                            $query->orderByRaw('FIELD(id, ' . implode(',', $alreadySelectIds) . ') DESC')->limit($limit);
                        });
                }
            ])
            ->whereHas('product_translation')
            ->with([
                'product_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'product_id', 'name')
                        ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                }
            ])
            ->where(function ($query) use ($field, $searchTerm) {
                $query->when(!empty($searchTerm), function ($query) use ($field, $searchTerm) {
                    $query->where(function ($query) use ($field, $searchTerm) {
                        $query->orWhereHas('product_translation', function ($q) use ($field, $searchTerm) {
                            $q->whereRaw("$field LIKE '%$searchTerm%'");
                        })->orWhereHas('product_variations_all', function ($q) use ($field, $searchTerm) {
                            $q->whereRaw("sku LIKE '%$searchTerm%'");
                        });
                    });
                });
            })
            ->where('extra_product', false)
            ->limit($limit)
            ->offset(0)
            ->get();

        $preparedData = [];

        foreach ($data as $product) {
            foreach ($product->product_variations_all as $variation) {
                if (count($preparedData) === $limit) {
                    break;
                }

                $preparedData[] = [
                    'value' => $variation->id,
                    'label' => $product->product_translation->name . ' (' . $variation->sku . ')',
                ];
            }
        }

        return $preparedData;
    }

// internal function
    private function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->where('product_variants.is_main', true)
            ->when($params['status'] > -1, function ($query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when($params['product_type'] > -1, function ($query) use ($params) {
                $query->where('type', $params['product_type']);
            })
            ->when($params['attribute_id'] > -1, function ($query) use ($params) {
                $query->whereHas('attributes_pivot', function ($q) use ($params) {
                    $q->where('attribute_id', $params['attribute_id']);
                });
            })
            ->when($params['item_id'] > -1, function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->orWhereHas('product_variant_from_all', function ($query) use ($params) {
                        $query->whereHas('product_variant_parents', function ($query) use ($params) {
                            $query->where('item_id', $params['item_id']);
                        });
                    })
                        ->orWhereHas('product_multiselect', function ($query) use ($params) {
                            $query->whereHas('product_multiselect_options', function ($query) use ($params) {
                                $query->whereHas('product_multiselect_option_parents', function ($query) use ($params) {
                                    $query->where('item_id', $params['item_id']);
                                });
                            });
                        });
                });
            })
            ->when($params['enable_reviews'] > -1, function ($query) use ($params) {
                $query->where('enable_reviews', $params['enable_reviews']);
            })
            ->when(!empty($params['category_ids']), function ($query) use ($params) {
                $query->whereHas('categories', function ($query) use ($params) {
                    $query->whereIn('category_id', $params['category_ids']);
                });
            })
            ->when($params['bestseller'] > -1, function ($query) use ($params) {
                $query->where('bestseller', $params['bestseller']);
            })
            ->when($params['tax_status'] > -1, function ($query) use ($params) {
                $query->where('tax_status', $params['tax_status']);
            })
            ->when($params['stock_status'] > -1, function ($query) use ($params) {
                $query->where('stock_status', $params['stock_status']);
            })
            ->when($params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('product_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when($params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('product_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when($params['regular_price_from'], function ($query) use ($params) {
                $query->where('regular_price', '>=', $params['regular_price_from']);
            })
            ->when($params['sku'], function ($query) use ($params) {
                $query->where('sku', $params['sku']);
            })
            ->when($params['regular_price_to'], function ($query) use ($params) {
                $query->where('regular_price', '<=', $params['regular_price_to']);
            })->when(!empty($params['source']), function ($query) use ($params) {
                $query->when($params['source'] === '-1' , function ($qu) use ($params) {
                    $qu->whereIn('source', [MarketplaceConstants::AMAZON, MarketplaceConstants::EBAY]);
                })->when($params['source'] !== '-1' , function ($qu) use ($params) {
                    $qu->where('source', $params['source']);
                });
            });
    }

    public function checkExistsById(int $id): bool
    {
        return $this->model->select('id')
            ->where('id', $id)
            ->exists();
    }

    public function fetchForExport(array $data): Collection
    {
        return $this->model
            ->with([
                'product_translation' => function ($translationQuery) use ($data) {
                    $translationQuery->select('product_id', 'name', 'sub_name', 'slug', 'short_description', 'watermark_settings', 'translation_status',
                        'description', 'meta_title', 'meta_keywords', 'meta_description', 'snippet_id', 'a_plus_content_id', 'sec_a_plus_content_id')
                        ->where('language_id', $data['language_id'])
                        ->with([
                            'a_plus_content' => function ($translationQuery) {
                                $translationQuery->select('id', 'page_id');
                            },
                            'sec_a_plus_content' => function ($translationQuery) {
                                $translationQuery->select('id', 'page_id');
                            },
                            'snippet' => function ($translationQuery) {
                                $translationQuery->select('id', 'page_id');
                            },
                        ]);
                },
                'product_variant_main' => function ($query) use ($data) {
                    $query->with([
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
                            ])->join('media', 'product_variant_galleries.media_id', '=', 'media.id');
                        },
                        'reel' => function ($reelQuery) {
                            $reelQuery->select([
                                'product_variant_reels.product_variant_id',
                                'product_variant_reels.video_type',
                                'product_variant_reels.video_url',
                                'media.type',
                                'media.original_path AS path'
                            ])->join('media', 'product_variant_reels.media_id', '=', 'media.id');
                        },
                        'shorts' => function ($galleryQuery) {
                            $galleryQuery->select(['id', 'product_variant_id', 'shorts_urls']);
                        },
                        'product_variant_custom_field_translation' => function ($query) use ($data) {
                            $query->select('product_variant_id', 'key', 'value')->where('language_id', $data['language_id']);
                        },
                        'media' => function ($imageQuery) {
                            $imageQuery->select('id', 'original_path');
                        },
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
                                    'reel' => function ($reelQuery) {
                                        $reelQuery->select([
                                            'product_variant_translation_reels.product_variant_translation_id',
                                            'product_variant_translation_reels.video_type',
                                            'product_variant_translation_reels.video_url',
                                            'media.type',
                                            'media.original_path AS path'
                                        ])->join('media', 'product_variant_translation_reels.media_id', '=', 'media.id');
                                    },
                                    'shorts' => function ($galleryQuery) {
                                        $galleryQuery->select(['id', 'product_variant_translation_id', 'shorts_urls']);
                                    },
                                ]);
                        }
                    ]);
                },
                'categories' => function ($categoriesQuery) {
                    $categoriesQuery->select('product_id', 'category_id');
                },
                'upsells' => function ($categoriesQuery) {
                    $categoriesQuery->select('sku')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
                        ->where('is_main', true);
                },
                'cross_sells' => function ($categoriesQuery) use ($data) {
                    $categoriesQuery->select('sku')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
                        ->where('is_main', true);
                },
                'extra_products' => function ($categoriesQuery) use ($data) {
                    $categoriesQuery->select('sku')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
                        ->where('is_main', true);
                },
                'related' => function ($categoriesQuery) {
                    $categoriesQuery->select('sku')
                        ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
                        ->where('is_main', true);
                },
                'variable_product_attributes' => function ($query) {
                    $query->select('product_id', 'attribute_id');
                },
                'simple_product_attributes' => function ($query) {
                    $query->select('product_id', 'attribute_id');
                },
                'related_reviewer_product' => function ($query) {
                    $query->select('id')
                        ->with([
                            'product_variant_main' => function ($query) {
                                $query->select('product_id', 'sku');
                            }
                        ]);
                }
            ])->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
                $query->whereIn('id', $data['ids'])
                    ->orderBy($data['ordering_field'], $data['ordering_direction']);
            })->get();
    }

    public function findBySKU(string $sku): ?Product
    {
        return $this->model->select('id')
            ->with([
                'product_variant_main' => function ($attributesQuery) {
                    $attributesQuery->select('id', 'product_id', 'media_id');
                },
            ])
            ->whereHas('product_variant_main', function ($query) use ($sku) {
                $query->where('sku', $sku);
            })
            ->first();
    }

    public function findBySKUForUploadProducts(string $sku, int $languageId): ?Product
    {
        return $this->model->select('id')
            ->with([
                'product_variant_main' => function ($attributesQuery) {
                    $attributesQuery->select('id', 'product_id', 'media_id');
                },
                'product_translation' => function ($query) use ($languageId) {
                    $query->select('id', 'product_id')->where('language_id', $languageId);
                }
            ])
            ->whereHas('product_variant_main', function ($query) use ($sku) {
                $query->where('sku', $sku);
            })
            ->first();
    }

    public function findByIdAndLanguage(int $id, int $languageId): ?Product
    {
        return $this->model->select('id', 'gtin')
            ->where('id', $id)
            ->with([
                'product_variant_main' => function ($attributesQuery) {
                    $attributesQuery->select('id', 'product_id', 'sku');
                },
                'product_translation' => function ($translationQuery) use ($languageId) {
                    $translationQuery->select('id', 'product_id', 'name', 'path')
                        ->where('language_id', $languageId);
                },
            ])
            ->first();
    }

    public function fetchForFront(int $languageId, string $slug, float $rate, string $baseCurrencyCode, int $customerUserGroupId)
    {
        return $this->model->select('id', 'calculator_id', 'type', 'enable_reviews',
            'created_at', 'updated_at', 'related_reviewer_id', 'new', 'overwrite_price', 'bundle_showing_type')
            ->whereHas('product_variant_main', function ($query) {
                $query->where('status', true);
            })
            ->where('extra_product', false)
            ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                $q->whereNull('real_product_id');
            })
            ->whereHas('product_translation', function ($query) use ($slug, $languageId) {
                $query->where('slug', $slug)->where('language_id', $languageId)->where('translation_status', true);
            })
            ->with([
                'product_multiselect' => function ($multiselectQuery) use ($languageId, $rate) {
                    $multiselectQuery->select('product_multiselects.id as id', 'product_id', 'options_limit', 'title', 'description')
                        ->leftJoin('product_multiselect_translations', function ($join) use ($languageId) {
                            $join->on('product_multiselect_translations.product_multiselect_id', '=', 'product_multiselects.id')
                                ->where('product_multiselect_translations.language_id', $languageId);
                        })
                        ->with([
                            'product_multiselect_options' => function ($multiselectQuery) use ($languageId, $rate) {
                                $multiselectQuery->select('product_multiselect_options.id', 'product_multiselect_id', 'media_id', DB::raw("additional_price * {$rate} as additional_price"), 'title')
                                    ->leftJoin('product_multiselect_option_translations', function ($join) use ($languageId) {
                                        $join->on('product_multiselect_option_translations.product_multiselect_option_id', '=', 'product_multiselect_options.id')
                                            ->where('product_multiselect_option_translations.language_id', $languageId);
                                    })
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
                                    ])
                                    ->whereHas('product_multiselect_option_parents', function ($q) {
                                        $q->join('items', 'product_multiselect_option_parents.item_id', 'items.id')
                                            ->where('items.status', true)
                                            ->where('items.stock_status', true);
                                    });
                            }
                        ]);
                },
                'product_variant_main' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'id as product_variant_id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                        DB::raw("sales_price * {$rate} as sales_price"), 'tax_status', 'media_id', 'size_by_unit',
                        'stock_status', 'sku')
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
                                        'media.original_path AS path',
                                        'media.path as general_path',
                                    ]);
                            },
                            'reel' => function ($galleryQuery) {
                                $galleryQuery->join('media', 'product_variant_reels.media_id', '=', 'media.id')
                                    ->select([
                                        'product_variant_reels.id',
                                        'product_variant_reels.media_id',
                                        'product_variant_reels.product_variant_id',
                                        'product_variant_reels.video_type',
                                        'product_variant_reels.video_url',
                                        'media.type',
                                        'media.original_path AS path',
                                        'media.path as general_path',
                                    ]);
                            },
                            'shorts' => function ($galleryQuery) {
                                $galleryQuery->select(['id', 'product_variant_id', 'shorts_urls']);
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
                                        'reel' => function ($galleryQuery) {
                                            $galleryQuery->join('media', 'product_variant_translation_reels.media_id', '=', 'media.id')
                                                ->select([
                                                    'product_variant_translation_reels.id',
                                                    'product_variant_translation_reels.media_id',
                                                    'product_variant_translation_reels.product_variant_translation_id',
                                                    'product_variant_translation_reels.video_type',
                                                    'product_variant_translation_reels.video_url',
                                                    'media.type',
                                                    'media.original_path AS path',
                                                    'media.path as general_path',
                                                ]);
                                        },
                                        'shorts' => function ($galleryQuery) {
                                            $galleryQuery->select(['id', 'product_variant_translation_id', 'shorts_urls']);
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
                        ]);
                },
                'product_translation' => function ($translationQuery) use ($languageId, $rate) {
                    $translationQuery->select('product_id', 'name', 'path', 'bundle_label', 'attributes_description_popup',
                        'sub_name', 'short_description', 'description', 'meta_title', 'meta_keywords', 'meta_description',
                        'snippet_id', 'a_plus_content_id', 'sec_a_plus_content_id', 'category_inheritance')
                        ->with([
                            'a_plus_content' => function ($query) use ($languageId, $rate) {
                                $query->select('page_translations.id', 'name', 'button_text', 'pages.type')
                                    ->join('pages', 'pages.id', '=', 'page_translations.page_id')
                                    ->where('pages.status', true);

                                return $this->builderElementsQuery($query, $languageId, $rate);
                            },
                            'sec_a_plus_content' => function ($query) use ($languageId, $rate) {
                                $query->select('page_translations.id', 'name', 'button_text', 'pages.type')
                                    ->join('pages', 'pages.id', '=', 'page_translations.page_id')
                                    ->where('pages.status', true);

                                return $this->builderElementsQuery($query, $languageId, $rate);
                            },
                            'snippet' => function ($query) use ($languageId, $rate) {
                                $query->select('page_translations.id', 'name', 'button_text')
                                    ->join('pages', 'pages.id', '=', 'page_translations.page_id')
                                    ->where('pages.status', true);

                                return $this->builderElementsQuery($query, $languageId, $rate);
                            },
                        ])
                        ->where('language_id', $languageId);
                },
                'calculator' => function ($query) use ($languageId) {
                    $query->select('id')
                        ->with([
                            'calculator_translation' => function ($translationQuery) use ($languageId) {
                                $translationQuery->select('calculator_id', 'config', 'name')->where('language_id', $languageId);
                            }
                        ]);
                },
                'reviews' => function ($query) use ($languageId) {
                    $query->select
                    (
                        'product_reviews.id', 'product_id', 'name', 'rating', 'product_reviews.text', 'product_reviews.created_at',
                        'country_code', 'product_review_translations.text as translated_text'
                    )
                        ->where('status', true)
                        ->with([
                            'attachments' => function ($q) {
                                $attachmentTypes = ReviewConstants::TYPES;
                                $q->select([
                                    'product_review_attachments.id',
                                    'product_review_attachments.product_review_id',
                                    'path as general_path',
                                    'product_reviews.name',
                                    DB::raw("CONCAT('Review attachment by: ', product_reviews.name) as alt"),
                                    DB::raw("CASE type
                                    WHEN {$attachmentTypes['video']} THEN 'video'
                                    WHEN {$attachmentTypes['image']} THEN 'image'
                                    END as type")
                                ])
                                    ->join('product_reviews', 'product_reviews.id', '=', 'product_review_attachments.product_review_id')
                                    ->orderBy('priority', 'ASC');
                            }
                        ])
                        ->leftJoin('product_review_translations', function ($join) use ($languageId) {
                            $join->on('product_review_translations.product_review_id', '=', 'product_reviews.id')
                                ->where('product_review_translations.language_id', $languageId);
                        })
                        ->orderBy('product_reviews.created_at', 'desc')
                        ->limit(ProductConstants::REVIEWS_LIMIT_FOR_RENDERING);
                },
                'review_statistics' => function ($query) use ($languageId) {
                    $query->selectRaw('
                            product_id,
                            AVG(rating) as avg_rating,
                            COUNT(*) as total_reviews,
                            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1_count,
                            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2_count,
                            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3_count,
                            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4_count,
                            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5_count
                        ')
                        ->where('status', true)
                        ->groupBy('product_id');
                },
                'product_variations' => function ($query) use ($rate) {
                    $query->selectRaw("product_id, MIN(regular_price) * {$rate} as min_price, MAX(regular_price) * {$rate} as max_price, COUNT(*) as variant_count")
                        ->whereNotNull('regular_price')
                        ->whereNotNull('sku')
                        ->groupBy('product_id');
                },
                'primary_category_pivot' => function ($query) use ($languageId, $rate) {
                    $query->select('categories.id', 'category_translations.name', 'path', 'breadcrumb', 'calculator_id')
                        ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                        ->where('category_translations.language_id', $languageId)
                        ->with([
                            'category_translation' => function ($translationQuery) use ($languageId, $rate) {
                                $translationQuery->select('category_id', 'related_category_translation_id', 'snippet_id', 'a_plus_content_id')
                                    ->with([
                                        'related' => function ($query) use ($languageId) {
                                            $query->select('id', 'category_id');
                                        },
                                        'a_plus_content' => function ($query) use ($languageId, $rate) {
                                            $query->select('page_translations.id', 'name', 'button_text', 'pages.type')
                                                ->join('pages', 'pages.id', '=', 'page_translations.page_id')
                                                ->where('pages.status', true);

                                            return $this->builderElementsQuery($query, $languageId, $rate);
                                        },
                                        'snippet' => function ($query) use ($languageId, $rate) {
                                            $query->select('page_translations.id', 'name', 'button_text')
                                                ->join('pages', 'pages.id', '=', 'page_translations.page_id')
                                                ->where('pages.status', true);

                                            return $this->builderElementsQuery($query, $languageId, $rate);
                                        },
                                    ]);
                            },
                            'calculator' => function ($query) use ($languageId) {
                                $query->select('id')
                                    ->with([
                                        'calculator_translation' => function ($translationQuery) use ($languageId) {
                                            $translationQuery->select('calculator_id', 'config', 'name')
                                                ->where('language_id', $languageId);
                                        }
                                    ]);
                            }
                        ]);
                },
                'categories' => function ($query) use ($languageId) {
                    $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                        ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                        ->where('category_translations.language_id', $languageId);
                },
                'cross_sells' => function ($query) use ($languageId, $rate, $customerUserGroupId) {
                    $productTypeConstants = ProductConstants::TYPES;
                    $query->select('related_product_id as id', 'new', DB::raw("CASE products.type
                    WHEN {$productTypeConstants['simple']} THEN 'simple'
                    WHEN {$productTypeConstants['variable']} THEN 'variable'
                    WHEN {$productTypeConstants['bundle']} THEN 'bundle'
                    WHEN {$productTypeConstants['gift_card']} THEN 'gift_card'
                    END as type"), 'name', 'short_description', 'sub_name', 'path')
                        ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                        ->where('product_translations.language_id', $languageId)
                        ->where('product_translations.translation_status', true)
                        ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                            $q->whereNull('real_product_id');
                        })
                        ->with([
                            'product_variant_main' => function ($query) use ($languageId, $rate) {
                                $query->select('id', 'product_id', 'sku', DB::raw("regular_price * {$rate} as regular_price"),
                                    DB::raw("sales_price * {$rate} as sales_price"), 'media_id', 'stock_status')
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
                                    });
                            },
                            'attributes' => function ($query) use ($languageId) {
                                $query->select('attributes.id', 'value', 'attribute_type_id', 'slug')
                                    ->join('attribute_translations', 'attributes.id', '=', 'attribute_translations.attribute_id')
                                    ->where('attribute_translations.language_id', $languageId)
                                    ->with([
                                        'attribute_type' => function ($query) use ($languageId) {
                                            $query->select('attribute_types.id', 'attribute_type_translations.name', 'attribute_type_translations.label', 'attribute_type_translations.slug')
                                                ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                                ->where('attribute_type_translations.language_id', $languageId);
                                        }
                                    ])
                                    ->whereHas('attribute_type', function ($query) use ($languageId) {
                                        $query->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation']);
                                    })
                                    ->orderBy('attributes.priority', 'asc')
                                    ->orderBy('attributes.id', 'asc');
                            },
                            'categories' => function ($query) use ($languageId) {
                                $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                                    ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                                    ->where('category_translations.language_id', $languageId);
                            },
                            'primary_category_pivot' => function ($query) use ($languageId) {
                                $query->select('categories.id', 'category_translations.name', 'path')
                                    ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                                    ->where('category_translations.language_id', $languageId)
                                    ->with([
                                        'category_translation' => function ($translationQuery) use ($languageId) {
                                            $translationQuery->select('category_id', 'related_category_translation_id')
                                                ->with([
                                                    'related' => function ($query) use ($languageId) {
                                                        $query->select('id', 'category_id');
                                                    }
                                                ]);
                                        }
                                    ]);
                            }
                        ])
                        ->whereHas('product_variant_main', function ($query) {
                            $query->where('status', true)
                                ->where('stock_status', true);
                        });
                },
                'bundling' => function ($query) use ($languageId, $rate, $customerUserGroupId) {
                    $query->select('related_product_id as id', 'name', 'path')
                        ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                        ->where('product_translations.language_id', $languageId)
                        ->where('product_translations.translation_status', true)
                        ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                            $q->whereNull('real_product_id');
                        })
                        ->with([
                            'product_variant_main' => function ($query) use ($languageId, $rate) {
                                $query->select('id', 'product_id', 'media_id')
                                    ->with([
                                        'media' => function ($imageQuery) use ($languageId) {
                                            $imageQuery->select([
                                                'media.id', 'alt', 'path as general_path', 'width', 'height'
                                            ])
                                                ->leftJoin('media_translations', function ($join) use ($languageId) {
                                                    $join->on('media_translations.media_id', '=', 'media.id')
                                                        ->where('media_translations.language_id', $languageId);
                                                });
                                        }
                                    ]);
                            },
                        ])
                        ->whereHas('product_variant_main', function ($query) {
                            $query->where('status', true)
                                ->where('stock_status', true);
                        });
                },
                'related' => function ($query) use ($languageId, $rate, $customerUserGroupId) {
                    $productTypeConstants = ProductConstants::TYPES;
                    $query->select('related_product_id as id', 'new', DB::raw("CASE products.type
                    WHEN {$productTypeConstants['simple']} THEN 'simple'
                    WHEN {$productTypeConstants['variable']} THEN 'variable'
                    WHEN {$productTypeConstants['bundle']} THEN 'bundle'
                    WHEN {$productTypeConstants['gift_card']} THEN 'gift_card'
                    END as type"), 'name', 'short_description', 'sub_name', 'path')
                        ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                        ->where('product_translations.language_id', $languageId)
                        ->where('product_translations.translation_status', true)
                        ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                            $q->whereNull('real_product_id');
                        })
                        ->with([
                            'product_variant_main' => function ($query) use ($languageId, $rate) {
                                $query->select('id', 'product_id', 'sku', DB::raw("regular_price * {$rate} as regular_price"),
                                    DB::raw("sales_price * {$rate} as sales_price"), 'media_id', 'stock_status')
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
                                    });
                            },
                            'attributes' => function ($query) use ($languageId) {
                                $query->select('attributes.id', 'value', 'attribute_type_id', 'slug')
                                    ->join('attribute_translations', 'attributes.id', '=', 'attribute_translations.attribute_id')
                                    ->where('attribute_translations.language_id', $languageId)
                                    ->with([
                                        'attribute_type' => function ($query) use ($languageId) {
                                            $query->select('attribute_types.id', 'attribute_type_translations.name', 'attribute_type_translations.label', 'attribute_type_translations.slug')
                                                ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                                ->where('attribute_type_translations.language_id', $languageId);
                                        }
                                    ])
                                    ->whereHas('attribute_type', function ($query) use ($languageId) {
                                        $query->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation']);
                                    })
                                    ->orderBy('attributes.priority', 'asc')
                                    ->orderBy('attributes.id', 'asc');
                            },
                            'categories' => function ($query) use ($languageId) {
                                $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                                    ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                                    ->where('category_translations.language_id', $languageId);
                            },
                            'primary_category_pivot' => function ($query) use ($languageId) {
                                $query->select('categories.id', 'category_translations.name', 'path')
                                    ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                                    ->where('category_translations.language_id', $languageId)
                                    ->with([
                                        'category_translation' => function ($translationQuery) use ($languageId) {
                                            $translationQuery->select('category_id', 'related_category_translation_id')
                                                ->with([
                                                    'related' => function ($query) use ($languageId) {
                                                        $query->select('id', 'category_id');
                                                    }
                                                ]);
                                        }
                                    ]);
                            },
                        ])
                        ->whereHas('product_variant_main', function ($query) {
                            $query->where('status', true)
                                ->where('stock_status', true);
                        });
                },
                'extra_products' => function ($query) use ($languageId, $rate) {
                    $query->select('related_product_id as id', 'name', 'short_description', 'required_extra', 'show_extra_prices')
                        ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                        ->where('product_translations.language_id', $languageId);
                },
                'upsells' => function ($query) use ($languageId, $rate, $customerUserGroupId) {
                    $productTypeConstants = ProductConstants::TYPES;
                    $query->select('related_product_id as id', 'name', 'short_description', 'sub_name', 'new', 'path', 'related_reviewer_id', DB::raw("CASE products.type
                    WHEN {$productTypeConstants['simple']} THEN 'simple'
                    WHEN {$productTypeConstants['variable']} THEN 'variable'
                    WHEN {$productTypeConstants['bundle']} THEN 'bundle'
                    WHEN {$productTypeConstants['gift_card']} THEN 'gift_card'
                    END as type"))
                        ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
                        ->where('product_translations.language_id', $languageId)
                        ->where('product_translations.translation_status', true)
                        ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                            $q->whereNull('real_product_id');
                        })
                        ->with([
                            'product_variant_main' => function ($query) use ($languageId, $rate) {
                                $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                                    DB::raw("sales_price * {$rate} as sales_price"), 'media_id')
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
                                    });
                            },
                            'review_statistics' => function ($query) use ($languageId) {
                                $query->selectRaw('
                                                    product_id,
                                                    AVG(rating) as avg_rating,
                                                    COUNT(*) as total_reviews
                                                ')
                                    ->where('status', true)
                                    ->groupBy('product_id');
                            },
                            'related_reviewer_product' => function ($query) use ($languageId) {
                                $query->select('id')
                                    ->with([
                                        'review_statistics' => function ($query) use ($languageId) {
                                            $query->selectRaw('
                                                    product_id,
                                                    AVG(rating) as avg_rating,
                                                    COUNT(*) as total_reviews
                                                ')
                                                ->where('status', true)
                                                ->groupBy('product_id');
                                        }
                                    ]);
                            }
                        ])
                        ->whereHas('product_variant_main', function ($query) {
                            $query->where('status', true)
                                ->where('stock_status', true);
                        });
                },
                'related_reviewer_product' => function ($query) use ($languageId) {
                    $query->select('id')
                        ->with([
                            'reviews' => function ($query) use ($languageId) {
                                $query->select('product_reviews.id', 'product_id', 'name', 'rating', 'product_reviews.text', 'product_review_translations.text as translated_text',
                                    'product_reviews.created_at', 'country_code')
                                    ->where('status', true)
                                    ->with([
                                        'attachments' => function ($q) {
                                            $attachmentTypes = ReviewConstants::TYPES;
                                            $q->select([
                                                'product_review_attachments.id',
                                                'product_review_attachments.product_review_id',
                                                'path as general_path',
                                                'product_reviews.name',
                                                DB::raw("CONCAT('Review attachment by: ', product_reviews.name) as alt"),
                                                DB::raw("CASE type
                                    WHEN {$attachmentTypes['video']} THEN 'video'
                                    WHEN {$attachmentTypes['image']} THEN 'image'
                                    END as type")
                                            ])
                                                ->join('product_reviews', 'product_reviews.id', '=', 'product_review_attachments.product_review_id')
                                                ->orderBy('priority', 'ASC');
                                        }
                                    ])
                                    ->leftJoin('product_review_translations', function ($join) use ($languageId) {
                                        $join->on('product_review_translations.product_review_id', '=', 'product_reviews.id')
                                            ->where('product_review_translations.language_id', $languageId);
                                    })
                                    ->orderBy('created_at', 'desc')
                                    ->limit(ProductConstants::REVIEWS_LIMIT_FOR_RENDERING);
                            },
                            'review_statistics' => function ($query) use ($languageId) {
                                $query->selectRaw('
                            product_id,
                            AVG(rating) as avg_rating,
                            COUNT(*) as total_reviews,
                            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1_count,
                            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2_count,
                            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3_count,
                            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4_count,
                            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5_count
                        ')
                                    ->where('status', true)
                                    ->groupBy('product_id');
                            },
                        ]);
                },
                'product_gift_cards' => function ($query) use ($rate, $baseCurrencyCode) {
                    $query->select('product_id', 'price')
                        ->join('currencies', 'product_gift_prices.currency_id', 'currencies.id')
                        ->where('currencies.code', $baseCurrencyCode)
                        ->orderBy('price', 'asc');
                }
            ])
            ->first();
    }

    public function fetchPathForChangeLanguage(int $languageId, int $id)
    {
        return $this->model->select('product_translations.path')
            ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.status', true)
            ->where('product_translations.translation_status', true)
            ->where('extra_product', false)
            ->where('product_translations.language_id', $languageId)
            ->where('products.id', $id)
            ->value('path');
    }

    public function fetchForCategoryBox(SupportCollection $categoryIds, int $languageId, float $rate, int $customerUserGroupId, ?int $productLimit = 12)
    {
        return $this->model->select('products.id', 'path', 'name', 'sub_name', 'type', 'related_reviewer_id', 'new')
            ->join('product_translations', 'product_translations.product_id', 'products.id')
            ->where('language_id', $languageId)
            ->where('extra_product', false)
            ->where('product_translations.translation_status', true)
            ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                $q->whereNull('real_product_id');
            })
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->whereHas('product_variant_main', function ($query) {
                $query->select('id', 'product_id')
                    ->where('status', true)
                    ->where('stock_status', true);
            })
            ->with([
                'review_statistics' => function ($query) use ($languageId) {
                    $query->selectRaw('
                            product_id,
                            AVG(rating) as avg_rating,
                            COUNT(*) as total_reviews
                        ')
                        ->where('status', true)
                        ->groupBy('product_id');
                },
                'product_variant_main' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                        DB::raw("sales_price * {$rate} as sales_price"), 'media_id')
                        ->with([
                            'media' => function ($query) use ($languageId) {
                                $query->select('media.id', 'original_path AS path', 'alt', 'path as general_path', 'width', 'height')
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
                        });
                },
                'related_reviewer_product' => function ($query) use ($languageId) {
                    $query->select('id')
                        ->with([
                            'review_statistics' => function ($query) use ($languageId) {
                                $query->selectRaw('
                                        product_id,
                                        AVG(rating) as avg_rating,
                                        COUNT(*) as total_reviews
                                    ')
                                    ->where('status', true)
                                    ->groupBy('product_id');
                            },
                        ]);
                }
            ])
            ->limit($productLimit)
            ->get();
    }

    public function fetchForCatalog(
        $categoryIds, int $languageId, int $limit, float $rate, array $queryParams, $categorySwoingType, array $attributeIds, int $customerUserGroupId
    )
    {
        $productTypeConstants = ProductConstants::TYPES;

        return self::fetchForCatalogQuery($categoryIds, $languageId, $limit, $rate, $queryParams, $attributeIds, $customerUserGroupId)
            ->select('products.id', 'path', 'name', 'sub_name', 'short_description', 'new',
                DB::raw("CASE products.type
                    WHEN {$productTypeConstants['simple']} THEN 'simple'
                    WHEN {$productTypeConstants['variable']} THEN 'variable'
                    WHEN {$productTypeConstants['bundle']} THEN 'bundle'
                    WHEN {$productTypeConstants['gift_card']} THEN 'gift_card'
                    END as type"), 'meta_description', 'related_reviewer_id',
                'products.updated_at', 'products.created_at')
            ->with([
                'review_statistics' => function ($query) use ($languageId) {
                    $query->selectRaw('
                            product_id,
                            AVG(rating) as avg_rating,
                            COUNT(*) as total_reviews
                        ')
                        ->where('status', true)
                        ->groupBy('product_id');
                },
                'product_variant_main' => function ($query) use ($rate, $languageId) {
                    $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                        DB::raw("sales_price * {$rate} as sales_price"), 'sku', 'media_id', 'stock_status')
                        ->with([
                            'media' => function ($query) use ($languageId) {
                                $query->select('media.id', 'original_path AS path', 'alt', 'path as general_path', 'width', 'height')
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
                        });
                },
                'product_variations' => function ($query) use ($rate) {
                    $query->selectRaw("
                        product_id,
                        MIN(LEAST(COALESCE(regular_price, 0), COALESCE(sales_price, regular_price))) * {$rate} as min_price,
                        MAX(GREATEST(COALESCE(regular_price, 0), COALESCE(sales_price, regular_price))) * {$rate} as max_price,
                        COUNT(*) as variant_count
                    ")
                        ->whereNotNull('regular_price')
                        ->whereNotNull('sku')
                        ->groupBy('product_id');
                },
                'related_reviewer_product' => function ($query) use ($languageId) {
                    $query->select('id')
                        ->with([
                            'review_statistics' => function ($query) use ($languageId) {
                                $query->selectRaw('
                            product_id,
                            AVG(rating) as avg_rating,
                            COUNT(*) as total_reviews
                        ')
                                    ->where('status', true)
                                    ->groupBy('product_id');
                            },
                        ]);
                },
            ])
            ->when($categorySwoingType === 3, function ($query) use ($languageId) {
                $query->with([
                    'attributes' => function ($query) use ($languageId) {
                        $query->select('attributes.id', 'value', 'attribute_type_id', 'slug', 'attributes.priority')
                            ->join('attribute_translations', 'attributes.id', '=', 'attribute_translations.attribute_id')
                            ->where('attribute_translations.language_id', $languageId)
                            ->with([
                                'attribute_type' => function ($query) use ($languageId) {
                                    $query->select('attribute_types.id', 'attribute_type_translations.name', 'attribute_type_translations.label', 'attribute_type_translations.slug')
                                        ->join('attribute_type_translations', 'attribute_type_translations.attribute_type_id', '=', 'attribute_types.id')
                                        ->where('attribute_type_translations.language_id', $languageId);
                                }
                            ])
                            ->whereHas('attribute_type', function ($query) use ($languageId) {
                                $query->where('logic', AttributeConstants::ATTRIBUTE_LOGIC['variation']);
                            })
                            ->orderBy('attributes.priority', 'asc')
                            ->orderBy('attributes.id', 'asc');
                    },
                    'categories' => function ($query) use ($languageId) {
                        $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                            ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                            ->where('category_translations.language_id', $languageId);
                    },
                    'primary_category_pivot' => function ($query) use ($languageId) {
                        $query->select('categories.id', 'category_translations.name', 'path')
                            ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                            ->where('category_translations.language_id', $languageId)
                            ->with([
                                'category_translation' => function ($translationQuery) use ($languageId) {
                                    $translationQuery->select('category_id', 'related_category_translation_id')
                                        ->with([
                                            'related' => function ($query) use ($languageId) {
                                                $query->select('id', 'category_id');
                                            }
                                        ]);
                                }
                            ]);
                    }
                ]);
            })
            ->when(isset($queryParams['sort']), function ($query) use ($queryParams) {
                $query->orderByRaw($queryParams['sort']);

                if ('priority asc' === $queryParams['sort']) {
                    $query->orderBy('products.id', 'asc');
                }
            })
            ->when(!isset($queryParams['sort']), function ($query) use ($queryParams) {
                $query->orderByRaw('priority asc')
                    ->orderBy('products.id', 'asc');
            })
            ->limit($limit)
            ->get();
    }

    public function fetchForCatalogCount($categoryIds, int $languageId, int $limit, float $rate, array $queryParams, array $attributeIds, int $customerUserGroupId)
    {
        return self::fetchForCatalogQuery($categoryIds, $languageId, $limit, $rate, $queryParams, $attributeIds, $customerUserGroupId)->count();
    }

    public function fetchForCatalogQuery($categoryIds, int $languageId, int $limit, float $rate, array $queryParams, array $attributeIds, int $customerUserGroupId)
    {
        return $this->model
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
            ->join('product_variants', function ($join) {
                $join->on('product_variants.product_id', '=', 'products.id')
                    ->where('product_variants.is_main', true)
                    ->where('product_variants.status', true);
            })
            ->where('product_translations.language_id', $languageId)
            ->where('product_translations.translation_status', true)
            ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                $q->whereNull('real_product_id');
            })
            ->where('extra_product', false)
            ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                $query->join('product_categories', 'products.id', '=', 'product_categories.product_id')
                    ->whereIn('product_categories.category_id', $categoryIds);
            })
            ->when(isset($queryParams['price']), function ($query) use ($queryParams, $rate) {
                $query->whereRaw("COALESCE(sales_price, regular_price) * ? BETWEEN ? AND ?", array_merge([$rate], $queryParams['price']));
            })
            ->when(isset($queryParams['query']), function ($query) use ($queryParams) {
                $query->where('product_translations.name', 'LIKE', "%" . addslashes($queryParams['query']) . "%");
            });
//            ->when(!empty($attributeIds), function ($query) use ($attributeIds) {
//                $query->join('product_attributes', 'products.id', '=', 'product_attributes.product_id');
//                foreach ($attributeIds as $attributeId) {
//                    $query->where('product_attributes.attribute_id', $attributeId);
//                }
//            });
    }

    public function autocompleteForFront(int $languageId, string $searchTerm)
    {
        $searchTerm = addslashes($searchTerm);

        return $this->model->select('products.id', 'path', 'name')
            ->join('product_translations', 'product_translations.product_id', 'products.id')
            ->where('product_translations.translation_status', true)
            ->where('language_id', $languageId)
            ->where('extra_product', false)
            ->where(function ($query) use ($searchTerm) {
                $query->orWhereHas('product_translation', function ($query) use ($searchTerm) {
                    $query->select('product_id')->where('name', 'LIKE', "%{$searchTerm}%");
                })->orWhereHas('attributes_pivot', function ($query) use ($searchTerm) {
                    $query->join('attribute_translations', 'product_attributes.attribute_id', '=', 'attribute_translations.attribute_id')
                        ->where('attribute_translations.value', 'LIKE', "%{$searchTerm}%");
                });
            })
            ->limit(10)
            ->orderBy('priority', 'ASC')
            ->get();
    }

    public function fetchForRelatedProductByCategory(int $categoryId, int $languageId, float $rate, int $currentProductId, int $customerUserGroupId)
    {
        $productTypeConstants = ProductConstants::TYPES;
        return $this->model->select('products.id', 'name', 'sub_name', 'path', 'new', 'related_reviewer_id', DB::raw("CASE products.type
                    WHEN {$productTypeConstants['simple']} THEN 'simple'
                    WHEN {$productTypeConstants['variable']} THEN 'variable'
                    WHEN {$productTypeConstants['bundle']} THEN 'bundle'
                    WHEN {$productTypeConstants['gift_card']} THEN 'gift_card'
                    END as type"))
            ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
            ->where('product_translations.language_id', $languageId)
            ->where('product_translations.translation_status', true)
            ->when(!Auth::user() || $customerUserGroupId === Auth::user()->user_group_id, function ($q) {
                $q->whereNull('real_product_id');
            })
            ->with([
                'product_variant_main' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                        DB::raw("sales_price * {$rate} as sales_price"), 'media_id')
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
                        ]);
                },
                'review_statistics' => function ($query) use ($languageId) {
                    $query->selectRaw('
                        product_id,
                        AVG(rating) as avg_rating,
                        COUNT(*) as total_reviews
                        ')
                        ->where('status', true)
                        ->groupBy('product_id');
                },
                'related_reviewer_product' => function ($query) use ($languageId) {
                    $query->select('id')
                        ->with([
                            'review_statistics' => function ($query) use ($languageId) {
                                $query->selectRaw('
                        product_id,
                        AVG(rating) as avg_rating,
                        COUNT(*) as total_reviews
                        ')
                                    ->where('status', true)
                                    ->groupBy('product_id');
                            },
                        ]);
                }
            ])
            ->whereHas('product_variant_main', function ($query) {
                $query->where('status', true)
                    ->where('stock_status', true);
            })
            ->whereHas('categories_pivot', function ($query) use ($categoryId, $currentProductId) {
                $query->where('category_id', $categoryId)
                    ->where('product_id', '!=', $currentProductId);
            })
            ->where('extra_product', false)
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    public function feed(string $select, array $params, array $joins)
    {
        $categoryIds = $params['category_ids'] ?? null;
        return $this->model->select(DB::raw($select))
            ->when(!empty($joins), function ($joinQuery) use ($joins) {
                foreach ($joins as $join) {
                    if (!empty($join[4]) && $join[4] === 'leftJoin') {
                        $joinQuery->leftJoin($join[0], $join[1], $join[2], $join[3]);
                    } else {
                        $joinQuery->join($join[0], $join[1], $join[2], $join[3]);
                    }
                }
            })
            ->where('extra_product', false)
            ->where('product_variants.is_main', true)
            ->whereHas('categories_pivot', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->whereNull('real_product_id')
            ->whereHas('product_translation', function ($query) use ($params) {
                $languageId = $params['base_language_id'];
                $query->where('language_id', $languageId)
                        ->where('translation_status', true);
            })
            ->with([
                'product_translation' => function ($translationQuery) use ($params) {
                    $languageId = $params['base_language_id'];
                    $translationQuery->select('id', 'product_id', 'name', 'sub_name', 'short_description', 'path')
                        ->where('language_id', $languageId);
                },
                'primary_category_pivot' => function ($categoryQuery) use ($params) {
                    $categoryQuery->with(['category_translation' => function ($translationCategoryQuery) use ($params) {
                        $languageId = $params['base_language_id'];
                        $translationCategoryQuery->select('category_id', 'language_id', 'name')
                            ->where('language_id', $languageId);
                    }]);
                },
                'product_variant_main' => function ($query) use ($params) {
                    $query->with([
                        'product_variant_custom_field_translation' => function ($query) use ($params) {
                            $languageId = $params['base_language_id'];
                            $query->select('product_variant_id', 'key', 'value'
                            )->where('language_id', $languageId);
                        },
                        'gallery' => function ($galleryQuery) {
                            $galleryQuery->join('media', 'product_variant_galleries.media_id', '=', 'media.id')
                                ->select([
                                    'product_variant_galleries.media_id',
                                    'product_variant_galleries.product_variant_id',
                                    'media.type',
                                    'media.original_path AS path'
                                ]);
                        },
                        'media' => function ($imageQuery) {
                            $imageQuery->select([
                                'id', 'type', 'original_path AS path'
                            ]);
                        },
                        'product_variant_translation' => function ($q) use ($params) {
                            $languageId = $params['base_language_id'];
                            $q->select('id', 'product_variant_id', 'media_id')
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
                                        ])->leftJoin('media_translations', function ($join) use ($languageId) {
                                            $join->on('media_translations.media_id', '=', 'media.id')
                                                ->where('media_translations.language_id', $languageId);
                                        });
                                    },
                                ]);
                        }
                    ]);
                },
            ])->orderBy('priority', 'asc');
    }

    public function productListForExcel(string $select, array $params, array $joins)
    {
        return $this->model->select(DB::raw($select))
            ->when(!empty($joins), function ($joinQuery) use ($joins) {
                foreach ($joins as $join) {
                    if (!empty($join[4]) && $join[4] === 'leftJoin') {
                        $joinQuery->leftJoin($join[0], $join[1], $join[2], $join[3]);
                    } else {
                        $joinQuery->join($join[0], $join[1], $join[2], $join[3]);
                    }
                }
            })
            ->whereNull('real_product_id')
            ->where('product_variants.status', true)
            ->where('extra_product', false)
            ->where('product_variants.is_main', true)
            ->whereHas('product_translation', function ($query) use ($params) {
                $languageId = $params['language_id'];
                $query
                    ->where('language_id', $languageId)
                    ->where('translation_status', true);
            })
            ->with([
                'product_translation' => function ($translationQuery) use ($params) {
                    $languageId = $params['language_id'];
                    $translationQuery->select('id', 'product_id', 'name', 'sub_name', 'short_description', 'path')
                        ->where('language_id', $languageId);
                },
                'product_variant_main' => function ($query) use ($params) {
                    $query->with([
                        'product_variant_custom_field_translation' => function ($query) use ($params) {
                            $languageId = $params['language_id'];
                            $query->select('product_variant_id', 'key', 'value'
                            )->where('language_id', $languageId);
                        },

                        'product_variant_translation' => function ($q) use ($params) {
                            $languageId = $params['language_id'];
                            $q->select('id', 'product_variant_id', 'media_id')
                                ->where('language_id', $languageId);
                        }
                    ]);
                },
            ])->orderBy('priority', 'asc');
    }

    public function fetchForClone(int $orderId)
    {
        return $this->model
            ->where('id', $orderId)
            ->with([
                'product_variations_all' => function ($query) {
                    $query->with([
                        'product_variant_attributes', 'product_variant_custom_field_translation', 'gallery', 'product_variant_parents', 'product_variant_prices',
                        'product_variant_translations' => function ($q) {
                            $q->with(['gallery']);
                        }
                    ]);
                },
                'product_translations',
                'categories_pivot',
                'linked_products_pivot',
                'attributes_pivot',
                'product_gift_cards',
                'product_multiselect' => function ($query) {
                    $query->with([
                        'product_multiselect_translations',
                        'product_multiselect_options' => function ($query) {
                            $query->with(['product_multiselect_option_parents', 'product_multiselect_option_translations']);
                        }
                    ]);
                }
            ])
            ->first();
    }

    public function fetchInfoForPurchaseSendToAnyTrack(int $productId, int $languageId)
    {
        return $this->model->select('products.id', 'product_translations.short_description')
            ->where('products.id', $productId)
            ->leftJoin('product_translations', function ($join) use ($languageId) {
                $join->on('product_translations.product_id', '=', 'products.id')
                    ->where('product_translations.language_id', $languageId)
                    ->where('product_translations.translation_status', true);
            })
            ->with([
                'categories' => function ($query) use ($languageId) {
                    $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                        ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                        ->where('category_translations.language_id', $languageId);
                }
            ])
            ->first()
            ->toArray();
    }

    public function fetchVariableProductsForPriceUpdate(?array $productIds)
    {
        return $this->model->select('id')
            ->where('type', ProductConstants::TYPES['variable'])
            ->when(!empty($productIds), function ($query) use ($productIds) {
                $query->whereIn('id', $productIds);
            })
            ->with([
                'product_variant_main' => function ($query) {
                    $query->select('id', 'product_id');
                }
            ])
            ->get();
    }

    public function getSlugByIdAndLanguageId(int $productId, int $languageId)
    {
        return $this->model->select('slug')
            ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
            ->where('products.id', $productId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function fetchForAnalytics(array $data, array $billingCountryCodes, array $shippingCountryCodes, string $dateType, int $languageId)
    {
        // Subquery: Aggregate parent_quantity per order_item_id
        $parentQuantitySub = DB::table('order_item_parents')
            ->selectRaw('order_item_id, SUM(quantity) as parent_quantity')
            ->when(!empty($data['item_id']) && $data['item_id'] > -1, function ($query) use ($data) {
                $query->where('item_id', $data['item_id']);
            })
            ->groupBy('order_item_id');

        return $this->model->withTrashed()->selectRaw(
            'product_translations.name as product_name,
        product_variants.sku as product_sku,
        COALESCE(order_stats.total_subtotal, 0) AS total_subtotal,
        COALESCE(order_stats.total_revenue, 0) AS total_revenue,
        COALESCE(order_stats.total_sold, 0) AS total_sold,
        COALESCE(order_stats.parent_quantity, 0) AS parent_quantity'
        )
            ->join('product_variants', function ($join) {
                $join->on('products.id', '=', 'product_variants.product_id')
                    ->where('product_variants.is_main', true);
            })
            ->join('product_translations', function ($join) use ($languageId) {
                $join->on('products.id', '=', 'product_translations.product_id')
                    ->where('product_translations.language_id', $languageId);
            })
            ->when(!empty($data['category_ids']), function ($q) use ($data) {
                $q->join('product_categories', 'products.id', '=', 'product_categories.product_id')
                    ->whereIn('product_categories.category_id', $data['category_ids']);
            })
            ->leftJoinSub(
                DB::table('order_items')
                    ->selectRaw(
                        'order_items.product_id,
                    SUM(order_items.subtotal / orders.order_currency_rate) AS total_subtotal,
                    SUM(order_items.total / orders.order_currency_rate) AS total_revenue,
                    SUM(order_items.quantity) AS total_sold,
                    COALESCE(SUM(pq.parent_quantity), 0) AS parent_quantity'
                    )
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('order_billing_addresses', 'orders.id', '=', 'order_billing_addresses.order_id')
                    ->join('order_shipping_addresses', 'orders.id', '=', 'order_shipping_addresses.order_id')
                    ->leftJoinSub($parentQuantitySub, 'pq', 'pq.order_item_id', '=', 'order_items.id')
                    ->whereIn('orders.status', [OrderConstants::STATUS_PROCESSING, OrderConstants::STATUS_COMPLETED])
                    ->whereNull('orders.full_reshipment')
                    ->whereBetween('orders.created_at', [
                        $data["{$dateType}order_date_from"],
                        to_date_at_the_end($data["{$dateType}order_date_to"])
                    ])
                    ->when(!empty($billingCountryCodes), function ($query) use ($billingCountryCodes) {
                        $query->whereIn('order_billing_addresses.country_code', $billingCountryCodes);
                    })
                    ->when(!empty($shippingCountryCodes), function ($query) use ($shippingCountryCodes) {
                        $query->whereIn('order_shipping_addresses.country_code', $shippingCountryCodes);
                    })
                    ->groupBy('order_items.product_id'),
                'order_stats',
                'products.id',
                '=',
                'order_stats.product_id'
            )
            ->orderBy('total_sold', 'desc')
            ->get()
            ->keyBy('product_sku');
    }

    public function recover(int $id)
    {
        return $this->model->onlyTrashed()->find($id)->restore();
    }

    public function fetchForLLM(int $languageId, int $product_id, float $rate)
    {
        return $this->model->select('id', 'type')
            ->whereHas('product_variant_main', function ($query) {
                $query->where('status', true);
            })
            ->where('extra_product', false)
            ->whereHas('product_translation', function ($query) use ($product_id, $languageId) {
                $query->where('product_id', $product_id)->where('language_id', $languageId)->where('translation_status', true);
            })
            ->with([
                'product_multiselect' => function ($multiselectQuery) use ($languageId, $rate) {
                    $multiselectQuery->select('product_multiselects.id as id', 'product_id', 'options_limit', 'title', 'description')
                        ->leftJoin('product_multiselect_translations', function ($join) use ($languageId) {
                            $join->on('product_multiselect_translations.product_multiselect_id', '=', 'product_multiselects.id')
                                ->where('product_multiselect_translations.language_id', $languageId);
                        })
                        ->with([
                            'product_multiselect_options' => function ($multiselectQuery) use ($languageId, $rate) {
                                $multiselectQuery->select('product_multiselect_options.id', 'product_multiselect_id', 'media_id', DB::raw("additional_price * {$rate} as additional_price"), 'title')
                                    ->leftJoin('product_multiselect_option_translations', function ($join) use ($languageId) {
                                        $join->on('product_multiselect_option_translations.product_multiselect_option_id', '=', 'product_multiselect_options.id')
                                            ->where('product_multiselect_option_translations.language_id', $languageId);
                                    })
                                    ->with([
                                        'media' => function ($imageQuery) use ($languageId) {
                                            $imageQuery->select([
                                                'media.id', 'original_path AS path', 'width', 'height', 'alt', 'path as general_path'
                                            ])->leftJoin('media_translations', function ($join) use ($languageId) {
                                                $join->on('media_translations.media_id', '=', 'media.id')
                                                    ->where('media_translations.language_id', $languageId);
                                            });
                                        },
                                    ]);
                            }
                        ]);
                },
                'product_variant_main' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'id as product_variant_id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                        DB::raw("sales_price * {$rate} as sales_price"), 'tax_status', 'media_id',
                        'stock_status', 'sku')
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
                        ]);
                },
                'product_translation' => function ($translationQuery) use ($languageId, $rate) {
                    $translationQuery->select('product_id', 'name', 'path',
                        'sub_name', 'short_description', 'meta_title', 'meta_keywords', 'meta_description', 'snippet_id')
                        ->with([
                            'snippet' => function ($query) use ($languageId, $rate) {
                                $query->select('page_translations.id', 'name', 'button_text')
                                    ->join('pages', 'pages.id', '=', 'page_translations.page_id')
                                    ->where('pages.status', true);

                                return $this->builderElementsQuery($query, $languageId, $rate);
                            },
                        ])
                        ->where('language_id', $languageId)
                        ->where('product_translations.translation_status', true);
                },
                'categories' => function ($query) use ($languageId) {
                    $query->select('categories.id as term_id', 'category_translations.name', 'category_translations.slug')
                        ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                        ->where('category_translations.language_id', $languageId);
                },
            ])
            ->first();
    }

    public function getAllIdsForLLM(): SupportCollection
    {
        return $this->model->select('id')
            ->whereHas('product_variant_main', function ($query) {
                $query->where('status', true);
            })
            ->where('extra_product', false)
            ->pluck('id');
    }

    public function getForNotifyAboutOutOfStocks(int $languageId)
    {
        return $this->model->select('products.id', 'path', 'sku', 'name')
            ->join('product_translations', 'product_translations.product_id', 'products.id')
            ->join('product_variants', 'product_variants.product_id', 'products.id')
            ->where('product_variants.is_main', true)
            ->where('product_variants.stock_status', false)
            ->where('language_id', $languageId)
            ->get();
    }

    public function getForNotifyAboutOutOfStocksVariants(int $languageId)
    {
        return $this->model->select('products.id', 'path', 'name', 'sku')
            ->where('type', ProductConstants::TYPES['variable'])
            ->join('product_translations', 'product_translations.product_id', 'products.id')
            ->join('product_variants', 'product_variants.product_id', 'products.id')
            ->where('product_variants.is_main', true)
            ->where('language_id', $languageId)
            ->whereHas('product_variations', function ($q) {
                $q->where('is_main', false)
                    ->where('stock_status', false);
            })
            ->get();
    }
}
