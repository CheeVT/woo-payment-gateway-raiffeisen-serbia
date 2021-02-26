<?php

class KursnaListaAPI {
    public function __construct()
    {
        $this->register_cron();

        add_action('kursna_lista_api', array($this, 'kursna_lista_api_func'));
    }

    public function handle()
    {

    }

    protected function register_cron()
    {
        $cron_jobs = get_option('cron');
        /*echo '<pre>';
        print_r($cron_jobs);
        echo '</pre>';*/
        if (! wp_next_scheduled ( 'kursna_lista_api' )) {
            wp_schedule_event(time(), 'hourly', 'kursna_lista_api');
        }
    }

 
    public function kursna_lista_api_func() {
        //func goes here
    }
}

new KursnaListaAPI();