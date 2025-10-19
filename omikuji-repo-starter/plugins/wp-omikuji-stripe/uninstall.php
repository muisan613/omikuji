<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
delete_option('omikuji_stripe_publishable_key');
delete_option('omikuji_stripe_secret_key');
delete_option('omikuji_stripe_price_amount');
delete_option('omikuji_stripe_currency');
delete_option('omikuji_stripe_success_page_id');
delete_option('omikuji_stripe_webhook_secret');
