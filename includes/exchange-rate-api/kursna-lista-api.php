<?php

class KursnaListaAPI {
    public function __construct()
    {
        add_filter('cron_schedules', array(__CLASS__, 'kursna_lista_cron_schedules')); 

        add_action('wp', array(__CLASS__, 'cronstarter_activation'));
        
        add_action('kursna_lista_srbija_func', array(__CLASS__, 'grab_rate'));        
    }

    public static function kursna_lista_cron_schedules($schedules)
    {
        if(!isset($schedules["kursna_lista_srbija"])){
            $schedules["kursna_lista_srbija"] = array(
                'interval' => 60, //every 5 minutes
                'display' => __('Import exchange rate from API')
            );
        }
        return $schedules;
    }

    public static function cronstarter_activation()
    {        
        if (!wp_next_scheduled('kursna_lista_srbija_func')) {
            wp_schedule_event( time(), 'kursna_lista_srbija', 'kursna_lista_srbija_func' );
        }
        
         /*echo '<pre>';
        print_r(get_option('cron'));
        echo '</pre>';*/
    }

    public static function grab_rate()
    {
        $settings = get_option('woocommerce_woo_raiffeisen_serbia_settings');
        //return;
        $log = 'Nije setupovano!';
        if(isset($settings['apiKey']) && trim($settings['apiKey'])) {
            $apiKey = $settings['apiKey'];

            $url = 'https://api.kursna-lista.info/'.$apiKey.'/kursna_lista/json';
            $content = file_get_contents($url);

            if (empty($content)) {
                $log = 'Greška u preuzimanju podataka';
                //die('Greška u preuzimanju podataka');
            }

            $data = json_decode($content, true);

            if ($data['status'] == 'ok') {
                $log = 'Uspešno!';
                update_option('woocommerce_woo_raiffeisen_serbia_kursna_lista', $content);
            } else {
                $log = "Došlo je do greške: " . $data['code'] . " - " . $data['msg'];
            }
        }

        file_put_contents("test-11.txt", $log, FILE_APPEND);
    }

}

new KursnaListaAPI();