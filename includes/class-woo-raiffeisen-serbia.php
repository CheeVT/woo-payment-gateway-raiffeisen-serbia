<?php

class WooRaiffeisenSerbia {
   
    public function __construct()
    {
        $this->includes();
    }    

    public function includes()
    {
        include_once WOO_RAIFFEISEN_SERBIA_PLUGIN_PATH . 'includes/class-woo-raiffeisen-serbia-activator.php';
    }

}