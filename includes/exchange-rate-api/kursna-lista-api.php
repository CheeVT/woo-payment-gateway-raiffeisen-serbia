<?php

class KursnaListaAPI {

    public static $option_name = 'woocommerce_woo_raiffeisen_serbia_kursna_lista';
    public $plugin_settings;

    public function __construct()
    {
        $this->plugin_settings = get_option('woocommerce_woo_raiffeisen_serbia_settings');

        $this->set_cron_jobs();
        
    }

    public static function get_exchange_rates_data()
    {
        $rates = get_option(self::$option_name);

        if($rates && is_string($rates)) {
            $rates = json_decode($rates, true);

            if(isset($rates['result'])) return $rates['result'];
        }
        return;
    } 

    protected function set_cron_jobs()
    {
        //unschedule cron if api key is blank
        if(isset($this->plugin_settings['apiKey']) && ! trim($this->plugin_settings['apiKey'])) {
            wp_unschedule_event(time(), 'kursna_lista_srbija_func');
            wp_clear_scheduled_hook('kursna_lista_srbija_func');
            return;
        }

        add_filter('cron_schedules', array($this, 'kursna_lista_cron_schedules')); 

        add_action('wp', array($this, 'cronstarter_activation'));
        
        add_action('kursna_lista_srbija_func', array($this, 'fetch_rate_and_update'));
    }

    public static function kursna_lista_cron_schedules($schedules)
    {
        if(!isset($schedules["kursna_lista_srbija"])){
            $schedules["kursna_lista_srbija"] = array(
                'interval' => 43200, //every 12 hours
                //'interval' => 60, //every minute
                'display' => __('Import exchange rate from API')
            );
        }
        return $schedules;
    }

    public static function cronstarter_activation()
    {        
        if (!wp_next_scheduled('kursna_lista_srbija_func')) {
            wp_schedule_event(time(), 'kursna_lista_srbija', 'kursna_lista_srbija_func');
        }
    }
 
    public static function fetch_rate_and_update()
    {
        $apiKey = $this->plugin_settings['apiKey'];

        $url = 'https://api.kursna-lista.info/'.$apiKey.'/kursna_lista/json';
        $content = file_get_contents($url);

        if (empty($content)) {
            $log = 'Greška u preuzimanju podataka';
            //die('Greška u preuzimanju podataka');
        }

        $data = json_decode($content, true);

        if ($data['status'] == 'ok') {
            $log = 'Uspešno!';
            update_option(self::$option_name, $content);
        } else {
            $log = "Došlo je do greške: " . $data['code'] . " - " . $data['msg'];
        }
        

        file_put_contents("test-11.txt", $log, FILE_APPEND);
    }

}

new KursnaListaAPI();