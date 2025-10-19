<?php
/**
 * Plugin Name: WP Omikuji Stripe
 * Description: Stripe Checkoutで電子おみくじを販売。くじ動画→結果表示までをミニマル実装。
 * Version: 1.0.1
 * Author: Your Name
 * Text Domain: wp-omikuji-stripe
 */
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'OMIKUJI_STRIPE_VERSION', '1.0.1' );
define( 'OMIKUJI_STRIPE_DIR', plugin_dir_path( __FILE__ ) );
define( 'OMIKUJI_STRIPE_URL', plugin_dir_url( __FILE__ ) );
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-plugin.php';
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-admin.php';
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-rest.php';
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-webhook.php';
function omikuji_stripe_init() {
    \OmikujiStripe\Plugin::instance();
    \OmikujiStripe\Admin::instance();
    \OmikujiStripe\Rest::instance();
    \OmikujiStripe\Webhook::instance();
}
add_action( 'plugins_loaded', 'omikuji_stripe_init' );
