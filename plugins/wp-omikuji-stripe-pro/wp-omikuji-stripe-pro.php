<?php
/**
 * Plugin Name: WP Omikuji Stripe Pro
 * Description: おみくじ電子決済Pro版（動画演出＋Stripe決済）
 * Version: 1.3.3
 * Author: Muian Studio
 */
if (!defined('ABSPATH')) exit;

define('OMIKUJI_PRO_DIR', plugin_dir_path(__FILE__));
define('OMIKUJI_PRO_URL', plugin_dir_url(__FILE__));
define('OMIKUJI_PRO_VERSION', '1.3.3');

require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-plugin.php';
require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-admin.php';
require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-rest.php';

add_action('plugins_loaded', function(){
  \OmikujiStripePro\Plugin::instance();
  \OmikujiStripePro\Admin::instance();
  \OmikujiStripePro\Rest::instance();
});
