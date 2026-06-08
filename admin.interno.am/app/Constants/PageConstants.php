<?php

namespace App\Constants;

class PageConstants
{
    const SECTIONS_SHOWING_TYPES = [
        'Grid' => 0,
        'Horizontal scroll' => 1
    ];

    const FIELD_TYPES = [
        'text' => 'Regular text',
        'email' => 'Email',
        'number' => 'Number field',
        'decimal' => 'Decimal field',
        'date_picker' => 'Date picker',
        'time_picker' => 'Time picker',
        'upload_file' => 'File upload',
        'textarea' => 'Textarea',
        'dropdown' => 'Dropdown',
    ];

    const PAGE_TYPES = [
        'page' => 0,
        'post' => 1,
        'a_plus_content' => 2,
    ];

    const A_PLUS_CONTENT_TYPE = [
        'Description' => 0,
        'Checkout button' => 1,
        'Snippet' => 2
    ];

    const MARKETING_SOURCE_QUERY_KEYS = [
        'utm_source', 'utm_medium', 'utm_id', 'utm_campaign', 'ttclid', 'ad_id', 'utm_content', 'rdt_cid', 'adgroup_id', 'ad_name', 'adgroup_name', 'utm_term',
        'adset_id', 'placement', 'site_source_name', 'fbclid', 'gclid', 'creativeId', 'targetid', 'gad_source', 'ad_placement', 'ad_type', 'ad_site',
        'adgroupid', 'pp', 'epik', 'gbraid', 'srsltid', 'stars', 'vote', 's', 'review_id', 1, '1', 2, '2', 3, '3', 'site_source_', 'add-to-cart', 'gad_campaignid'
    ];

    const AI_TRANSLATE_JSON_EXCLUDE_KEYS = [
        'bg_color', 'bullet_color', 'button_url', 'calculator_translation_id', 'category_translation_id', 'classes', 'color', 'component_id', 'countries', 'date', 'desktop', 'desktop_per_raw', 'email', 'field_type', 'file_type', 'generate_contract', 'height', 'hide_buttons', 'id', 'image_path', 'images', 'make_as_full_width', 'media_id', 'mobile', 'mobile_per_raw', 'new_tab', 'open', 'page_section_component_id', 'page_section_id', 'page_translation_id', 'parent_id', 'path', 'phone', 'portrait_orientation', 'position', 'post_category_translation_id', 'price', 'primary', 'priority', 'product_translation_id', 'products_limit', 'projects', 'projectsData', 'receivers', 'required', 'section_showing_type_desktop', 'section_showing_type_mobile', 'send_to_visitor', 'size', 'space_columns', 'space_components_bottom', 'space_sections_bottom', 'space_sections_top', 'status', 'success_url', 'tablet', 'tablet_per_raw', 'type', 'url', 'use_as_name', 'version_light', 'vertical_alignment', 'video_type', 'video_url', 'without_all',
    ];
}
