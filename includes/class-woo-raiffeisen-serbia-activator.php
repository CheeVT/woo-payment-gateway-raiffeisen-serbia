<?php

class WooRaiffeisenSerbiaActivator {

    public static function plugin_activation()
    {
        //store init data here [maybe :-)]

        //Check if WooCommerce is active
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            echo '<h3>'.__('Please install WooCommerce first before activating. <a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce Wordpress plugin</a>', 'woo-raiffeisen-serbia').'</h3>';

            //Adding @ before will prevent XDebug output
            @trigger_error(__('Please install WooCommerce first before activating.', 'woo-raiffeisen-serbia'), E_USER_ERROR);
        }
    }
}