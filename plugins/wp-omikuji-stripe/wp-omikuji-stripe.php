<?php
/**
 * Plugin Name: WP Omikuji Stripe
 * Description: Stripe Checkoutで電子おみくじ（UIモック）。旧API互換のHotfix版。
 * Version: 1.0.3
 * Author: Your Name
 * Text Domain: wp-omikuji-stripe
 */
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'OMIKUJI_STRIPE_VERSION', '1.0.3' );
define( 'OMIKUJI_STRIPE_DIR', plugin_dir_path( __FILE__ ) );
define( 'OMIKUJI_STRIPE_URL', plugin_dir_url( __FILE__ ) );
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-plugin.php';
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-admin.php';
require_once OMIKUJI_STRIPE_DIR . 'includes/class-omikuji-rest.php';
function omikuji_stripe_init(){ \OmikujiStripe\Plugin::instance(); \OmikujiStripe\Admin::instance(); \OmikujiStripe\Rest::instance(); }
add_action('plugins_loaded','omikuji_stripe_init');
