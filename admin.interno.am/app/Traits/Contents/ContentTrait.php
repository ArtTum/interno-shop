<?php

namespace App\Traits\Contents;

use App\Constants\ProductConstants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ContentTrait
{
    // query for snippets and a plus content
    public function builderElementsQuery($query, int $languageId, float $rate)
    {
        return $query->with([
            'sections' => function ($query) use ($languageId, $rate) {
                return $this->sectionQuery($query, $languageId, $rate);
            }
        ])->whereHas('page', function ($query) {
            $query->select('id')->where('status', true);
        });
    }

    public function sectionQuery($query, int $languageId, float $rate)
    {
        return $query->select('id', 'page_translation_id', 'responsive_settings')
            ->where('status', true)
            ->with([
                'columns' => function ($query) use ($languageId, $rate) {
                    $query->select('id', 'parent_id', 'page_translation_id', 'responsive_settings')
                        ->with([
                            'components' => function ($query) use ($languageId, $rate) {
                                $query->select('page_section_components.id', 'page_section_id', 'config', 'component_name', 'component_key')
                                    ->join('components', 'components.id', '=', 'page_section_components.component_id')
                                    ->with([
                                        'items' => function ($query) use ($languageId, $rate) {
                                            $query->select(
                                                'page_section_component_id', 'category_translation_id', 'product_translation_id',
                                                'post_category_translation_id', 'page_translation_id', 'media_id', 'calculator_translation_id', 'config'
                                            )
                                                ->with([
                                                    'category_translation' => function ($query) use ($languageId) {
                                                        $query->select('id', 'path', 'name', 'category_id')
                                                            ->with([
                                                                'category' => function ($query) use ($languageId) {
                                                                    $query->select('id', 'media_id')
                                                                        ->with([
                                                                            'media' => function ($query) use ($languageId) {
                                                                                $query->select([
                                                                                    'media.id', 'original_path AS path', 'alt', 'path as general_path', 'width', 'height'
                                                                                ])
                                                                                    ->leftJoin('media_translations', function ($join) use ($languageId) {
                                                                                        $join->on('media_translations.media_id', '=', 'media.id')
                                                                                            ->where('media_translations.language_id', $languageId);
                                                                                    });
                                                                            }
                                                                        ]);
                                                                }
                                                            ]);
                                                    },
                                                    'product_translation' => function ($query) use ($languageId, $rate) {
                                                        $query->select('id', 'product_id', 'path', 'name', 'sub_name')
                                                            ->with([
                                                                // todo look on query for category boxes, there have done already res of query
                                                                'product' => function ($query) use ($languageId, $rate) {
                                                                    $productTypeConstants = ProductConstants::TYPES;
                                                                    $query->select('id', 'related_reviewer_id', 'new', DB::raw("CASE products.type
                                                                        WHEN {$productTypeConstants['simple']} THEN 'simple'
                                                                        WHEN {$productTypeConstants['variable']} THEN 'variable'
                                                                        WHEN {$productTypeConstants['bundle']} THEN 'bundle'
                                                                        WHEN {$productTypeConstants['gift_card']} THEN 'gift_card'
                                                                        END as type"))
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
                                                                            'product_variant_main' => function ($query) use ($languageId, $rate) {
                                                                                $query->select('id', 'product_id', DB::raw("regular_price * {$rate} as regular_price"),
                                                                                    DB::raw("sales_price * {$rate} as sales_price"), 'media_id')
                                                                                    ->with([
                                                                                        'media' => function ($query) use ($languageId) {
                                                                                            $query->select([
                                                                                                'media.id', 'original_path AS path', 'alt', 'path as general_path', 'width', 'height'
                                                                                            ])->leftJoin('media_translations', function ($join) use ($languageId) {
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
                                                                            }
                                                                        ]);
                                                                }
                                                            ]);
                                                    },
                                                    'post_category_translation' => function ($query) {
                                                        $query->select('id', 'name', 'slug');
                                                    },
                                                    'page_translation' => function ($query) {
                                                        $query->select('id', 'name', 'subname', 'path');
                                                    },
                                                    'calculator_translation' => function ($query) {
                                                        $query->select('id', 'name', 'config');
                                                    },
                                                    'media' => function ($query) use ($languageId) {
                                                        $query->select([
                                                            'media.id', 'type', 'original_path AS path', 'alt', 'path as general_path', 'width', 'height'
                                                        ])
                                                            ->leftJoin('media_translations', function ($join) use ($languageId) {
                                                                $join->on('media_translations.media_id', '=', 'media.id')
                                                                    ->where('media_translations.language_id', $languageId);
                                                            });
                                                    }
                                                ])
                                                ->orderBy('priority', 'asc');
                                        }
                                    ])->orderBy('priority', 'asc');
                            }
                        ])->orderBy('priority', 'asc');
                }
            ])->orderBy('priority', 'asc');
    }
}
