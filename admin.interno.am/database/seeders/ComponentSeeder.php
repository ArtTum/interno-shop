<?php

namespace Database\Seeders;

use App\Models\Component;
use Illuminate\Database\Seeder;

class ComponentSeeder extends Seeder
{
    const COMPONENTS_ARRAY = [
        [
            'name' => 'Slider (1)',
            'layout_image' => '/components/slider-1.png',
            'component_key' => 'slider_first',
            'component_name' => 'SliderComponentFirst',
        ],
        [
            'name' => 'Custom links (1)',
            'layout_image' => '/components/custom-links-1.png',
            'component_key' => 'custom_links_component_first',
            'component_name' => 'CustomLinksComponentFirst',
        ],
        [
            'name' => 'Custom links (2)',
            'layout_image' => '/components/custom-links-2.png',
            'component_key' => 'custom_links_component_second',
            'component_name' => 'CustomLinksComponentSecond',
        ],
        [
            'name' => 'Relation (1)',
            'layout_image' => '/components/relation-1.png',
            'component_key' => 'relation_component_first',
            'component_name' => 'RelationComponentFirst',
        ],
//        [
//            'name' => 'Relation (2)',
//            'layout_image' => '/components/slider.png',
//            'component_key' => 'relation_component_second',
//            'component_name' => 'RelationComponentSecond',
//        ],
//        [
//            'name' => 'Relation (3)',
//            'layout_image' => '/components/slider.png',
//            'component_key' => 'relation_component_third',
//            'component_name' => 'RelationComponentThird',
//        ],
        [
            'name' => 'Products by categories',
            'layout_image' => '/components/products-by-categories.png',
            'component_key' => 'products_by_categories_component',
            'component_name' => 'ProductsByCategoriesComponent',
        ],
        [
            'name' => 'FAQ',
            'layout_image' => '/components/faq.png',
            'component_key' => 'faq_component',
            'component_name' => 'FAQComponent',
        ],
        [
            'name' => 'Gallery',
            'layout_image' => '/components/gallery.png',
            'component_key' => 'gallery_component',
            'component_name' => 'GalleryComponent',
        ],
        [
            'name' => 'Video',
            'layout_image' => '/components/video.png',
            'component_key' => 'video_component',
            'component_name' => 'VideoComponent',
        ],
        [
            'name' => 'Employee',
            'layout_image' => '/components/employee.png',
            'component_key' => 'employee_component',
            'component_name' => 'EmployeeComponent',
        ],
        [
            'name' => 'Image',
            'layout_image' => '/components/image.png',
            'component_key' => 'image_component',
            'component_name' => 'ImageComponent',
        ],
        [
            'name' => 'Event',
            'layout_image' => '/components/event.png',
            'component_key' => 'event_component',
            'component_name' => 'EventComponent',
        ],
        [
            'name' => 'Editor',
            'layout_image' => '/components/editor.png',
            'component_key' => 'editor_component',
            'component_name' => 'EditorComponent',
        ],
        [
            'name' => 'Offer',
            'layout_image' => '/components/offer.png',
            'component_key' => 'offer_component',
            'component_name' => 'OfferComponent',
        ],
        [
            'name' => 'Form',
            'layout_image' => '/components/form.png',
            'component_key' => 'form_component',
            'component_name' => 'FormComponent',
        ],
        [
            'name' => 'Blogs',
            'layout_image' => '/components/blog.png',
            'component_key' => 'blogs_component',
            'component_name' => 'BlogsComponent',
        ],
        [
            'name' => 'A + content',
            'layout_image' => '/components/a-plus.png',
            'component_key' => 'a_plus_content_component',
            'component_name' => 'APlusContentComponent',
        ],
        [
            'name' => 'Calculator',
            'layout_image' => '/components/calculator.png',
            'component_key' => 'calculator_component',
            'component_name' => 'CalculatorComponent',
        ],
        [
            'name' => 'Testimonials',
            'layout_image' => '/components/testimonial.png',
            'component_key' => 'testimonials_component',
            'component_name' => 'TestimonialsComponent',
        ],
        [
            'name' => 'Result numbers',
            'layout_image' => '/components/result-numbers.png',
            'component_key' => 'result_numbers_component',
            'component_name' => 'ResultNumbersComponent',
        ],
        [
            'name' => 'Trustpilot component',
            'layout_image' => '/components/trustpilot.png',
            'component_key' => 'trustpilot_component',
            'component_name' => 'TrustpilotComponent',
        ],
        [
            'name' => 'USP list component',
            'layout_image' => '/components/usp-list.png',
            'component_key' => 'usp_list_component',
            'component_name' => 'USPListComponent'
        ],
        [
            'name' => 'B2B quick cart',
            'layout_image' => '/components/usp-list.png',
            'component_key' => 'b2b_quick_cart',
            'component_name' => 'B2BQuickCart'
        ],
        [
            'name' => 'Button component',
            'layout_image' => '/components/button.png',
            'component_key' => 'builder_button_component',
            'component_name' => 'BuilderButtonComponent'
        ],
        [
            'name' => 'Lead Form',
            'layout_image' => '/components/lead-form.png',
            'component_key' => 'lead_form_component',
            'component_name' => 'LeadFormComponent',
        ],
        [
            'name' => 'Bullet points',
            'layout_image' => '/components/bullet-points.png',
            'component_key' => 'bullet_points_component',
            'component_name' => 'BulletPointsComponent',
        ],
        [
            'name' => 'All products table',
            'layout_image' => '/components/all-products.png',
            'component_key' => 'all_products_component',
            'component_name' => 'AllProductsComponent',
        ],
        [
            'name' => 'Newsletter component',
            'layout_image' => '/components/newsletter.png',
            'component_key' => 'newsletter_component',
            'component_name' => 'NewsletterComponent',
        ],
        [
            'name' => 'Invoice Request Form',
            'layout_image' => '/components/invoice-request-form.jpg',
            'component_key' => 'invoice_request_form_component',
            'component_name' => 'InvoiceRequestFormComponent',
        ],
        [
            'name' => 'Video slider',
            'layout_image' => '/components/video.png',
            'component_key' => 'video_slider_component',
            'component_name' => 'VideoSliderComponent',
        ],
        [
            'name' => 'Tracking form',
            'layout_image' => '/components/tracking-form.png',
            'component_key' => 'tracking_form_component',
            'component_name' => 'TrackingFormComponent',
        ],
    ];

    public function run(): void
    {
        foreach (self::COMPONENTS_ARRAY as $component) {
            Component::updateOrCreate(
                [
                    'component_key' => $component['component_key'],
                ],
                [
                    'name' => $component['name'],
                    'layout_image' => $component['layout_image'],
                    'component_name' => $component['component_name'],
                ]
            );
        }
    }
}
