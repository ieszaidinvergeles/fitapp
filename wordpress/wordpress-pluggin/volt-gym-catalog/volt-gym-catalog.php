<?php
/**
 * Plugin Name: Volt GYM Catalog
 * Plugin URI: https://voltgym.local
 * Description: Registers the Volt GYM custom post type used as a product catalog without eCommerce checkout.
 * Version: 0.1.0
 * Author: FitApp
 * Text Domain: volt-gym-catalog
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Volt_Gym_Catalog_Plugin
{
    public const POST_TYPE = 'volt_gym_product';
    public const TAXONOMY = 'volt_gym_category';

    public function boot(): void
    {
        add_action('init', [$this, 'register_catalog']);
        add_action('init', [$this, 'register_taxonomy']);
    }

    public function register_catalog(): void
    {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name' => __('Volt GYM Catalog', 'volt-gym-catalog'),
                'singular_name' => __('Volt GYM Product', 'volt-gym-catalog'),
                'add_new' => __('Add Product', 'volt-gym-catalog'),
                'add_new_item' => __('Add New Volt GYM Product', 'volt-gym-catalog'),
                'edit_item' => __('Edit Volt GYM Product', 'volt-gym-catalog'),
                'new_item' => __('New Volt GYM Product', 'volt-gym-catalog'),
                'view_item' => __('View Volt GYM Product', 'volt-gym-catalog'),
                'search_items' => __('Search Volt GYM Catalog', 'volt-gym-catalog'),
                'not_found' => __('No catalog products found.', 'volt-gym-catalog'),
                'menu_name' => __('Volt GYM', 'volt-gym-catalog'),
            ],
            'public' => true,
            'show_in_rest' => true,
            'has_archive' => true,
            'menu_position' => 21,
            'menu_icon' => 'dashicons-products',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
            'rewrite' => ['slug' => 'volt-gym'],
        ]);
    }

    public function register_taxonomy(): void
    {
        register_taxonomy(self::TAXONOMY, [self::POST_TYPE], [
            'labels' => [
                'name' => __('Volt GYM Categories', 'volt-gym-catalog'),
                'singular_name' => __('Volt GYM Category', 'volt-gym-catalog'),
            ],
            'public' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'rewrite' => ['slug' => 'volt-gym-category'],
        ]);
    }
}

(new Volt_Gym_Catalog_Plugin())->boot();
