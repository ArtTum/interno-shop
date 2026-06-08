<?php

if (!function_exists('generate_snippet_shortcode')) {
    function generate_snippet_shortcode(string $name): string
    {
        return '[content="' . $name . '"]';
    }
}
