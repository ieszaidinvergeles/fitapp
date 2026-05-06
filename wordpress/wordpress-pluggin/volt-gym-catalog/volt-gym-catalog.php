<?php
/**
 * Plugin Name: Volt GYM Catalog
 * Plugin URI: https://voltgym.local
 * Description: Professional gym product catalog with a 3-column responsive grid layout using Tailwind CSS.
 * Version: 1.0.0
 * Author: Voltgym
 * Text Domain: volt-gym-catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access is not allowed.' );
}

final class Gym_Catalog_Plugin {

    private static $instance;

    /**
     * Singleton Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Initialization hooks
        add_action( 'init', array( $this, 'gym_cat_register_cpt' ) );
        add_action( 'init', array( $this, 'gym_cat_register_taxonomies' ) );

        // Admin hooks
        add_action( 'admin_menu', array( $this, 'gym_cat_settings_menu' ) );
        add_action( 'admin_init', array( $this, 'gym_cat_settings_register' ) );

        // Frontend assets and Shortcode
        add_action( 'wp_enqueue_scripts', array( $this, 'gym_cat_enqueue_assets' ) );
        add_shortcode( 'gym_show_product', array( $this, 'gym_cat_show_product_shortcode' ) );
        add_shortcode( 'gym_catalog', array( $this, 'gym_cat_catalog_grid_shortcode' ) );
    }

    /**
     * Enqueue Tailwind CSS and inject grid-fix styles
     */
    public function gym_cat_enqueue_assets() {
        wp_enqueue_script( 'tailwind-cdn', 'https://cdn.tailwindcss.com', array(), null, false );
        
        $options = get_option( 'gym_cat_settings', array( 'brand_color' => '#1a1a1a' ) );
        $primary_color = $options['brand_color'];
        
        wp_add_inline_script( 'tailwind-cdn', "
            tailwind.config = {
              theme: {
                extend: {
                  colors: {
                    gymbrand: '$primary_color',
                  }
                }
              }
            }
        " );
    }

    /**
     * Register Custom Post Type: Gym Products
     */
    public function gym_cat_register_cpt() {
        register_post_type( 'gym_product', array(
            'public'    => true,
            'label'     => 'Gym Products',
            'menu_icon' => 'dashicons-cart',
            'supports'  => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
            'has_archive' => true,
            'show_in_rest' => true,
            'rewrite'   => array( 'slug' => 'gym-products' ),
        ));
    }

    /**
     * Register Taxonomy: Categories
     */
    public function gym_cat_register_taxonomies() {
        register_taxonomy( 'product_cat', 'gym_product', array(
            'label'        => 'Categories',
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true
        ));
    }

    /**
     * Settings Page in Admin Dashboard
     */
    public function gym_cat_settings_menu() {
        add_menu_page( 'Gym Settings', 'Gym Settings', 'manage_options', 'gym-catalog-settings', array( $this, 'gym_cat_settings_page_html' ) );
    }

    public function gym_cat_settings_register() {
        register_setting( 'gym_cat_options_group', 'gym_cat_settings' );
    }

    public function gym_cat_settings_page_html() {
        $options = get_option( 'gym_cat_settings', array( 'brand_color' => '#1a1a1a' ) );
        ?>
        <div class="wrap">
            <h1>Volt GYM Design Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'gym_cat_options_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Brand Primary Color:</th>
                        <td><input type="color" name="gym_cat_settings[brand_color]" value="<?php echo esc_attr( $options['brand_color'] ); ?>"></td>
                    </tr>
                </table>
                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Shortcode to display product card
     */
    public function gym_cat_show_product_shortcode( $atts ) {
        $args = shortcode_atts( array( 'id' => 0 ), $atts );
        $post_id = $args['id'];
        if ( ! $post_id ) return 'Invalid Product ID';

        // NEW ENGLISH METADATA NAMES (Update your Custom Fields in WP)
        $price    = get_post_meta( $post_id, 'price', true );
        $brand    = get_post_meta( $post_id, 'brand', true );
        $variant  = get_post_meta( $post_id, 'variant', true );
        $format   = get_post_meta( $post_id, 'format', true );
        $stock    = get_post_meta( $post_id, 'stock', true );
        
        $thumb    = get_the_post_thumbnail_url( $post_id, 'large' );
        $terms    = get_the_terms( $post_id, 'product_cat' );
        $cat_name = ( $terms ) ? $terms[0]->name : 'Gym';

        ob_start(); ?>
        
        <div class="h-full">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 h-full flex flex-col transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl group">
                
                <div class="relative h-48 overflow-hidden bg-gray-50">
                    <?php if( $thumb ): ?>
                        <img src="<?php echo $thumb; ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-300 italic text-sm">No Image Available</div>
                    <?php endif; ?>
                    <span class="absolute top-4 right-4 bg-gymbrand text-white text-[10px] font-black px-3 py-1 rounded-lg uppercase shadow-lg z-10">
                        <?php echo esc_html( $cat_name ); ?>
                    </span>
                </div>

                <div class="p-6 flex-grow flex flex-col">
                    <span class="text-gymbrand text-[10px] font-bold uppercase tracking-widest mb-1 block">
                        <?php echo $brand ? esc_html( $brand ) : 'VOLT GYM'; ?>
                    </span>
                    
                    <h3 class="text-xl font-black text-gray-900 mb-4 h-12 flex items-center leading-tight">
                        <?php echo get_the_title( $post_id ); ?>
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-2 mb-6">
                        <div class="bg-gray-50 p-2 rounded-xl text-center border border-gray-100">
                            <p class="text-[8px] text-gray-400 uppercase font-black">Variant</p>
                            <p class="text-[10px] font-bold text-gray-700 truncate"><?php echo $variant ?: 'N/A'; ?></p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-xl text-center border border-gray-100">
                            <p class="text-[8px] text-gray-400 uppercase font-black">Format</p>
                            <p class="text-[10px] font-bold text-gray-700 truncate"><?php echo $format ?: 'N/A'; ?></p>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?php echo (strtolower($stock) === 'out of stock' || strtolower($stock) === 'sold out') ? 'bg-red-400' : 'bg-green-400'; ?> opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 <?php echo (strtolower($stock) === 'out of stock' || strtolower($stock) === 'sold out') ? 'bg-red-500' : 'bg-green-500'; ?>"></span>
                            </span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                <?php echo $stock ? esc_html( $stock ) : 'In Stock'; ?>
                            </span>
                        </div>
                        <div class="text-2xl font-black text-gray-900 tracking-tighter">
                            <?php echo $price; ?><span class="text-sm font-bold text-gymbrand ml-0.5">€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode to display the full catalog grid
     */
    public function gym_cat_catalog_grid_shortcode() {
        $query = new WP_Query( array(
            'post_type'      => 'gym_product',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ));

        if ( ! $query->have_posts() ) {
            return '<div class="text-center py-10 text-gray-500">No products found in the catalog.</div>';
        }

        ob_start(); ?>
        
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php echo $this->gym_cat_show_product_shortcode( array( 'id' => get_the_ID() ) ); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}

// Start Plugin
Gym_Catalog_Plugin::instance();

// Activation hooks
register_activation_hook( __FILE__, function() {
    Gym_Catalog_Plugin::instance()->gym_cat_register_cpt();
    flush_rewrite_rules();
});