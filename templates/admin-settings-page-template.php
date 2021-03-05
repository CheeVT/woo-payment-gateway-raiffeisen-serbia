<?php

/*echo '<pre>';
var_dump($this->exchange_rates_data);
echo '</pre>';*/

//var_dump($this->exchange_rate);
?>
<div class="woo-raiffeisen-container">
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
            <div class="heading">
                <h2><?php _e('Exchange rates', 'woo-raiffeisen-serbia'); ?></h2>
                <span><a href="#" id="fetch-rates"><?php _e('Refresh', 'woo-raiffeisen-serbia'); ?></a></span>
            </div>
            <table class="widefat fixed striped exchange-rate-table">
                <?php if($this->exchange_rates_data && is_array($this->exchange_rates_data)): ?>
                    <thead>
                        <tr>
                            <th><?php _e('On date:', 'woo-raiffeisen-serbia'); ?></th>
                            <th><?php echo $this->exchange_rates_data['date']; ?></th>
                        </tr>
                    </thead>
                    <?php unset($this->exchange_rates_data['date']); ?>
                    <tbody id="the-list">
                        <?php foreach($this->exchange_rates_data as $currency => $rate): ?>
                            <tr>
                                <td><?php echo strtoupper($currency); ?></td>
                                <td><?php echo $rate['sre']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php else: ?>
                    <tbody id="the-list">
                        <tr>
                            <td><?php _e('There is no imported exchange rates. Please click "Refresh"', 'woo-raiffeisen-serbia'); ?></td>
                        </tr>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .woo-raiffeisen-container {
        display: flex;
        flex-flow: row;
        justify-content: space-between;
        width: 100%;
    }

    .woo-raiffeisen-settings .input-text,
    .woo-raiffeisen-settings .select {
        width: 100% !important;
        max-width: 400px;
    }

    .woo-raiffeisen-exchange-rates {
        padding-left: 2%;
    }

    .woo-raiffeisen-exchange-rates .heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .exchange-rate-table {
        width: 250px;
    }

    .exchange-rate-table th {
        font-weight: 700;
    }

    @media (max-width: 1023px) {
        .woo-raiffeisen-container {
            flex-flow: column;
        }
        .woo-raiffeisen-exchange-rates {
            padding-left: unset;
        }
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

    $('#fetch-rates').on('click', function(e) {
        e.preventDefault();

        $.post(ajaxurl, {'action': 'fetch_rates'}, function(response) {
            alert(response.msg)
            window.location.reload(true);
		});
    })
});
</script>
