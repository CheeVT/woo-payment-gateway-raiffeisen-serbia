<?php

class WooRaiffeisenSerbiaGateway extends WC_Payment_Gateway {

    public $test_mode;
    public $payment_gateway_url;
    public $terminal_id;
    public $merchant_id;
    public $currency;
    public $card_logos;
    public $exchange_rate;
    public $apiKey;
    public $exchange_rates_data;
    public $checkout_currency;
    public $rsd_currency_abbreviations = ['rsd', 'рсд', 'din', 'дин'];
    
    public function __construct()
    {
        $this->id = 'woo_raiffeisen_serbia';
        $this->icon = apply_filters('woocommerce_offline_icon', '');
        $this->has_fields = false;
        $this->method_title = __('Raiffeisen Serbia Gateway', 'woo-raiffeisen-serbia');
        $this->method_description = __('Take a credit card payments on your WooCommerce store using Raiffeisen Bank in Serbia.', 'woo-raiffeisen-serbia');
        $this->exchange_rates_data = KursnaListaAPI::get_exchange_rates_data();

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->test_mode = $this->get_option('testMode');
        $this->payment_gateway_url = $this->test_mode == 'yes' ? 
            'https://ecg.test.upc.ua/rbrs/enter' :
            'https://ecommerce.raiffeisenbank.rs/rbrs/enter';
        $this->terminal_id = $this->get_option('terminalid');
        $this->merchant_id = $this->get_option('merchantid');
        $this->currency = $this->get_option('currency');
        $this->card_logos = $this->get_option('cardLogos');
        $this->exchange_rate = $this->get_option('exchangeRate');
        $this->apiKey = $this->get_option('apiKey');
        $this->checkout_currency = strtolower(get_woocommerce_currency());
        //var_dump($this->payment_gateway_url);

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_receipt_woo_raiffeisen_serbia', array($this, 'receipt_page'));

        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'transaction_verification'));

        add_action('woocommerce_admin_order_totals_after_total', array($this, 'custom_admin_order_paid_in_rsd_after_total'), 10, 1 );

        // Filters
        add_filter('woocommerce_available_payment_gateways', array($this, 'show_is_correctly_configured'));

        add_filter( 'woocommerce_gateway_description', array($this, 'gateway_raiffeisen_custom_fields'), 20, 2 );    
    }

    public function admin_options()
    {
        include_once (WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'templates/admin-settings-page-template.php');
    }
    
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('Enable Raiffeisen Serbia Payment Gateway', 'woo-raiffeisen-serbia'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'woo-raiffeisen-serbia'),
                'default' => __('Raiffeisen Serbia Payment', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'woo-raiffeisen-serbia'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woo-raiffeisen-serbia'),
                'default' => __('Pay by credit card. Redirect to Raiffeisen gateway to process payment.', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),
            'testMode' => array(
                'title' => __('Test mode', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('Enable Test Mode', 'woo-raiffeisen-serbia'),
                'default' => 'yes',
            ),
            'terminalid' => array(
                'title' => __('Terminal ID', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('Provided by Raiffeisen bank.', 'woo-raiffeisen-serbia'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true
            ),
            'merchantid' => array(
                'title' => __('Merchant ID', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('Provided by Raiffeisen bank.', 'woo-raiffeisen-serbia'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),            
            'currency' => array(
                'title' => __('Currency', 'woo-raiffeisen-serbia'),
                'type' => 'select',
                'description' => __('Supported currency by Raiffeisen Payment gateway', 'woo-raiffeisen-serbia'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
                'options' => array('941' => 'Serbian dinar (RSD)')
                //'options' => array('941' => 'Serbian dinar (RSD)', '978' => 'Euro (€)', '840' => 'Dollar ($)')
            ),
            'exchangeRate' => array(
                'title' => __('Exchange rates API', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('If you use no supported currency, use "Kursna lista" API to convert to RSD for Raiffeisen gateway', 'woo-raiffeisen-serbia'),
                'default' => 'no',
                'description' => __('Available currencies: EUR, USD, CHF, GBP, AUD, CAD, SEK, DKK, NOK, JPY, RUB, CNY, HRK, KWD, PLN, CZK, HUF, BAM <br> <a href="https://www.kursna-lista.info/kursna-lista-api" target="_blank">www.kursna-lista.info/kursna-lista-api</a>', 'woo-raiffeisen-serbia'),
                //'desc_tip' => true
            ),
            'apiKey' => array(
                'title' => __('API ID', 'woo-raiffeisen-serbia'),
                'type' => 'text',
                'description' => __('Please create API ID on www.kursna-lista.info', 'woo-raiffeisen-serbia'),
                'default' => __('', 'woo-raiffeisen-serbia'),
                'desc_tip' => true,
            ),            
            'cardLogos' => array(
                'title' => __('Show card logos', 'woo-raiffeisen-serbia'),
                'type' => 'checkbox',
                'label' => __('Show Visa, MasterCard and Raiffeisen Bank logos in the payment methods section on checkout', 'woo-raiffeisen-serbia'),
                'default' => 'yes',
            ),
        );
    }

    public function show_is_correctly_configured($available_gateways)
    {
        foreach ($available_gateways as $gateway) {
			if ($gateway instanceof WooRaiffeisenSerbiaGateway) {
                if(! $this->is_raiffeisen_gateway_available($gateway)) {
                    unset($available_gateways[$gateway->id]);
                }
			}
        }
        
        return $available_gateways;
    }

    protected function is_raiffeisen_gateway_available($gateway)
    {
        if(!$gateway->terminal_id || !$gateway->merchant_id) return false;

        if(in_array($this->checkout_currency, $this->rsd_currency_abbreviations)) return true;
        //if($this->checkout_currency == 'RSD') return true;

        if($this->exchange_rate == 'no') return false;

        if(isset($this->exchange_rates_data[strtolower($this->checkout_currency)])) return true;

        return false;
    }

    public function get_icon()
    {
        $icon = '<img src="'.WOO_RAIFFEISEN_SERBIA_PLUGIN_URL.'/assets/images/card-icons.png" alt="Raiffeisen Bank" />';

        if ($this->card_logos == "yes") {
            return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
        } else {
            return false;
        }
    }    

    public function process_payment($order_id)
    {
        //validation here

        $order = new WC_Order($order_id);
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url( true )
            //'redirect' => add_query_arg('order_id', $order->get_id(), add_query_arg('key', $order->get_order_key(), get_permalink(wc_get_page_id('checkout'))))
        );
    }

    /**
     * Order page
     * @param $order
     */
    public function receipt_page($order_id)
    {
        $purchase_time = date("ymdHis");
        
        $total_amount = $this->total_in_rsd($this->get_order_total());

        //generate signature with .pem file
        $signature = $this->generate_signature($purchase_time, $order_id, $total_amount);

        //var_dump($total_amount);
        //exit;

        
        $form_data = array(
            'TotalAmount' => $total_amount,
            //'returnUrl' => WC()->api_request_url('WooRaiffeisenSerbiaGateway'),
            'OrderID' => $order_id,
            'MerchantID' => $this->merchant_id,
            'TerminalID' => $this->terminal_id,
            'Currency' => $this->currency,
            'PurchaseTime' => $purchase_time,
            'locale' => 'rs',
            'Signature' => $signature,
            //'Version' => 1,
        );

        echo '<p>' . __('Thank you for your order, please click the button below to pay with Raiffeisen.', 'woo-raiffeisen-serbia') . '</p>';
        //if($this->checkout_currency != 'RSD') {
        if(! in_array($this->checkout_currency, $this->rsd_currency_abbreviations)) {
            echo $this->print_converted_price($this->convert_to_rsd($this->get_order_total()));
        }
        $this->generate_raiffeisen_form($form_data);
        
    }

    protected function total_in_rsd($total)
    {
        //if($this->checkout_currency != 'RSD') {
        if(! in_array($this->checkout_currency, $this->rsd_currency_abbreviations)) {
            $total = $this->convert_to_rsd($total);
        }

        return $this->calculate_total_in_cents($total);
    }

    protected function convert_to_rsd($total) {
        if(in_array($this->checkout_currency, $this->rsd_currency_abbreviations)) return $total;

        if(! isset($this->exchange_rates_data[strtolower($this->checkout_currency)])) {
            die(__('There is no currency in exchange rates! Please choose other payment method.', 'woo-raiffeisen-serbia'));
        }

        $rate = $this->exchange_rates_data[strtolower($this->checkout_currency)];

        if(! isset($rate['sre'])) {            
            die(__('There is no currency in exchange rates!', 'woo-raiffeisen-serbia'));
        }

        return round($total * $rate['sre'], 2);
    }

    protected function calculate_total_in_cents($total)
    {
        return round($total, 2) * 100;
    }

    protected function generate_signature($purchase_time, $order_id, $total_amount)
    {
        $pem_file = WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'pem/' . $this->merchant_id . '.pem';

        //here
        $data = "$this->merchant_id;$this->terminal_id;$purchase_time;$order_id;$this->currency;$total_amount;;";

        $fp = fopen($pem_file, "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $pkeyid = openssl_get_privatekey($priv_key);
        openssl_sign($data, $signature, $pkeyid);
        openssl_free_key($pkeyid);
        $b64sign = base64_encode($signature);

        return $b64sign;
    }

    protected function generate_raiffeisen_form($form_data)
    {
        //echo '<form action="http://localhost/placanje-test/" method="POST" id="submit_raiffeisen_payment_form">';
        echo '<form action="' . $this->payment_gateway_url . '" method="POST" id="submit_raiffeisen_payment_form">';
        foreach($form_data as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        echo '<button type="submit">PAY</button>';
        echo '<script type="text/javascript">document.getElementById("submit_raiffeisen_payment_form").submit();</script>';
        echo '</form>';
    }

    public function transaction_verification()
    {
        global $woocommerce;

        if (empty($_POST)) {
            $callback = json_decode(file_get_contents("php://input"));
            if (empty($callback)) {
                die('go away!');
            }
            $_POST = array();
            foreach ($callback as $key => $val) {
                $_POST[esc_sql($key)] = esc_sql($val);
            }
        }
        
        if(! $this->signature_verification($_POST)) {            
            die(__('signature verification failed!', 'woo-raiffeisen-serbia'));
        };

        $order = new WC_Order($_POST['OrderID']);

        if($this->is_payment_process_success($_POST['TranCode'])) {
            $order->update_meta_data('total_paid_in_rsd', $this->convert_to_rsd($order->get_total()));
            $order->payment_complete();
        }

        $woocommerce->cart->empty_cart();
        
        wp_redirect($this->get_return_url($order));
        //var_dump($_POST);
        exit;
    }

    protected function signature_verification($postData)
    {
        $cert_file = WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'pem/test-server.cert';

        $signature = base64_decode($postData['Signature']);

        $data = "$postData[MerchantID];$postData[TerminalID];$postData[PurchaseTime];$postData[OrderID];$postData[XID];$postData[Currency];$postData[TotalAmount];$postData[SD];$postData[TranCode];$postData[ApprovalCode];";

        $fp = fopen($cert_file, "r");
        $cert_key = fread($fp, 8192);
        fclose($fp);
        $pkeyid = openssl_get_publickey($cert_key);
        $verify = openssl_verify($data, $signature, $pkeyid);
        openssl_free_key($pkeyid);

        return $verify;
    }

    protected function is_payment_process_success($trans_code)
    {
        return $trans_code == '000';
    }

    // Raiffeisen Serbia payement gateway description: Append calculated Total in RSD (serbian dinar)
    public function gateway_raiffeisen_custom_fields($description, $payment_id)
    {
        global $woocommerce;

        if('woo_raiffeisen_serbia' === $payment_id){            
            $total = WC()->cart->total;
            
            //if($this->checkout_currency != 'RSD') {
            if(! in_array($this->checkout_currency, $this->rsd_currency_abbreviations)) {
                $total = $this->convert_to_rsd($total);

                $description .= $this->print_converted_price($total);
            }
        }
        return $description;
    }

    protected function print_converted_price($total)
    {        
        $description = '<p>';
        $description .= sprintf(__('Converted price for Raiffeisen gateway: %s', 'woo-raiffeisen-serbia'), '<strong>'.$total.' RSD</strong>');
        $description .= '</p>';

        return $description;
    }

    
    public function custom_admin_order_paid_in_rsd_after_total($order_id)
    {
        $order = new WC_Order($order_id);
        $paymentMethod = $order->get_payment_method();

        if($paymentMethod == 'woo_raiffeisen_serbia') {
            $total_paid = $order->get_meta('total_paid_in_rsd');
            if(!$total_paid) return;

            echo '<table class="wc-order-totals">';
            echo '<tr>';
            echo '<td class="label label-highlight">' . __('Paid in RSD', 'woo-raiffeisen-serbia') . ':</td>';
            echo '<td width="1%"></td>';
            echo '<td class="total">';
            echo '<span class="woocommerce-Price-amount amount">';
            echo '' . $total_paid . 'RSD';
            echo '</span>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';
        }
    }

}