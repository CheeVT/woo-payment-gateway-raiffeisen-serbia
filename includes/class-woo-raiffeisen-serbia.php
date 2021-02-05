<?php

class WooRaiffeisenSerbia extends WC_Payment_Gateway {
    
    public function __construct()
    {
        $this->includes();

        $this->id = 'woo_raiffeisen_serbia';
        $this->icon = apply_filters('woocommerce_offline_icon', '');
        $this->has_fields = false;
        $this->method_title = __('Raiffeisen Serbia Gateway', 'woo-raiffeisen-serbia');
        $this->method_description = __('Take a credit card payments on your WooCommerce store using Raiffeisen Bank in Serbia', 'woo-raiffeisen-serbia');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->instructions = $this->get_option('instructions', $this->description);

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

    }    

    public function includes()
    {
        include_once WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia-activator.php';
    }

    public function init_form_fields()
    {
        $this->form_fields = apply_filters('wc_offline_form_fields', array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('Enable Raiffeisen Bank Payment Gateway', 'woo-raiffeisen-serbia'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-offline'),
                'default' => __('Raiffeisen Bank Payment', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'woo-raiffeisen-serbia'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woo-raiffeisen-serbia'),
                'default' => __('Please remit payment to Store Name upon pickup or delivery.', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => __('Instructions', 'woo-raiffeisen-serbia'),
                'type' => 'textarea',
                'description' => __('Instructions that will be added to the thank you page and emails.', 'wc-gateway-offline'),
                'default' => '',
                'desc_tip' => true,
            ),
            'gatewayAddress' => array(
                'title' => __('Gateway Address', 'woo-raiffeisen-serbia'),
                'type' => 'select',
                'description' => __('This controls gateway for form action, and redirect to payment page', 'wc-gateway-offline'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
                'options' => array('https://ecg.test.upc.ua/ecgtestrs/enter' => 'Test', 'https://ecommerce.raiffeisenbank.rs/rbrs/enter' => 'Production')
            ),
            'terminalid' => array(
                'title' => __('Terminal ID', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-offline'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true
            ),
            'merchantid' => array(
                'title' => __('Merchant ID', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-offline'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),
            'currency' => array(
                'title' => __('Currency', 'woo-raiffeisen-serbia'),
                'type' => 'select',
                'description' => __('', 'wc-gateway-offline'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => false,
                'options' => array('941' => 'Serbian dinar (RSD)', '978' => 'Euro (€)', '840' => 'Dollar ($)')
            )
        ));
    }

}