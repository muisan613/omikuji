<?php
namespace OmikujiStripePro;
if (!defined('ABSPATH')) exit;

class Plugin {
  private static $instance=null;
  public static function instance(){ if(self::$instance===null) self::$instance=new self(); return self::$instance; }

  private function __construct(){
    add_shortcode('omikuji_pro_draw', [$this,'shortcode_draw']);
    add_shortcode('omikuji_pro_result', [$this,'shortcode_result']);
    add_action('wp_enqueue_scripts', [$this,'enqueue_assets']);
  }

  public function enqueue_assets(){
    // Stripe.js
    wp_enqueue_script('stripe-js','https://js.stripe.com/v3/',[],null,true);
    // CSS & JS
    wp_enqueue_style('omikuji-pro-css', OMIKUJI_PRO_URL.'assets/css/omikuji.css',[],OMIKUJI_PRO_VERSION);
    wp_enqueue_script('omikuji-pro-js', OMIKUJI_PRO_URL.'assets/js/omikuji-pro.js',['jquery','stripe-js'],OMIKUJI_PRO_VERSION,true);
    $success = get_permalink((int)get_option('omikuji_pro_success_page_id'));
    wp_localize_script('omikuji-pro-js','OMIKUJI_PRO_VARS',[
      'restUrl'=>esc_url_raw(rest_url('omikuji-pro/v1')),
      'publishable'=>get_option('omikuji_pro_pub_key',''),
      'successPage'=>$success ?: home_url('/'),
    ]);
  }

  public function shortcode_draw(){
    ob_start();
    include OMIKUJI_PRO_DIR.'templates/omikuji-button.php';
    return ob_get_clean();
  }
  public function shortcode_result(){
    ob_start();
    include OMIKUJI_PRO_DIR.'templates/omikuji-result.php';
    return ob_get_clean();
  }
}
