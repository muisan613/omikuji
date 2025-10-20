<?php
/**
 * Plugin Name: WP Omikuji Stripe Pro
 * Description: Stripe Checkoutで電子おみくじ（堅牢版：サーバー検証・重複防止・加重確率・DB保存・UI演出／結果データはDB管理）
 * Version: 1.2.1-dbresults-authfix
 * Author: Studio Muian
 * Text Domain: wp-omikuji-stripe-pro
 */
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'OMIKUJI_PRO_VERSION', '1.2.1-dbresults-authfix' );
define( 'OMIKUJI_PRO_DIR', plugin_dir_path( __FILE__ ) );
define( 'OMIKUJI_PRO_URL', plugin_dir_url( __FILE__ ) );

require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-install.php';
require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-plugin.php';
require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-admin.php';
require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-model.php';
require_once OMIKUJI_PRO_DIR . 'includes/class-omikuji-pro-rest.php';

function omikuji_pro_init() {
  \OmikujiStripePro\Install::maybe_upgrade();
  \OmikujiStripePro\Plugin::instance();
  \OmikujiStripePro\Admin::instance();
  \OmikujiStripePro\Rest::instance();
}
add_action('plugins_loaded','omikuji_pro_init');

register_activation_hook( __FILE__, ['\\OmikujiStripePro\\Install','activate'] );
