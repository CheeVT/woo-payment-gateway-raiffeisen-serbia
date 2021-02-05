<?php

class WooRaiffeisenSerbiaActivator {

    public function __construct()
    {
        add_filter('plugin_action_links_' . WOO_RAIFFEISEN_SERBIA_PLUGIN_NAME, array($this, 'plugin_action_links'));        
    }

    public function plugin_action_links($links)
    {
        $action_links = array(
			'settings' => sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woo_raiffeisen_serbia' ), esc_html__( 'Settings', 'woo-raiffeisen-serbia' ) ),
			'docs'     => sprintf( '<a target="_blank" href="#">%s</a>', __( 'Documentation', 'woo-raiffeisen-serbia' ) ),
		);

		return array_merge($action_links, $links);
    }    
}

new WooRaiffeisenSerbiaActivator();