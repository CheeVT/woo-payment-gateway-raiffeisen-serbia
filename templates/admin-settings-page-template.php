<?php

/*echo '<pre>';
var_dump($this->exchange_rates_data);
echo '</pre>';*/

//var_dump($this->exchange_rate);
?>
<div style="display: flex; flex-flow: row;">
    <div class="woo-raiffeisen-settings">
        <h2><?php echo $this->method_title; ?></h2>
        <p><?php echo $this->method_description; ?></p>
        <p><?php _e('Please fill Success and Failure URLs to your Merchant interface. Copy API endpoint below:'); ?></p>
        <code><?php echo get_site_url() . '/wc-api/' . get_class($this); ?></code>
        <hr>
        <table class="form-table">
        <?php $this->generate_settings_html(); ?>
        </table>
    </div>
    <?php if($this->exchange_rate == 'yes'): ?>
        <div class="woo-raiffeisen-exchange-rates"">
            <h2>Kursna Lista</h2>
            <table>
                <?php if($this->exchange_rates_data && is_array($this->exchange_rates_data)): ?>
                    <tr>
                        <th><?php echo $this->exchange_rates_data['date']; ?></th>
                    </tr>
                    <?php unset($this->exchange_rates_data['date']); ?>
                    <?php foreach($this->exchange_rates_data as $currency => $rate): ?>
                        <tr>
                            <td><?php echo $currency; ?></td>
                            <td><?php echo $rate['sre']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>Nema trenutno importovanih valuta i kurseva</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .woo-raiffeisen-settings {
        flex-basis: 100%;
    }

    .woo-raiffeisen-exchange-rates {
        flex-basis: 30%;
        padding-left: 2%;
    }
</style>

<script>
jQuery(function($) {
    check_api_enabled();

    $('#woocommerce_woo_raiffeisen_serbia_exchangeRate').on('change', function(e) {
        check_api_enabled();
    })

    function check_api_enabled() {
        if($('#woocommerce_woo_raiffeisen_serbia_exchangeRate').is(':checked')) {
            $('#woocommerce_woo_raiffeisen_serbia_apiKey').parents('tr').show();
        } else {
            $('#woocommerce_woo_raiffeisen_serbia_apiKey').parents('tr').hide();
        }
    }
});
</script>
