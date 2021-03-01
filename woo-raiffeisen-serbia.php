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

add_filter('woocommerce_payment_gateways', 'add_woo_raiffeisen_serbia_to_gateways');
function add_woo_raiffeisen_serbia_to_gateways($gateways)
{
    require_once( WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia-gateway.php' );
    $gateways[] = 'WooRaiffeisenSerbiaGateway';
    return $gateways;
}

// Raiffeisen Serbia payement gateway description: Append calculated Total in RSD (serbian dinar)
add_filter( 'woocommerce_gateway_description', 'gateway_raiffeisen_custom_fields', 20, 2 );
function gateway_raiffeisen_custom_fields($description, $payment_id)
{
    global $woocommerce;

    if( 'woo_raiffeisen_serbia' === $payment_id ){
        ob_start(); // Start buffering

        $woo_currency = get_woocommerce_currency();

        $total = WC()->cart->total;

        if($woo_currency == 'EUR') {
            $total = $total * 117.64;
            echo '<div>';
            echo 'Price in RSD: ' . $total;
            echo '</div>';
        }

        $description .= ob_get_clean(); // Append buffered content
    }
    return $description;
}
/*
add_action('wp', 'cronstarter_activation');

function cronstarter_activation()
{
    if (!wp_next_scheduled('kursna_lista_task_hook23')) {
        wp_schedule_event( time(), 'kursna_lista_api5', 'kursna_lista_task_hook23' );
    }
}

add_action('kursna_lista_task_hook23', 'grab_rate');

function grab_rate()
{
    file_put_contents("test-11.txt", "Hello World. Testing!", FILE_APPEND);
}*/

include_once WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/exchange-rate-api/kursna-lista-api.php';