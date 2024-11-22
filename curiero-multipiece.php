<?php

/*******************************************************************************
 * Plugin Name: CurieRO Extension - Multipiece
 * Plugin URI: https://curie.ro
 * Description: Extensie pentru plugin-ul CurieRO - Generare Multipiece
 * Version: 1.0.0
 * Author: Echipa CurieRO
 * Author URI: https://curie.ro
 * WC requires at least: 3.4.5
 * WC tested up to: 9.1.2
 * Requires PHP: 7.4.0
 * Requires Plugins: curiero-plugin
 * Text Domain: curiero-plugin
 *******************************************************************************/

// Exit if accessed directly
defined('ABSPATH') || exit;

final class CurieRO_Multipiece
{
    private static $_instance;

    private function __construct()
    {
        $this->set_defines();
        $this->init_hooks();
        $this->register_classes();
    }

    /**
     * Init hooks (for hpos compatibility)
     * 
     * @return void
     */
    private function init_hooks(): void
    {
        add_action('before_woocommerce_init', function () {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('remote_logging', __FILE__, true);
            }
        });
    }

    /**
     * Init instance of this class
     * 
     * @return CurieRO_Multipiece
     */
    public static function instance(): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    /**
     * Register classes for shipping methods
     * 
     * @return void
     */
    private function register_classes(): void
    {
        require CURIERO_MULTIPIECE_PLUGIN_PATH . 'includes/cargus-multipiece.php';
        new Cargus_Shipping_Method_Multipiece();
    }

    /**
     * Sets defines
     * 
     * @return void
     */
    private function set_defines(): void
    {
        define('CURIERO_MULTIPIECE_PLUGIN_FILE', __FILE__);
        define('CURIERO_MULTIPIECE_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('CURIERO_MULTIPIECE_PLUGIN_PATH', plugin_dir_path(__FILE__));
    }
}

function CurieRO_Multipiece(): CurieRO_Multipiece
{
    return CurieRO_Multipiece::instance();
}

$GLOBALS['curiero_multipiece'] = CurieRO_Multipiece();
