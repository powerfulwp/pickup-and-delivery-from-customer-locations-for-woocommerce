<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://powerfulwp.com
 * @since             1.0.0
 * @package           Pdfclw
 *
 * @wordpress-plugin
 * Plugin Name:       Pickup & Delivery from Customer Locations for WooCommerce
 * Plugin URI:        https://powerfulwp.com/pickup-and-delivery-from-customer-locations-for-woocommerce
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.1
 * Author:            powerfulwp
 * Author URI:        https://powerfulwp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pdfclw
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( function_exists( 'pdfclw_fs' ) ) {
    pdfclw_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'pdfclw_fs' ) ) {
        
        if ( !function_exists( 'pdfclw_fs' ) ) {
            // Create a helper function for easy SDK access.
            function pdfclw_fs()
            {
                global  $pdfclw_fs ;
                
                if ( !isset( $pdfclw_fs ) ) {
                    // Include Freemius SDK.
                    require_once dirname( __FILE__ ) . '/freemius/start.php';
                    $pdfclw_fs = fs_dynamic_init( array(
                        'id'             => '9677',
                        'slug'           => 'pickup-and-delivery-from-customer-locations-for-woocommerce',
                        'premium_slug'   => 'pickup-and-delivery-from-customer-locations-for-woocommerce-pro',
                        'type'           => 'plugin',
                        'public_key'     => 'pk_a981edbaf48ab40aa946a8714e66e',
                        'is_premium'     => false,
                        'premium_suffix' => 'Premium',
                        'has_addons'     => false,
                        'has_paid_plans' => true,
                        'trial'          => array(
                        'days'               => 14,
                        'is_require_payment' => true,
                    ),
                        'menu'           => array(
                        'slug'    => 'pdfclw-settings',
                        'support' => false,
                        'network' => true,
                    ),
                        'is_live'        => true,
                    ) );
                }
                
                return $pdfclw_fs;
            }
            
            // Init Freemius.
            pdfclw_fs();
            // Signal that SDK was initiated.
            do_action( 'pdfclw_fs_loaded' );
        }
    
    }
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'PDFCLW_VERSION', '1.0.1' );
    /**
     * Define supported plugins.
     */
    $pdfclw_plugins = array();
    $pdfclw_multivendor = '';
    if ( is_plugin_active( 'comunas-de-chile-para-woocommerce/woocoomerce-comunas.php' ) ) {
        // Chile states.
        $pdfclw_plugins[] = 'comunas-de-chile-para-woocommerce';
    }
    
    if ( is_plugin_active( 'wc-frontend-manager/wc_frontend_manager.php' ) ) {
        // WCFM.
        $pdfclw_plugins[] = 'wcfm';
        $pdfclw_multivendor = 'wcfm';
    }
    
    
    if ( is_plugin_active( 'dc-woocommerce-multi-vendor/dc_product_vendor.php' ) ) {
        // WC Marketplace.
        $pdfclw_plugins[] = 'wcmp';
        $pdfclw_multivendor = 'wcmp';
    }
    
    
    if ( is_plugin_active( 'dokan-lite/dokan.php' ) ) {
        // Dokan.
        $pdfclw_plugins[] = 'dokan';
        $pdfclw_multivendor = 'dokan';
    }
    
    define( 'PDFCLW_PLUGINS', $pdfclw_plugins );
    /**
     * Define multivendor plugin.
     */
    define( 'PDFCLW_MULTIVENDOR', ( in_array( $pdfclw_multivendor, PDFCLW_PLUGINS, true ) ? $pdfclw_multivendor : '' ) );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-pdfclw-activator.php
     */
    function activate_pdfclw()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pdfclw-activator.php';
        Pdfclw_Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-pdfclw-deactivator.php
     */
    function deactivate_pdfclw()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-pdfclw-deactivator.php';
        Pdfclw_Deactivator::deactivate();
    }
    
    register_activation_hook( __FILE__, 'activate_pdfclw' );
    register_deactivation_hook( __FILE__, 'deactivate_pdfclw' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-pdfclw.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_pdfclw()
    {
        $plugin = new Pdfclw();
        $plugin->run();
    }
    
    run_pdfclw();
}
