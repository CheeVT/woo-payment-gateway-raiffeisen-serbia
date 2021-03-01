<?php

class WooRaiffeisenSerbiaDeactivator {


    public static function plugin_deactivation()
    {
        wp_unschedule_event(time(), 'kursna_lista_srbija_func');
        wp_clear_scheduled_hook('kursna_lista_srbija_func');
    }

  
}