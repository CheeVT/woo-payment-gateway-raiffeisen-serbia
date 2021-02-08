<?php

class WooRaiffeisenSerbiaGateway extends WC_Payment_Gateway {

    public $test_mode;
    public $payment_gateway_url;
    public $terminal_id;
    public $merchant_id;
    public $currency;
    
    public function __construct()
    {
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
        $this->test_mode = $this->get_option('testMode');
        $this->payment_gateway_url = $this->test_mode == 'yes' ? 
            'https://ecg.test.upc.ua/ecgtestrs/enter' : 
            'https://ecommerce.raiffeisenbank.rs/rbrs/enter';
        $this->terminal_id = $this->get_option('terminalid');
        $this->merchant_id = $this->get_option('merchantid');
        $this->currency = $this->get_option('currency');
        //var_dump($this->payment_gateway_url);

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_receipt_woo_raiffeisen_serbia', array($this, 'receipt_page'));

        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'transaction_verification'));
    } 
    
    public function init_form_fields()
    {
        $this->form_fields = apply_filters('wc_offline_form_fields', array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('Enable Raiffeisen Serbia Payment Gateway', 'woo-raiffeisen-serbia'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-offline'),
                'default' => __('Raiffeisen Serbia Payment', 'woo-raiffeisen-serbia'),
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
            'testMode' => array(
                'title' => __('Test mode', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('Enable Test Mode', 'woo-raiffeisen-serbia'),
                'default' => 'yes',
            ),
            /*'gatewayAddress' => array(
                'title' => __('Gateway Address', 'woo-raiffeisen-serbia'),
                'type' => 'select',
                'description' => __('This controls gateway for form action, and redirect to payment page', 'wc-gateway-offline'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
                'options' => array('https://ecg.test.upc.ua/ecgtestrs/enter' => 'Test', 'https://ecommerce.raiffeisenbank.rs/rbrs/enter' => 'Production')
            ),*/
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

    function process_payment($order_id)
    {
        //validation here

        $order = new WC_Order($order_id);
        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('checkout'))))
        );
    }

    /**
     * Order page
     * @param $order
     */

    public function receipt_page($order_id){
        
        $form_data = array(
            'TotalAmount' => $this->get_order_total(),
            'returnUrl' => WC()->api_request_url('WooRaiffeisenSerbiaGateway'),
            'OrderID' => $order_id,
            'MerchantID' => $this->merchant_id,
            'TerminalID' => $this->terminal_id,
            'Currency' => $this->currency,
            'PurchaseTime' => date("ymdHis"),
            'Signature' => ''
        );

        //var_dump($order);
        echo '<p>' . __('Thank you for your order, please click the button below to pay with Raiffeisen.', 'woocommerce') . '</p>';
        //WC()->api_request_url('WooRaiffeisenSerbiaGateway')

        $this->generate_raiffeisen_form($form_data);
        
        /*echo '<form action="http://localhost/placanje-test/" method="post" 
          id="submit_raiffeisen_payment_form">
                <input type="hidden" name="amount" value="'. $this->get_order_total() .'"/>
                <input type="hidden" name="returnUrl" value="'. WC()->api_request_url('WooRaiffeisenSerbiaGateway') .'"/>
                <input type="hidden" name="OrderID" value="'. $order_id .'"/>
                <input type="hidden" name="MerchantID" value="'. $this->merchant_id .'"/>
                <input type="hidden" name="TerminalID" value="'. $this->terminal_id .'"/>
                <button type="submit">PAY</button>
                <script type="text/javascript">
					jQuery("#submit_payment_form").submit();
                </script>
                </form>';*/

        //var_dump($this->get_return_url());
    }

    protected function generate_raiffeisen_form($form_data)
    {
        //echo '<form action="http://localhost/placanje-test/" method="post" 
        echo '<form action="' . $this->payment_gateway_url . '" method="post" 
            id="submit_raiffeisen_payment_form">';
        foreach($form_data as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        echo '<button type="submit">PAY</button>';
        //echo '<script type="text/javascript">document.getElementById("submit_raiffeisen_payment_form").submit();</script>';
        echo '</form>';
    }

    public function transaction_verification()
    {
        if (empty($_POST)) {
            $callback = json_decode(file_get_contents("php://input"));
            if (empty($callback)) {
                wp_die('go away!');
            }
            $_POST = array();
            foreach ($callback as $key => $val) {
                $_POST[esc_sql($key)] = esc_sql($val);
            }
        }

        $order = new WC_Order($_POST['OrderID']);
        $order->payment_complete();
        //var_dump($_POST);
        //var_dump($order);
        wp_redirect($this->get_return_url($order));
        exit;
    }
}