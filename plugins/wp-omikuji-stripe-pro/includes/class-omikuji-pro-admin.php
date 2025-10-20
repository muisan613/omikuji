<?php
namespace OmikujiStripePro;
if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {
  private static $instance = null;
  public static function instance(){ if (null===self::$instance) self::$instance = new self(); return self::$instance; }
  private function __construct(){
    add_action('admin_menu',[ $this,'menu' ]);
    add_action('admin_init',[ $this,'settings' ]);
  }
  public function menu(){
    add_options_page('Omikuji Stripe Pro','Omikuji Stripe Pro','manage_options','omikuji-stripe-pro',[ $this,'render' ]);
  }
  public function settings(){
    register_setting('omikuji_pro_group','omikuji_pro_pub_key');
    register_setting('omikuji_pro_group','omikuji_pro_sec_key');
    register_setting('omikuji_pro_group','omikuji_pro_price_amount');
    register_setting('omikuji_pro_group','omikuji_pro_currency');
    register_setting('omikuji_pro_group','omikuji_pro_success_page_id');
    register_setting('omikuji_pro_group','omikuji_pro_results_json');
  }
  public function render(){ ?>
  <div class="wrap">
    <h1>Omikuji Stripe Pro 設定</h1>
    <form method="post" action="options.php">
      <?php settings_fields('omikuji_pro_group'); do_settings_sections('omikuji_pro_group'); ?>
      <table class="form-table">
        <tr><th>Publishable Key</th><td><input type="text" name="omikuji_pro_pub_key" value="<?php echo esc_attr(get_option('omikuji_pro_pub_key','')); ?>" class="regular-text"></td></tr>
        <tr><th>Secret Key</th><td><input type="password" name="omikuji_pro_sec_key" value="<?php echo esc_attr(get_option('omikuji_pro_sec_key','')); ?>" class="regular-text"></td></tr>
        <tr><th>価格（最小単位）</th><td><input type="number" name="omikuji_pro_price_amount" value="<?php echo esc_attr(get_option('omikuji_pro_price_amount','200')); ?>" class="small-text"> <span class="description">JPY は 50 以上の整数</span></td></tr>
        <tr><th>通貨</th><td><input type="text" name="omikuji_pro_currency" value="<?php echo esc_attr(get_option('omikuji_pro_currency','jpy')); ?>" class="small-text"></td></tr>
        <tr><th>成功ページ（[omikuji_result]）</th><td><?php wp_dropdown_pages(['name'=>'omikuji_pro_success_page_id','selected'=>(int)get_option('omikuji_pro_success_page_id'),'show_option_none'=>'— 選択 —']); ?></td></tr>
        <tr><th>結果と重み（JSON）</th><td><textarea name="omikuji_pro_results_json" rows="8" class="large-text code"><?php echo esc_textarea(get_option('omikuji_pro_results_json','[]')); ?></textarea></td></tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php }
}
