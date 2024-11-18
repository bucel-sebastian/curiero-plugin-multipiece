<?php

/*******************************************************************************
 * Plugin Name: CurieRO Multipiece Extension
 * Plugin URI: https://curie.ro
 * Description: Plugin-ul CurieRO All-in-one - Generare AWB si Metode de livrare
 * Version: 1.0.0
 * Author: Echipa CurieRO
 * Author URI: https://curie.ro
 * WC requires at least: 3.4.5
 * WC tested up to: 9.1.2
 * Requires PHP: 7.3.0
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
        $this->register_classes();
    }

    /**
     * @return CurieRO_Multipiece
     */
    public static function instance(): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    private function register_classes()
    {
        require CURIERO_MULTIPIECE_PLUGIN_PATH . 'includes/cargus-multipiece.php';
        new Cargus_Shipping_Method_Multipiece();
    }

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
