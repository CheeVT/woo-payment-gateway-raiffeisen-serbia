<?php
/**
 * Plugin Name: WooCommerce Raiffeisen Serbia Gateway
 * Version: 1.0.0
 * Plugin URI: 
 * Description: Take a credit card payments on your WooCommerce store using Raiffeisen Bank in Serbia
 * Author: cheevt
 * Author URI: https://github.com/cheevt
 * Requires at least: x.x
 * Tested up to: x.x
 *
 * @package WordPress
 * @author cheevt
 * @since 1.0.0
 */

defined('ABSPATH') or exit;

define( 'WOO_RAIFFEISEN_SERBIA_PLUGIN_NAME', plugin_basename( __FILE__ ) );
define( 'WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_RAIFFEISEN_SERBIA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// include main plugin file.
require_once WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia-activator.php';
register_activation_hook(__FILE__, array('WooRaiffeisenSerbiaActivator', 'plugin_activation'));

require_once WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia-deactivator.php';
register_deactivation_hook(__FILE__, array('WooRaiffeisenSerbiaDeactivator', 'plugin_deactivation'));

add_action('plugins_loaded', 'wc_raiffeisen_gateway_init', 11);
function wc_raiffeisen_gateway_init()
{
    require_once(WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia.php');
    new WooRaiffeisenSerbia();
}

add_filter('woocommerce_payment_gateways', 'add_woo_raiffeisen_serbia_to_gateways');
function add_woo_raiffeisen_serbia_to_gateways($gateways)
{
    require_once(WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia-gateway.php');
    $gateways[] = 'WooRaiffeisenSerbiaGateway';
    return $gateways;
}