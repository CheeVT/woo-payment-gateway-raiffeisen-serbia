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

add_action('plugins_loaded', 'wc_raiffeisen_gateway_init', 11);

function wc_raiffeisen_gateway_init()
{
    require_once( WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia.php' );
    new WooRaiffeisenSerbia();
}